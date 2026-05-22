<?php

// المسار الكامل: app/Http/Requests/StoreCustomerRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('customers.create');
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'phone'          => ['required', 'string', 'max:20', 'unique:customers,phone'],
            'phone_alt'      => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'address'        => ['nullable', 'string', 'max:500'],
            'type'           => ['required', 'in:individual,company'],
            'company_name'   => ['nullable', 'string', 'max:255'],
            'tax_number'     => ['nullable', 'string', 'max:100'],
            'classification' => ['required', 'in:regular,vip,inactive'],
            'credit_limit'   => ['nullable', 'numeric', 'min:0'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'is_active'      => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'اسم العميل مطلوب',
            'phone.required'         => 'رقم الهاتف مطلوب',
            'phone.unique'           => 'رقم الهاتف مسجل مسبقاً',
            'email.unique'           => 'البريد الإلكتروني مسجل مسبقاً',
            'type.required'          => 'نوع العميل مطلوب',
            'type.in'                => 'نوع العميل غير صحيح',
            'classification.required'=> 'تصنيف العميل مطلوب',
            'classification.in'      => 'تصنيف العميل غير صحيح',
            'credit_limit.numeric'   => 'حد الائتمان يجب أن يكون رقماً',
            'credit_limit.min'       => 'حد الائتمان لا يمكن أن يكون سالباً',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
