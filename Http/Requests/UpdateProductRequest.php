<?php

// المسار الكامل: app/Http/Requests/UpdateProductRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasPermission('products.edit');
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'sku'            => ['nullable', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($productId)],
            'barcode'        => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($productId)],
            'name_ar'        => 'required|string|max:200',
            'name_en'        => 'nullable|string|max:200',
            'category_id'    => 'nullable|exists:categories,id',
            'brand'          => 'nullable|string|max:100',
            'unit'           => 'required|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price'     => 'required|numeric|min:0',
            'reorder_point'  => 'nullable|integer|min:0',
            'type'           => 'required|in:power_tools,hand_tools,equipment,spare_parts,other',
            'description'    => 'nullable|string',
            'is_active'      => 'boolean',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.required'        => 'اسم المنتج بالعربية مطلوب',
            'purchase_price.required' => 'سعر الشراء مطلوب',
            'sale_price.required'     => 'سعر البيع مطلوب',
            'unit.required'           => 'وحدة القياس مطلوبة',
            'type.required'           => 'نوع المنتج مطلوب',
            'sku.unique'              => 'كود المنتج (SKU) مستخدم مسبقاً',
            'barcode.unique'          => 'الباركود مستخدم مسبقاً',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active', true)]);
    }
}
