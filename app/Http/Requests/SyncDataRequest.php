<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // APIトークンミドルウェアで認証済みなのでtrue
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data' => 'required|array|min:1|max:100', // 最大100テーブルまで
            'data.*.table' => [
                'required',
                'string',
                Rule::in(['products', 'customers', 'transactions', 'inventory_adjustments'])
            ],
            'data.*.records' => 'required|array|min:1|max:500', // テーブルあたり最大500レコード
            'data.*.records.*.uuid' => 'required|string|uuid',
            'data.*.records.*.updated_at' => 'nullable|date',
            'data.*.records.*.created_at' => 'nullable|date',
        ];
    }

    /**
     * テーブル別の追加検証ルール
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->input('data', []);

            foreach ($data as $tableIndex => $tableData) {
                $tableName = $tableData['table'] ?? '';
                $records = $tableData['records'] ?? [];

                foreach ($records as $recordIndex => $record) {
                    $this->validateRecordByTable($validator, $tableName, $record, $tableIndex, $recordIndex);
                }
            }
        });
    }

    /**
     * テーブル別のレコード検証
     */
    private function validateRecordByTable($validator, string $tableName, array $record, int $tableIndex, int $recordIndex)
    {
        switch ($tableName) {
            case 'products':
                $this->validateProductRecord($validator, $record, $tableIndex, $recordIndex);
                break;
            case 'customers':
                $this->validateCustomerRecord($validator, $record, $tableIndex, $recordIndex);
                break;
            case 'transactions':
                $this->validateTransactionRecord($validator, $record, $tableIndex, $recordIndex);
                break;
            case 'inventory_adjustments':
                $this->validateInventoryAdjustmentRecord($validator, $record, $tableIndex, $recordIndex);
                break;
        }
    }

    /**
     * 商品レコードの検証
     */
    private function validateProductRecord($validator, array $record, int $tableIndex, int $recordIndex)
    {
        $rules = [
            "data.{$tableIndex}.records.{$recordIndex}.product_code" => 'required|string|max:50',
            "data.{$tableIndex}.records.{$recordIndex}.name" => 'required|string|max:255',
            "data.{$tableIndex}.records.{$recordIndex}.stock_quantity" => 'required|integer|min:0',
            "data.{$tableIndex}.records.{$recordIndex}.unit_price" => 'required|numeric|min:0|max:999999.99',
            "data.{$tableIndex}.records.{$recordIndex}.selling_price" => 'required|numeric|min:0|max:999999.99',
            "data.{$tableIndex}.records.{$recordIndex}.description" => 'nullable|string|max:1000',
        ];

        // ビジネスロジック検証：売値は原価以上であること
        if (isset($record['unit_price']) && isset($record['selling_price'])) {
            if ($record['selling_price'] < $record['unit_price']) {
                $validator->errors()->add(
                    "data.{$tableIndex}.records.{$recordIndex}.selling_price",
                    '売値は原価以上である必要があります。'
                );
            }
        }

        $this->addValidationRules($validator, $rules);
    }

    /**
     * 顧客レコードの検証
     */
    private function validateCustomerRecord($validator, array $record, int $tableIndex, int $recordIndex)
    {
        $rules = [
            "data.{$tableIndex}.records.{$recordIndex}.name" => 'required|string|max:255',
            "data.{$tableIndex}.records.{$recordIndex}.contact_person" => 'nullable|string|max:255',
            "data.{$tableIndex}.records.{$recordIndex}.phone" => 'nullable|string|max:20',
            "data.{$tableIndex}.records.{$recordIndex}.email" => 'nullable|email|max:255',
            "data.{$tableIndex}.records.{$recordIndex}.address" => 'nullable|string|max:500',
            "data.{$tableIndex}.records.{$recordIndex}.type" => 'nullable|in:individual,company',
            "data.{$tableIndex}.records.{$recordIndex}.notes" => 'nullable|string|max:1000',
        ];

        $this->addValidationRules($validator, $rules);
    }

    /**
     * 取引レコードの検証
     */
    private function validateTransactionRecord($validator, array $record, int $tableIndex, int $recordIndex)
    {
        $rules = [
            "data.{$tableIndex}.records.{$recordIndex}.product_id" => 'required|integer|exists:products,id',
            "data.{$tableIndex}.records.{$recordIndex}.customer_id" => 'nullable|integer|exists:customers,id',
            "data.{$tableIndex}.records.{$recordIndex}.user_id" => 'required|integer|exists:users,id',
            "data.{$tableIndex}.records.{$recordIndex}.type" => 'required|in:sale,rental',
            "data.{$tableIndex}.records.{$recordIndex}.quantity" => 'required|integer|min:1|max:9999',
            "data.{$tableIndex}.records.{$recordIndex}.unit_price" => 'required|numeric|min:0|max:999999.99',
            "data.{$tableIndex}.records.{$recordIndex}.total_amount" => 'required|numeric|min:0|max:99999999.99',
            "data.{$tableIndex}.records.{$recordIndex}.transaction_date" => 'required|date|before_or_equal:today',
            "data.{$tableIndex}.records.{$recordIndex}.expected_return_date" => 'nullable|date|after:transaction_date',
            "data.{$tableIndex}.records.{$recordIndex}.returned_at" => 'nullable|datetime',
            "data.{$tableIndex}.records.{$recordIndex}.notes" => 'nullable|string|max:1000',
        ];

        // ビジネスロジック検証：合計金額の計算が正しいか
        if (isset($record['quantity']) && isset($record['unit_price']) && isset($record['total_amount'])) {
            $calculatedTotal = $record['quantity'] * $record['unit_price'];
            $allowedDifference = 0.01; // 1セントまでの誤差を許容

            if (abs($record['total_amount'] - $calculatedTotal) > $allowedDifference) {
                $validator->errors()->add(
                    "data.{$tableIndex}.records.{$recordIndex}.total_amount",
                    '合計金額が数量×単価と一致しません。'
                );
            }
        }

        // レンタル返却日の検証
        if (isset($record['type']) && $record['type'] === 'rental') {
            if (!isset($record['expected_return_date'])) {
                $validator->errors()->add(
                    "data.{$tableIndex}.records.{$recordIndex}.expected_return_date",
                    'レンタル取引には返却予定日が必要です。'
                );
            }
        }

        $this->addValidationRules($validator, $rules);
    }

    /**
     * 在庫調整レコードの検証
     */
    private function validateInventoryAdjustmentRecord($validator, array $record, int $tableIndex, int $recordIndex)
    {
        $rules = [
            "data.{$tableIndex}.records.{$recordIndex}.product_id" => 'required|integer|exists:products,id',
            "data.{$tableIndex}.records.{$recordIndex}.user_id" => 'required|integer|exists:users,id',
            "data.{$tableIndex}.records.{$recordIndex}.adjustment_type" => 'required|in:increase,decrease',
            "data.{$tableIndex}.records.{$recordIndex}.quantity" => 'required|integer|min:1|max:9999',
            "data.{$tableIndex}.records.{$recordIndex}.previous_quantity" => 'required|integer|min:0',
            "data.{$tableIndex}.records.{$recordIndex}.new_quantity" => 'required|integer|min:0',
            "data.{$tableIndex}.records.{$recordIndex}.reason" => 'required|string|max:500',
        ];

        // ビジネスロジック検証：在庫計算の整合性
        if (isset($record['adjustment_type']) && isset($record['previous_quantity']) && isset($record['quantity'])) {
            $expectedNewQuantity = $record['adjustment_type'] === 'increase'
                ? $record['previous_quantity'] + $record['quantity']
                : $record['previous_quantity'] - $record['quantity'];

            if (isset($record['new_quantity']) && $record['new_quantity'] !== $expectedNewQuantity) {
                $validator->errors()->add(
                    "data.{$tableIndex}.records.{$recordIndex}.new_quantity",
                    '新しい在庫数量が調整前の数量と調整数量から計算される値と一致しません。'
                );
            }
        }

        $this->addValidationRules($validator, $rules);
    }

    /**
     * 検証ルールをバリデータに追加
     */
    private function addValidationRules($validator, array $rules)
    {
        foreach ($rules as $field => $rule) {
            $validator->addRules([$field => $rule]);
        }
    }

    /**
     * カスタムエラーメッセージ
     */
    public function messages(): array
    {
        return [
            'data.required' => '同期データは必須です。',
            'data.array' => '同期データは配列である必要があります。',
            'data.min' => '少なくとも1つのテーブルデータを指定してください。',
            'data.max' => '同期できるテーブルは最大100個までです。',
            'data.*.table.required' => 'テーブル名は必須です。',
            'data.*.table.in' => '無効なテーブル名です。',
            'data.*.records.required' => 'レコードデータは必須です。',
            'data.*.records.array' => 'レコードデータは配列である必要があります。',
            'data.*.records.min' => '少なくとも1つのレコードを指定してください。',
            'data.*.records.max' => '1テーブルあたり最大500レコードまでです。',
            'data.*.records.*.uuid.required' => 'UUIDは必須です。',
            'data.*.records.*.uuid.uuid' => 'UUIDの形式が正しくありません。',
            'data.*.records.*.updated_at.date' => '更新日時の形式が正しくありません。',
            'data.*.records.*.created_at.date' => '作成日時の形式が正しくありません。',
        ];
    }
}
