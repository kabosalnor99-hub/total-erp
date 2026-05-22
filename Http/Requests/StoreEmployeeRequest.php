<?php

// المسار الكامل: app/Http/Requests/StoreEmployeeRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id;

        return [
            // ─── البيانات الشخصية ─────────────────────────────────────────
            'employee_number'   => 'required|string|max:50|unique:employees,employee_number,' . $employeeId,
            'name'              => 'required|string|max:255',
            'name_en'           => 'nullable|string|max:255',
            'national_id'       => 'nullable|string|max:50|unique:employees,national_id,' . $employeeId,
            'nationality'       => 'nullable|string|max:100',
            'date_of_birth'     => 'nullable|date|before:today',
            'gender'            => 'required|in:male,female',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'address'           => 'nullable|string|max:500',
            'photo'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // ─── البيانات الوظيفية ────────────────────────────────────────
            'department_id'     => 'nullable|exists:departments,id',
            'user_id'           => 'nullable|exists:users,id',
            'job_title'         => 'required|string|max:255',
            'contract_type'     => 'required|in:permanent,temporary,part_time,contract',
            'hire_date'         => 'required|date',
            'contract_end_date' => 'nullable|date|after:hire_date',
            'basic_salary'      => 'required|numeric|min:0',
            'status'            => 'required|in:active,on_leave,terminated',

            // ─── البيانات البنكية ─────────────────────────────────────────
            'bank_name'         => 'nullable|string|max:255',
            'bank_account'      => 'nullable|string|max:100',

            // ─── أرصدة الإجازات ───────────────────────────────────────────
            'annual_leave_balance' => 'nullable|integer|min:0|max:365',
            'sick_leave_balance'   => 'nullable|integer|min:0|max:365',

            'notes'             => 'nullable|string|max:1000',

            // ─── هيكل الراتب ─────────────────────────────────────────────
            'housing_allowance'   => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'food_allowance'      => 'nullable|numeric|min:0',
            'other_allowances'    => 'nullable|numeric|min:0',
            'social_insurance'    => 'nullable|numeric|min:0',
            'tax_deduction'       => 'nullable|numeric|min:0',
            'other_deductions'    => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'employee_number'      => 'رقم الموظف',
            'name'                 => 'الاسم',
            'name_en'              => 'الاسم بالإنجليزية',
            'national_id'          => 'الرقم الوطني',
            'nationality'          => 'الجنسية',
            'date_of_birth'        => 'تاريخ الميلاد',
            'gender'               => 'الجنس',
            'phone'                => 'رقم الهاتف',
            'email'                => 'البريد الإلكتروني',
            'address'              => 'العنوان',
            'photo'                => 'الصورة الشخصية',
            'department_id'        => 'القسم',
            'user_id'              => 'حساب المستخدم',
            'job_title'            => 'المسمى الوظيفي',
            'contract_type'        => 'نوع العقد',
            'hire_date'            => 'تاريخ التعيين',
            'contract_end_date'    => 'تاريخ انتهاء العقد',
            'basic_salary'         => 'الراتب الأساسي',
            'status'               => 'الحالة',
            'bank_name'            => 'اسم البنك',
            'bank_account'         => 'رقم الحساب البنكي',
            'annual_leave_balance' => 'رصيد الإجازة السنوية',
            'sick_leave_balance'   => 'رصيد الإجازة المرضية',
            'notes'                => 'ملاحظات',
            'housing_allowance'    => 'بدل السكن',
            'transport_allowance'  => 'بدل المواصلات',
            'food_allowance'       => 'بدل الغذاء',
            'other_allowances'     => 'بدلات أخرى',
            'social_insurance'     => 'التأمين الاجتماعي',
            'tax_deduction'        => 'خصم الضريبة',
            'other_deductions'     => 'خصومات أخرى',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'employee_number.unique' => 'رقم الموظف مستخدم مسبقاً، يرجى اختيار رقم آخر.',
            'national_id.unique'     => 'الرقم الوطني مسجل مسبقاً لموظف آخر.',
            'email.email'            => 'صيغة البريد الإلكتروني غير صحيحة.',
            'date_of_birth.before'   => 'تاريخ الميلاد يجب أن يكون قبل اليوم.',
            'contract_end_date.after'=> 'تاريخ انتهاء العقد يجب أن يكون بعد تاريخ التعيين.',
            'photo.image'            => 'يجب أن يكون الملف صورة (jpg, jpeg, png, webp).',
            'photo.max'              => 'حجم الصورة يجب ألا يتجاوز 2 ميغابايت.',
            'basic_salary.min'       => 'الراتب الأساسي يجب أن يكون 0 أو أكثر.',
            'gender.in'              => 'قيمة الجنس غير صحيحة.',
            'contract_type.in'       => 'نوع العقد غير صحيح.',
            'status.in'              => 'حالة الموظف غير صحيحة.',
        ];
    }
}
