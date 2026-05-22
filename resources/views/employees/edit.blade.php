{{-- المسار الكامل: resources/views/employees/edit.blade.php --}}

@extends('layouts.app')

@section('title', 'تعديل بيانات الموظف')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('employees.show', $employee) }}" class="text-gray-400 hover:text-teal-600 transition">
            <i class="fa fa-arrow-right text-lg"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">تعديل بيانات الموظف</h1>
            <p class="text-sm text-gray-500">{{ $employee->name }} — {{ $employee->employee_number }}</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <ul class="list-disc list-inside space-y-1 text-sm text-red-600">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- البيانات الشخصية --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">
                <i class="fa fa-user text-teal-500 me-2"></i> البيانات الشخصية
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم الموظف</label>
                    <input type="text" value="{{ $employee->employee_number }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500" readonly>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الاسم الكامل (عربي) <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $employee->name) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الاسم (إنجليزي)</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $employee->name_en) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent" dir="ltr">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الرقم الوطني</label>
                    <input type="text" name="national_id" value="{{ old('national_id', $employee->national_id) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الميلاد</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الجنس <span class="text-red-500">*</span></label>
                    <select name="gender" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500" required>
                        <option value="male"   {{ old('gender', $employee->gender) === 'male'   ? 'selected' : '' }}>ذكر</option>
                        <option value="female" {{ old('gender', $employee->gender) === 'female' ? 'selected' : '' }}>أنثى</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الجنسية</label>
                    <input type="text" name="nationality" value="{{ old('nationality', $employee->nationality) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">صورة الموظف</label>
                    @if($employee->photo)
                        <div class="mb-2">
                            <img src="{{ asset('storage/'.$employee->photo) }}" alt="صورة الموظف"
                                 class="w-16 h-16 rounded-full object-cover border-2 border-teal-200">
                        </div>
                    @endif
                    <input type="file" name="photo" accept="image/*"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 file:me-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-teal-50 file:text-teal-700">
                    <p class="text-xs text-gray-400 mt-1">اتركه فارغاً للإبقاء على الصورة الحالية</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" dir="ltr"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">العنوان</label>
                    <textarea name="address" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">{{ old('address', $employee->address) }}</textarea>
                </div>
            </div>
        </div>

        {{-- البيانات الوظيفية --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">
                <i class="fa fa-briefcase text-teal-500 me-2"></i> البيانات الوظيفية
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">المسمى الوظيفي <span class="text-red-500">*</span></label>
                    <input type="text" name="job_title" value="{{ old('job_title', $employee->job_title) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">القسم</label>
                    <select name="department_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                        <option value="">-- اختر القسم --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">نوع العقد <span class="text-red-500">*</span></label>
                    <select name="contract_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500" required>
                        <option value="permanent"  {{ old('contract_type', $employee->contract_type) === 'permanent'  ? 'selected' : '' }}>دائم</option>
                        <option value="temporary"  {{ old('contract_type', $employee->contract_type) === 'temporary'  ? 'selected' : '' }}>مؤقت</option>
                        <option value="part_time"  {{ old('contract_type', $employee->contract_type) === 'part_time'  ? 'selected' : '' }}>جزء من الوقت</option>
                        <option value="contract"   {{ old('contract_type', $employee->contract_type) === 'contract'   ? 'selected' : '' }}>عقد</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">حساب المستخدم (اختياري)</label>
                    <select name="user_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                        <option value="">-- لا يوجد --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $employee->user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ التعيين <span class="text-red-500">*</span></label>
                    <input type="date" name="hire_date" value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ انتهاء العقد</label>
                    <input type="date" name="contract_end_date" value="{{ old('contract_end_date', $employee->contract_end_date?->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الحالة <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500" required>
                        <option value="active"     {{ old('status', $employee->status) === 'active'     ? 'selected' : '' }}>نشط</option>
                        <option value="on_leave"   {{ old('status', $employee->status) === 'on_leave'   ? 'selected' : '' }}>في إجازة</option>
                        <option value="terminated" {{ old('status', $employee->status) === 'terminated' ? 'selected' : '' }}>منتهية خدمته</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الراتب الأساسي <span class="text-red-500">*</span></label>
                    <input type="number" name="basic_salary" value="{{ old('basic_salary', $employee->basic_salary) }}" step="0.01" min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent" required>
                </div>
            </div>
        </div>

        {{-- البيانات البنكية وأرصدة الإجازات --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">
                <i class="fa fa-building-columns text-teal-500 me-2"></i> البيانات البنكية وأرصدة الإجازات
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم البنك</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم الحساب البنكي</label>
                    <input type="text" name="bank_account" value="{{ old('bank_account', $employee->bank_account) }}" dir="ltr"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رصيد الإجازة السنوية (يوم)</label>
                    <input type="number" name="annual_leave_balance" value="{{ old('annual_leave_balance', $employee->annual_leave_balance) }}" min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رصيد الإجازة المرضية (يوم)</label>
                    <input type="number" name="sick_leave_balance" value="{{ old('sick_leave_balance', $employee->sick_leave_balance) }}" min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">{{ old('notes', $employee->notes) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Buttons --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('employees.show', $employee) }}"
               class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">
                إلغاء
            </a>
            <button type="submit"
                    class="px-5 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition font-medium">
                <i class="fa fa-save me-1"></i> حفظ التعديلات
            </button>
        </div>
    </form>
</div>
@endsection
