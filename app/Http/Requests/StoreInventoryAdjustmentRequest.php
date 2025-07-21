<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;

class StoreInventoryAdjustmentRequest extends FormRequest
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
            'product_id' => 'required|exists:products,id',
            'adjustment_type' => 'required|in:increase,decrease',
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($this->input('adjustment_type') === 'decrease') {
                        $product = Product::find($this->input('product_id'));
                        if ($product && $product->stock_quantity < $value) {
                            $fail('在庫が不足しています。現在の在庫数: ' . $product->stock_quantity);
                        }
                    }
                },
            ],
            'reason' => 'required|string|max:500',
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
            'product_id' => '商品',
            'adjustment_type' => '調整タイプ',
            'quantity' => '数量',
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
            'product_id.required' => '商品を選択してください。',
            'product_id.exists' => '選択された商品が見つかりません。',
            'adjustment_type.required' => '調整タイプを選択してください。',
            'adjustment_type.in' => '調整タイプは増加または減少を選択してください。',
            'quantity.required' => '数量を入力してください。',
            'quantity.integer' => '数量は整数で入力してください。',
            'quantity.min' => '数量は1以上で入力してください。',
            'reason.required' => '理由を入力してください。',
            'reason.max' => '理由は500文字以内で入力してください。',
        ];
    }
}
