{{-- المسار الكامل: resources/views/payroll/index.blade.php --}}

@extends('layouts.app')

@section('title', 'الرواتب الشهرية')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">الرواتب الشهرية</h1>
            <p class="text-sm text-gray-500 mt-1">إدارة رواتب الموظفين وقسائم الدفع</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('payroll.report', ['month' => $month, 'year' => $year]) }}"
               class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition">
                <i class="fa fa-chart-bar me-1"></i> تقرير الشهر
            </a>
            @if(!$alreadyGenerated)
            <a href="{{ route('payroll.generate') }}"
               class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition font-medium">
                <i class="fa fa-plus me-1"></i> توليد رواتب الشهر
            </a>
            @else
            <a href="{{ route('payroll.generate') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition font-medium">
                <i class="fa fa-plus me-1"></i> توليد موظفين إضافيين
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
        <i class="fa fa-circle-check me-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
        <i class="fa fa-circle-xmark me-2"></i>{{ session('error') }}
    </div>
    @endif

    {{-- فلتر الشهر والسنة --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">الشهر</label>
                <select name="month" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->locale('ar')->monthName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">السنة</label>
                <select name="year" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                    @foreach(range(now()->year, now()->year - 3) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">القسم</label>
                <select name="department_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                    <option value="">الكل</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">الحالة</label>
                <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                    <option value="">الكل</option>
                    <option value="draft"    {{ request('status') === 'draft'    ? 'selected' : '' }}>مسودة</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>معتمد</option>
                    <option value="paid"     {{ request('status') === 'paid'     ? 'selected' : '' }}>مدفوع</option>
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition">
                <i class="fa fa-search me-1"></i> بحث
            </button>
        </form>
    </div>

    {{-- ملخص الشهر --}}
    @if($totals && $totals->count > 0)
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">عدد الموظفين</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totals->count }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي الرواتب</p>
            <p class="text-2xl font-bold text-teal-600">{{ number_format($totals->total_gross, 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي الخصومات</p>
            <p class="text-2xl font-bold text-red-500">{{ number_format($totals->total_deductions, 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">صافي المدفوع</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($totals->total_net, 0) }}</p>
        </div>
    </div>

    {{-- اعتماد الكل --}}
    @php $hasDraft = $payrolls->where('status','draft')->count() @endphp
    @if($hasDraft > 0)
    <form method="POST" action="{{ route('payroll.approve-all') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">
        <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition"
                onclick="return confirm('اعتماد جميع الرواتب المسودة لهذا الشهر؟')">
            <i class="fa fa-check-double me-1"></i> اعتماد الكل ({{ $hasDraft }} راتب)
        </button>
    </form>
    @endif
    @endif

    {{-- الجدول --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($payrolls->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <i class="fa fa-money-bill-wave text-5xl mb-3 opacity-30"></i>
            <p class="text-lg font-medium">لا توجد رواتب لهذا الشهر</p>
            <p class="text-sm mt-1">استخدم زر «توليد رواتب الشهر» لإنشاء الرواتب</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-teal-600 text-white text-right">
                        <th class="px-4 py-3 font-medium">الموظف</th>
                        <th class="px-4 py-3 font-medium">القسم</th>
                        <th class="px-4 py-3 font-medium">الراتب الإجمالي</th>
                        <th class="px-4 py-3 font-medium">الخصومات</th>
                        <th class="px-4 py-3 font-medium">صافي الراتب</th>
                        <th class="px-4 py-3 font-medium">الحالة</th>
                        <th class="px-4 py-3 font-medium">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($payrolls as $payroll)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $payroll->employee->name }}</div>
                            <div class="text-xs text-gray-400">{{ $payroll->employee->employee_number }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $payroll->employee->department?->name ?? '—' }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-800">{{ number_format($payroll->gross_salary, 2) }}</td>
                        <td class="px-4 py-3 text-red-600">{{ number_format($payroll->total_deductions, 2) }}</td>
                        <td class="px-4 py-3 font-bold text-teal-700 text-base">{{ number_format($payroll->net_salary, 2) }}</td>
                        <td class="px-4 py-3">
                            @if($payroll->status === 'draft')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">مسودة</span>
                            @elseif($payroll->status === 'approved')
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">معتمد</span>
                            @else
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">مدفوع</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('payroll.show', $payroll) }}"
                                   class="text-teal-600 hover:text-teal-800 transition" title="عرض">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="{{ route('payroll.payslip', $payroll) }}"
                                   class="text-purple-600 hover:text-purple-800 transition" title="قسيمة PDF">
                                    <i class="fa fa-file-pdf"></i>
                                </a>
                                @if($payroll->status === 'draft')
                                <form method="POST" action="{{ route('payroll.approve', $payroll) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-blue-600 hover:text-blue-800 transition" title="اعتماد">
                                        <i class="fa fa-check"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('payroll.destroy', $payroll) }}" class="inline"
                                      onsubmit="return confirm('حذف هذا الراتب؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 transition" title="حذف">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                                @if($payroll->status === 'approved')
                                <button onclick="document.getElementById('pay-modal-{{ $payroll->id }}').classList.remove('hidden')"
                                        class="text-green-600 hover:text-green-800 transition text-xs font-medium" title="تسجيل دفع">
                                    <i class="fa fa-money-bill"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $payrolls->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Pay Modals --}}
@foreach($payrolls as $payroll)
@if($payroll->status === 'approved')
<div id="pay-modal-{{ $payroll->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">تسجيل دفع راتب</h3>
        <p class="text-sm text-gray-600 mb-4">{{ $payroll->employee->name }} — صافي الراتب: <strong class="text-teal-600">{{ number_format($payroll->net_salary, 2) }}</strong></p>
        <form method="POST" action="{{ route('payroll.mark-paid', $payroll) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الدفع</label>
                <input type="date" name="payment_date" value="{{ now()->toDateString() }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">طريقة الدفع</label>
                <select name="payment_method" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500" required>
                    <option value="cash">نقدي</option>
                    <option value="bank_transfer">تحويل بنكي</option>
                    <option value="check">شيك</option>
                </select>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('pay-modal-{{ $payroll->id }}').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">
                    إلغاء
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                    <i class="fa fa-check me-1"></i> تأكيد الدفع
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endforeach

@endsection
