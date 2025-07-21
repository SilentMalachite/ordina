<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'type' => 'required|in:sale,rental',
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $product = Product::find($this->input('product_id'));
                    if ($product && $product->stock_quantity < $value) {
                        $fail('在庫が不足しています。現在の在庫数: ' . $product->stock_quantity);
                    }
                },
            ],
            'unit_price' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'expected_return_date' => 'required_if:type,rental|nullable|date|after:transaction_date',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => '取引タイプ',
            'customer_id' => '顧客',
            'product_id' => '商品',
            'quantity' => '数量',
            'unit_price' => '単価',
            'transaction_date' => '取引日',
            'notes' => '備考',
            'expected_return_date' => '返却予定日',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => '取引タイプを選択してください。',
            'type.in' => '取引タイプは売上または貸出を選択してください。',
            'customer_id.required' => '顧客を選択してください。',
            'customer_id.exists' => '選択された顧客が見つかりません。',
            'product_id.required' => '商品を選択してください。',
            'product_id.exists' => '選択された商品が見つかりません。',
            'quantity.required' => '数量を入力してください。',
            'quantity.integer' => '数量は整数で入力してください。',
            'quantity.min' => '数量は1以上で入力してください。',
            'unit_price.required' => '単価を入力してください。',
            'unit_price.numeric' => '単価は数値で入力してください。',
            'unit_price.min' => '単価は0以上で入力してください。',
            'transaction_date.required' => '取引日を入力してください。',
            'transaction_date.date' => '有効な日付を入力してください。',
            'expected_return_date.required_if' => '貸出の場合は返却予定日を入力してください。',
            'expected_return_date.date' => '有効な日付を入力してください。',
            'expected_return_date.after' => '返却予定日は取引日より後の日付を入力してください。',
        ];
    }
}
