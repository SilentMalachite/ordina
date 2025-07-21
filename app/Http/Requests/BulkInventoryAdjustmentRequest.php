<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;

class BulkInventoryAdjustmentRequest extends FormRequest
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
            'adjustments' => 'required|array|min:1',
            'adjustments.*.product_id' => 'required|exists:products,id',
            'adjustments.*.adjustment_type' => 'required|in:increase,decrease',
            'adjustments.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'reason' => 'required|string|max:500',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->input('adjustments', []) as $index => $adjustment) {
                if (isset($adjustment['adjustment_type']) && $adjustment['adjustment_type'] === 'decrease') {
                    $product = Product::find($adjustment['product_id'] ?? null);
                    if ($product && $product->stock_quantity < ($adjustment['quantity'] ?? 0)) {
                        $validator->errors()->add(
                            "adjustments.{$index}.quantity",
                            "商品「{$product->name}」の在庫が不足しています。現在の在庫数: {$product->stock_quantity}"
                        );
                    }
                }
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'adjustments' => '調整項目',
            'adjustments.*.product_id' => '商品',
            'adjustments.*.adjustment_type' => '調整タイプ',
            'adjustments.*.quantity' => '数量',
            'reason' => '理由',
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
            'adjustments.required' => '調整項目を入力してください。',
            'adjustments.array' => '調整項目は配列形式で入力してください。',
            'adjustments.min' => '少なくとも1つの調整項目を入力してください。',
            'adjustments.*.product_id.required' => '商品を選択してください。',
            'adjustments.*.product_id.exists' => '選択された商品が見つかりません。',
            'adjustments.*.adjustment_type.required' => '調整タイプを選択してください。',
            'adjustments.*.adjustment_type.in' => '調整タイプは増加または減少を選択してください。',
            'adjustments.*.quantity.required' => '数量を入力してください。',
            'adjustments.*.quantity.integer' => '数量は整数で入力してください。',
            'adjustments.*.quantity.min' => '数量は1以上で入力してください。',
            'reason.required' => '理由を入力してください。',
            'reason.max' => '理由は500文字以内で入力してください。',
        ];
    }
}
