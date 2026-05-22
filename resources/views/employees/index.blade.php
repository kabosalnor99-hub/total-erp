{{-- المسار الكامل: resources/views/employees/index.blade.php --}}

@extends('layouts.app')

@section('title', 'الموظفون')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">الموظفون</h1>
            <p class="text-sm text-gray-500 mt-1">إجمالي {{ $stats['total'] }} موظف</p>
        </div>
        @canPermission('hr.create')
        <a href="{{ route('employees.create') }}"
           class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            إضافة موظف
        </a>
        @endcanPermission
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-teal-100 flex items-center justify-center">
                <i class="fa fa-users text-teal-600"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">الإجمالي</p>
                <p class="text-xl font-bold text-gray-800">{{ $stats['total'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                <i class="fa fa-circle-check text-green-600"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">نشطون</p>
                <p class="text-xl font-bold text-green-600">{{ $stats['active'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                <i class="fa fa-umbrella-beach text-blue-600"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">في إجازة</p>
                <p class="text-xl font-bold text-blue-600">{{ $stats['on_leave'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                <i class="fa fa-user-minus text-red-500"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">منتهية خدمتهم</p>
                <p class="text-xl font-bold text-red-500">{{ $stats['terminated'] }}</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="بحث بالاسم أو الرقم أو المسمى..."
                   class="flex-1 min-w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">

            <select name="department_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                <option value="">كل الأقسام</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                <option value="">كل الحالات</option>
                <option value="active"     {{ request('status') === 'active'     ? 'selected' : '' }}>نشط</option>
                <option value="on_leave"   {{ request('status') === 'on_leave'   ? 'selected' : '' }}>في إجازة</option>
                <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>منتهية خدمته</option>
            </select>

            <button type="submit"
                    class="bg-teal-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition">
                <i class="fa fa-search me-1"></i> بحث
            </button>
            @if(request()->hasAny(['search','department_id','status']))
                <a href="{{ route('employees.index') }}"
                   class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                    مسح
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-teal-600 text-white">
                <tr>
                    <th class="px-4 py-3 text-right font-medium">الموظف</th>
                    <th class="px-4 py-3 text-right font-medium">القسم</th>
                    <th class="px-4 py-3 text-right font-medium">المسمى الوظيفي</th>
                    <th class="px-4 py-3 text-right font-medium">تاريخ التعيين</th>
                    <th class="px-4 py-3 text-right font-medium">الراتب الأساسي</th>
                    <th class="px-4 py-3 text-right font-medium">الحالة</th>
                    <th class="px-4 py-3 text-center font-medium">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($employees as $emp)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $emp->photo_url }}" alt="{{ $emp->name }}"
                                 class="w-9 h-9 rounded-full object-cover border border-gray-200">
                            <div>
                                <p class="font-medium text-gray-800">{{ $emp->name }}</p>
                                <p class="text-xs text-gray-400">{{ $emp->employee_number }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $emp->department?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $emp->job_title }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $emp->hire_date->format('Y/m/d') }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">
                        {{ number_format($emp->basic_salary, 0) }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $emp->status === 'active'     ? 'bg-green-100 text-green-700' : '' }}
                            {{ $emp->status === 'on_leave'   ? 'bg-blue-100 text-blue-700'  : '' }}
                            {{ $emp->status === 'terminated' ? 'bg-red-100 text-red-600'    : '' }}
                        ">
                            {{ $emp->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('employees.show', $emp) }}"
                               class="text-teal-600 hover:text-teal-800 text-xs font-medium">
                                <i class="fa fa-eye"></i>
                            </a>
                            @canPermission('hr.edit')
                            <a href="{{ route('employees.edit', $emp) }}"
                               class="text-blue-500 hover:text-blue-700 text-xs font-medium">
                                <i class="fa fa-pen"></i>
                            </a>
                            @endcanPermission
                            @canPermission('hr.delete')
                            <form method="POST" action="{{ route('employees.destroy', $emp) }}"
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا الموظف؟')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 text-xs">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                            @endcanPermission
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                        <i class="fa fa-users text-3xl mb-2 block"></i>
                        لا يوجد موظفون
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($employees->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $employees->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
