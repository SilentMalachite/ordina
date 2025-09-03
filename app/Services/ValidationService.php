<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ValidationService
{
    /**
     * 商品のバリデーションルール
     */
    public function getProductValidationRules($productId = null): array
    {
        $uniqueRule = $productId 
            ? "unique:products,product_code,{$productId}"
            : 'unique:products';

        return [
            'product_code' => "required|string|max:50|{$uniqueRule}",
            'name' => 'required|string|max:255',
            'stock_quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * 顧客のバリデーションルール
     */
    public function getCustomerValidationRules($customerId = null): array
    {
        $uniqueRule = $customerId 
            ? "unique:customers,email,{$customerId}"
            : 'unique:customers';

        return [
            'name' => 'required|string|max:255',
            'email' => "required|string|email|max:255|{$uniqueRule}",
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'company' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * 取引のバリデーションルール
     */
    public function getTransactionValidationRules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:sale,rental',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'return_date' => 'nullable|date|after:transaction_date',
        ];
    }

    /**
     * 在庫調整のバリデーションルール
     */
    public function getInventoryAdjustmentValidationRules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'adjustment_type' => 'required|in:increase,decrease,set',
            'quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * ユーザーのバリデーションルール
     */
    public function getUserValidationRules($userId = null): array
    {
        $uniqueRule = $userId 
            ? "unique:users,email,{$userId}"
            : 'unique:users';

        return [
            'name' => 'required|string|max:255',
            'email' => "required|string|email|max:255|{$uniqueRule}",
            'password' => $userId ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ];
    }

    /**
     * 締め日のバリデーションルール
     */
    public function getClosingDateValidationRules($closingDateId = null): array
    {
        $uniqueRule = $closingDateId 
            ? "unique:closing_dates,day_of_month,{$closingDateId}"
            : 'unique:closing_dates';

        return [
            'day_of_month' => "required|integer|between:1,31|{$uniqueRule}",
            'description' => 'nullable|string|max:500',
        ];
    }

    /**
     * データ削除のバリデーションルール
     */
    public function getDataDeletionValidationRules(): array
    {
        return [
            'data_type' => 'required|in:transactions,products,customers,all',
            'confirmation' => 'required|accepted',
        ];
    }

    /**
     * バリデーションを実行
     */
    public function validate(Request $request, array $rules, array $messages = []): array
    {
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * 商品データをバリデーション
     */
    public function validateProduct(Request $request, $productId = null): array
    {
        $rules = $this->getProductValidationRules($productId);
        $messages = [
            'product_code.required' => '商品コードは必須です。',
            'product_code.unique' => 'この商品コードは既に使用されています。',
            'name.required' => '商品名は必須です。',
            'stock_quantity.required' => '在庫数は必須です。',
            'stock_quantity.min' => '在庫数は0以上である必要があります。',
            'unit_price.required' => '単価は必須です。',
            'unit_price.min' => '単価は0以上である必要があります。',
            'selling_price.required' => '売値は必須です。',
            'selling_price.min' => '売値は0以上である必要があります。',
        ];

        return $this->validate($request, $rules, $messages);
    }

    /**
     * 顧客データをバリデーション
     */
    public function validateCustomer(Request $request, $customerId = null): array
    {
        $rules = $this->getCustomerValidationRules($customerId);
        $messages = [
            'name.required' => '顧客名は必須です。',
            'email.required' => 'メールアドレスは必須です。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.unique' => 'このメールアドレスは既に使用されています。',
        ];

        return $this->validate($request, $rules, $messages);
    }

    /**
     * 取引データをバリデーション
     */
    public function validateTransaction(Request $request): array
    {
        $rules = $this->getTransactionValidationRules();
        $messages = [
            'product_id.required' => '商品を選択してください。',
            'product_id.exists' => '選択された商品が存在しません。',
            'customer_id.required' => '顧客を選択してください。',
            'customer_id.exists' => '選択された顧客が存在しません。',
            'type.required' => '取引タイプを選択してください。',
            'type.in' => '無効な取引タイプです。',
            'quantity.required' => '数量は必須です。',
            'quantity.min' => '数量は1以上である必要があります。',
            'unit_price.required' => '単価は必須です。',
            'unit_price.min' => '単価は0以上である必要があります。',
            'total_amount.required' => '合計金額は必須です。',
            'total_amount.min' => '合計金額は0以上である必要があります。',
            'transaction_date.required' => '取引日は必須です。',
            'transaction_date.date' => '有効な日付を入力してください。',
        ];

        return $this->validate($request, $rules, $messages);
    }

    /**
     * 在庫調整データをバリデーション
     */
    public function validateInventoryAdjustment(Request $request): array
    {
        $rules = $this->getInventoryAdjustmentValidationRules();
        $messages = [
            'product_id.required' => '商品を選択してください。',
            'product_id.exists' => '選択された商品が存在しません。',
            'adjustment_type.required' => '調整タイプを選択してください。',
            'adjustment_type.in' => '無効な調整タイプです。',
            'quantity.required' => '数量は必須です。',
            'quantity.min' => '数量は0以上である必要があります。',
            'reason.required' => '理由は必須です。',
        ];

        return $this->validate($request, $rules, $messages);
    }

    /**
     * ユーザーデータをバリデーション
     */
    public function validateUser(Request $request, $userId = null): array
    {
        $rules = $this->getUserValidationRules($userId);
        $messages = [
            'name.required' => 'ユーザー名は必須です。',
            'email.required' => 'メールアドレスは必須です。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.unique' => 'このメールアドレスは既に使用されています。',
            'password.required' => 'パスワードは必須です。',
            'password.min' => 'パスワードは8文字以上である必要があります。',
            'password.confirmed' => 'パスワードの確認が一致しません。',
            'role.required' => 'ロールを選択してください。',
            'role.exists' => '選択されたロールが存在しません。',
        ];

        return $this->validate($request, $rules, $messages);
    }

    /**
     * 締め日データをバリデーション
     */
    public function validateClosingDate(Request $request, $closingDateId = null): array
    {
        $rules = $this->getClosingDateValidationRules($closingDateId);
        $messages = [
            'day_of_month.required' => '締め日は必須です。',
            'day_of_month.between' => '締め日は1日から31日の間で入力してください。',
            'day_of_month.unique' => 'この締め日は既に設定されています。',
        ];

        return $this->validate($request, $rules, $messages);
    }

    /**
     * データ削除をバリデーション
     */
    public function validateDataDeletion(Request $request): array
    {
        $rules = $this->getDataDeletionValidationRules();
        $messages = [
            'data_type.required' => '削除するデータタイプを選択してください。',
            'data_type.in' => '無効なデータタイプです。',
            'confirmation.required' => '確認が必要です。',
            'confirmation.accepted' => '削除を確認してください。',
        ];

        return $this->validate($request, $rules, $messages);
    }

    /**
     * バリデーションエラーをフォーマット
     */
    public function formatValidationErrors(array $errors): array
    {
        $formatted = [];
        
        foreach ($errors as $field => $messages) {
            $formatted[$field] = is_array($messages) ? $messages[0] : $messages;
        }
        
        return $formatted;
    }
}