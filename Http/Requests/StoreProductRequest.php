<?php

// المسار الكامل: app/Http/Requests/StoreProductRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasPermission('products.create');
    }

    public function rules(): array
    {
        return [
            'sku'              => 'nullable|string|max:50|unique:products,sku',
            'barcode'          => 'nullable|string|max:100|unique:products,barcode',
            'name_ar'          => 'required|string|max:200',
            'name_en'          => 'nullable|string|max:200',
            'category_id'      => 'nullable|exists:categories,id',
            'brand'            => 'nullable|string|max:100',
            'unit'             => 'required|string|max:50',
            'purchase_price'   => 'required|numeric|min:0',
            'sale_price'       => 'required|numeric|min:0',
            'reorder_point'    => 'nullable|integer|min:0',
            'type'             => 'required|in:power_tools,hand_tools,equipment,spare_parts,other',
            'description'      => 'nullable|string',
            'is_active'        => 'boolean',
            'image'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'extra_images'     => 'nullable|array|max:5',
            'extra_images.*'   => 'image|mimes:jpg,jpeg,png,webp|max:3072',
            'initial_quantity' => 'nullable|integer|min:0',
            'warehouse_id'     => 'nullable|exists:warehouses,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.required'        => 'اسم المنتج بالعربية مطلوب',
            'purchase_price.required' => 'سعر الشراء مطلوب',
            'purchase_price.numeric'  => 'سعر الشراء يجب أن يكون رقماً',
            'sale_price.required'     => 'سعر البيع مطلوب',
            'sale_price.numeric'      => 'سعر البيع يجب أن يكون رقماً',
            'unit.required'           => 'وحدة القياس مطلوبة',
            'type.required'           => 'نوع المنتج مطلوب',
            'type.in'                 => 'نوع المنتج غير صالح',
            'sku.unique'              => 'كود المنتج (SKU) مستخدم مسبقاً',
            'barcode.unique'          => 'الباركود مستخدم مسبقاً',
            'image.image'             => 'يجب أن يكون ملف صورة',
            'image.max'               => 'حجم الصورة لا يتجاوز 3MB',
        ];
    }

    protected function prepareForValidation(): void
    {
        // توليد SKU تلقائي إذا لم يُدخل
        if (empty($this->sku)) {
            $this->merge(['sku' => \App\Models\Product::generateSku()]);
        }

        // تحويل is_active لـ boolean
        $this->merge(['is_active' => $this->boolean('is_active', true)]);
    }
}
