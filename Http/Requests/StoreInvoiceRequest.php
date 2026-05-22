<?php

// المسار الكامل: app/Http/Requests/StoreInvoiceRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('invoices.create');
    }

    public function rules(): array
    {
        return [
            'customer_id'      => ['nullable', 'exists:customers,id'],
            'type'             => ['required', 'in:cash,credit,partial'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount'  => ['nullable', 'numeric', 'min:0'],
            'tax_percent'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'due_date'         => ['nullable', 'date', 'after_or_equal:today'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'reference'        => ['nullable', 'string', 'max:255'],
            'warehouse_id'     => ['required', 'exists:warehouses,id'],

            // بنود الفاتورة
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.product_id'       => ['required', 'exists:products,id'],
            'items.*.quantity'         => ['required', 'numeric', 'min:0.01'],
            'items.*.price'            => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'              => 'نوع الفاتورة مطلوب',
            'type.in'                    => 'نوع الفاتورة غير صحيح',
            'warehouse_id.required'      => 'المستودع مطلوب',
            'warehouse_id.exists'        => 'المستودع المحدد غير موجود',
            'customer_id.exists'         => 'العميل المحدد غير موجود',
            'due_date.after_or_equal'    => 'تاريخ الاستحقاق يجب ألا يكون في الماضي',
            'items.required'             => 'يجب إضافة منتج واحد على الأقل',
            'items.min'                  => 'يجب إضافة منتج واحد على الأقل',
            'items.*.product_id.required'=> 'المنتج مطلوب',
            'items.*.product_id.exists'  => 'المنتج المحدد غير موجود',
            'items.*.quantity.required'  => 'الكمية مطلوبة',
            'items.*.quantity.min'       => 'الكمية يجب أن تكون أكبر من صفر',
            'items.*.price.required'     => 'السعر مطلوب',
            'items.*.price.min'          => 'السعر لا يمكن أن يكون سالباً',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // التحقق: فاتورة آجل أو جزئي تحتاج عميل
            if (in_array($this->type, ['credit', 'partial']) && empty($this->customer_id)) {
                $validator->errors()->add('customer_id', 'العميل مطلوب للفواتير الآجلة والجزئية');
            }

            // التحقق: فاتورة آجل تحتاج تاريخ استحقاق
            if ($this->type === 'credit' && empty($this->due_date)) {
                $validator->errors()->add('due_date', 'تاريخ الاستحقاق مطلوب للفواتير الآجلة');
            }
        });
    }
}
