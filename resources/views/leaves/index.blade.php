{{-- المسار الكامل: resources/views/leaves/index.blade.php --}}

@extends('layouts.app')

@section('title', 'إدارة الإجازات')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">إدارة الإجازات</h1>
            <p class="text-sm text-gray-500 mt-1">متابعة طلبات الإجازات واعتمادها</p>
        </div>
        <a href="{{ route('leaves.create') }}"
           class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition font-medium">
            <i class="fa fa-plus me-1"></i> طلب إجازة جديد
        </a>
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

    {{-- إحصائيات --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
            <p class="text-xs text-yellow-600 mb-1">في انتظار الاعتماد</p>
            <p class="text-3xl font-bold text-yellow-700">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <p class="text-xs text-green-600 mb-1">معتمدة هذا العام</p>
            <p class="text-3xl font-bold text-green-700">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
            <p class="text-xs text-red-600 mb-1">مرفوضة هذا العام</p>
            <p class="text-3xl font-bold text-red-700">{{ $stats['rejected'] }}</p>
        </div>
    </div>

    {{-- فلاتر --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">الموظف</label>
                <select name="employee_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                    <option value="">الكل</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">نوع الإجازة</label>
                <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                    <option value="">الكل</option>
                    <option value="annual"    {{ request('type') === 'annual'    ? 'selected' : '' }}>سنوية</option>
                    <option value="sick"      {{ request('type') === 'sick'      ? 'selected' : '' }}>مرضية</option>
                    <option value="emergency" {{ request('type') === 'emergency' ? 'selected' : '' }}>طارئة</option>
                    <option value="unpaid"    {{ request('type') === 'unpaid'    ? 'selected' : '' }}>بدون راتب</option>
                    <option value="other"     {{ request('type') === 'other'     ? 'selected' : '' }}>أخرى</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">الحالة</label>
                <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                    <option value="">الكل</option>
                    <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>في الانتظار</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>معتمدة</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>مرفوضة</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">من تاريخ</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">إلى تاريخ</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition">
                <i class="fa fa-search me-1"></i> بحث
            </button>
            <a href="{{ route('leaves.index') }}"
               class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition">
                إعادة تعيين
            </a>
        </form>
    </div>

    {{-- جدول الإجازات --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($leaves->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <i class="fa fa-umbrella-beach text-5xl mb-3 opacity-30"></i>
            <p class="text-lg font-medium">لا توجد إجازات مطابقة</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-teal-600 text-white text-right">
                        <th class="px-4 py-3 font-medium">الموظف</th>
                        <th class="px-4 py-3 font-medium">نوع الإجازة</th>
                        <th class="px-4 py-3 font-medium">من</th>
                        <th class="px-4 py-3 font-medium">إلى</th>
                        <th class="px-4 py-3 font-medium">الأيام</th>
                        <th class="px-4 py-3 font-medium">الحالة</th>
                        <th class="px-4 py-3 font-medium">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($leaves as $leave)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $leave->employee->name }}</div>
                            <div class="text-xs text-gray-400">{{ $leave->employee->department?->name ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $typeLabels = ['annual'=>'سنوية','sick'=>'مرضية','emergency'=>'طارئة','unpaid'=>'بدون راتب','other'=>'أخرى'];
                                $typeColors = ['annual'=>'blue','sick'=>'red','emergency'=>'orange','unpaid'=>'gray','other'=>'purple'];
                                $color = $typeColors[$leave->type] ?? 'gray';
                            @endphp
                            <span class="px-2 py-1 bg-{{ $color }}-100 text-{{ $color }}-700 rounded-full text-xs font-medium">
                                {{ $typeLabels[$leave->type] ?? $leave->type }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $leave->start_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $leave->end_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $leave->days }} يوم</td>
                        <td class="px-4 py-3">
                            @if($leave->status === 'pending')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">في الانتظار</span>
                            @elseif($leave->status === 'approved')
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">معتمدة</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">مرفوضة</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if($leave->status === 'pending')
                                <form method="POST" action="{{ route('leaves.approve', $leave) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 transition text-xs font-medium px-2 py-1 bg-green-50 rounded" title="اعتماد">
                                        <i class="fa fa-check me-1"></i>اعتماد
                                    </button>
                                </form>
                                <button onclick="document.getElementById('reject-modal-{{ $leave->id }}').classList.remove('hidden')"
                                        class="text-red-600 hover:text-red-800 transition text-xs font-medium px-2 py-1 bg-red-50 rounded" title="رفض">
                                    <i class="fa fa-xmark me-1"></i>رفض
                                </button>
                                @endif
                                @if($leave->reason)
                                <span class="text-gray-400 cursor-pointer" title="{{ $leave->reason }}">
                                    <i class="fa fa-comment"></i>
                                </span>
                                @endif
                                <form method="POST" action="{{ route('leaves.destroy', $leave) }}" class="inline"
                                      onsubmit="return confirm('حذف هذا الطلب؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition" title="حذف">
                                        <i class="fa fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $leaves->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Reject Modals --}}
@foreach($leaves as $leave)
@if($leave->status === 'pending')
<div id="reject-modal-{{ $leave->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">رفض طلب الإجازة</h3>
        <p class="text-sm text-gray-600 mb-4">{{ $leave->employee->name }} — {{ $leave->days }} أيام</p>
        <form method="POST" action="{{ route('leaves.reject', $leave) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">سبب الرفض <span class="text-red-500">*</span></label>
                <textarea name="rejection_reason" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500"
                          placeholder="اذكر سبب الرفض..." required></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('reject-modal-{{ $leave->id }}').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">
                    إلغاء
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                    <i class="fa fa-xmark me-1"></i> تأكيد الرفض
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endforeach

@endsection
