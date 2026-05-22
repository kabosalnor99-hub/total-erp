<?php

// المسار الكامل: app/Http/Requests/StorePurchaseOrderRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('purchase-orders.create');
    }

    public function rules(): array
    {
        return [
            'supplier_id'           => 'required|exists:suppliers,id',
            'purchase_request_id'   => 'nullable|exists:purchase_requests,id',
            'expected_date'         => 'nullable|date',
            'discount'              => 'nullable|numeric|min:0',
            'tax_rate'              => 'nullable|numeric|min:0|max:100',
            'notes'                 => 'nullable|string|max:1000',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.unit_price'    => 'required|numeric|min:0',
            'items.*.discount'      => 'nullable|numeric|min:0',
            'items.*.notes'         => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required'        => 'يرجى اختيار المورد',
            'items.required'              => 'يجب إضافة منتج واحد على الأقل',
            'items.*.product_id.required' => 'يجب اختيار المنتج',
            'items.*.quantity.required'   => 'يجب إدخال الكمية',
            'items.*.quantity.min'        => 'الكمية يجب أن تكون أكبر من صفر',
            'items.*.unit_price.required' => 'يجب إدخال سعر الوحدة',
        ];
    }
}
