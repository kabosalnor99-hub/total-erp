{{-- المسار الكامل: resources/views/suppliers/index.blade.php --}}

@extends('layouts.app')

@section('title', 'الموردون')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">الموردون</h1>
            <p class="text-sm text-gray-500 mt-1">إجمالي {{ $suppliers->total() }} مورد</p>
        </div>
        @can('suppliers.create')
        <a href="{{ route('suppliers.create') }}"
           class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            إضافة مورد
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="بحث بالاسم أو الهاتف..."
                   class="flex-1 min-w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">

            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                <option value="">كل الحالات</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>نشط</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير نشط</option>
            </select>

            <select name="rating" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                <option value="">كل التقييمات</option>
                @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} نجوم</option>
                @endfor
            </select>

            <button type="submit"
                    class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm transition">
                بحث
            </button>
            @if(request()->hasAny(['search','status','rating']))
            <a href="{{ route('suppliers.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
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
                    <th class="px-4 py-3 text-right font-medium">#</th>
                    <th class="px-4 py-3 text-right font-medium">اسم المورد</th>
                    <th class="px-4 py-3 text-right font-medium">الهاتف</th>
                    <th class="px-4 py-3 text-right font-medium">شروط الدفع</th>
                    <th class="px-4 py-3 text-right font-medium">التقييم</th>
                    <th class="px-4 py-3 text-right font-medium">الرصيد المستحق</th>
                    <th class="px-4 py-3 text-right font-medium">عدد الأوامر</th>
                    <th class="px-4 py-3 text-right font-medium">الحالة</th>
                    <th class="px-4 py-3 text-right font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($suppliers as $supplier)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $supplier->name }}</div>
                        @if($supplier->company_name)
                            <div class="text-xs text-gray-400">{{ $supplier->company_name }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $supplier->phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $supplier->payment_terms_label }}</td>
                    <td class="px-4 py-3 text-yellow-500 text-base">{{ $supplier->rating_stars }}</td>
                    <td class="px-4 py-3">
                        <span class="{{ $supplier->balance > 0 ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                            {{ number_format($supplier->balance, 2) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $supplier->purchase_orders_count }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            {{ $supplier->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $supplier->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('suppliers.show', $supplier) }}"
                               class="text-teal-600 hover:text-teal-800 text-xs font-medium">عرض</a>
                            @can('suppliers.edit')
                            <a href="{{ route('suppliers.edit', $supplier) }}"
                               class="text-blue-600 hover:text-blue-800 text-xs font-medium">تعديل</a>
                            @endcan
                            <a href="{{ route('suppliers.statement', $supplier) }}"
                               class="text-purple-600 hover:text-purple-800 text-xs font-medium">كشف</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                        لا يوجد موردون
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($suppliers->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
