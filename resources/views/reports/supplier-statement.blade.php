{{-- المسار: resources/views/reports/purchases/supplier-statement.blade.php --}}

@extends('layouts.app')

@section('title', __('reports.supplier_statement'))

@section('content')
<div class="p-6" dir="rtl">

    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('reports.supplier_statement') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('reports.supplier_statement_desc') }}</p>
        </div>
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <x-heroicon-o-arrow-right class="w-4 h-4"/>
            العودة للتقارير
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">المورد <span class="text-red-500">*</span></label>
                <select name="supplier_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                    <option value="">اختر المورد</option>
                    @foreach(\App\Models\Supplier::orderBy('name')->get() as $supplier)
                        <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id || (isset($data['supplier']) && $data['supplier']->id == $supplier->id))>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">من تاريخ</label>
                <input type="date" name="date_from" value="{{ request('date_from', $data['date_from'] ?? now()->startOfYear()->toDateString()) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">إلى تاريخ</label>
                <input type="date" name="date_to" value="{{ request('date_to', $data['date_to'] ?? now()->toDateString()) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-cyan-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-cyan-700 transition">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 inline-block ml-1"/>
                    عرض الكشف
                </button>
            </div>
        </div>
    </form>

    @if(isset($data['supplier']))
    {{-- Supplier Info Card --}}
    <div class="bg-white rounded-xl border border-cyan-200 p-5 mb-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-cyan-100 flex items-center justify-center">
                    <x-heroicon-o-truck class="w-6 h-6 text-cyan-600"/>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">{{ $data['supplier']->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $data['supplier']->email ?? '' }} {{ $data['supplier']->phone ? '— ' . $data['supplier']->phone : '' }}</p>
                </div>
            </div>
            <div class="text-sm text-gray-400">
                {{ \Carbon\Carbon::parse($data['date_from'])->format('Y/m/d') }} — {{ \Carbon\Carbon::parse($data['date_to'])->format('Y/m/d') }}
            </div>
        </div>
    </div>

    {{-- Balance Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي أوامر الشراء</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($data['total_orders'], 2) }} <span class="text-sm font-normal text-gray-400">ج.س</span></p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي المدفوعات</p>
            <p class="text-2xl font-bold text-green-700">{{ number_format($data['total_payments'], 2) }} <span class="text-sm font-normal text-gray-400">ج.س</span></p>
        </div>
        <div class="bg-white rounded-xl border border-{{ $data['balance'] > 0 ? 'red' : 'gray' }}-200 p-4">
            <p class="text-xs text-gray-500 mb-1">الرصيد المستحق</p>
            <p class="text-2xl font-bold text-{{ $data['balance'] > 0 ? 'red-600' : 'gray-800' }}">{{ number_format($data['balance'], 2) }} <span class="text-sm font-normal text-gray-400">ج.س</span></p>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-5">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <x-heroicon-o-shopping-cart class="w-4 h-4 text-gray-400"/>
            <h2 class="font-semibold text-gray-700">أوامر الشراء</h2>
            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $data['orders']->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">رقم الأمر</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">تاريخ الأمر</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">الحالة</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">المبلغ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data['orders'] as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-cyan-700">{{ $order->order_number ?? '#' . $order->id }}</td>
                        <td class="px-4 py-3 text-center text-gray-500 text-xs">{{ \Carbon\Carbon::parse($order->order_date)->format('Y/m/d') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                @if($order->status === 'received') bg-green-100 text-green-700
                                @elseif($order->status === 'ordered') bg-blue-100 text-blue-700
                                @else bg-gray-100 text-gray-600 @endif">
                                {{ match($order->status) {
                                    'received' => 'مستلم',
                                    'ordered'  => 'مُرسَل',
                                    'partial'  => 'جزئي',
                                    default    => $order->status
                                } }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-left font-mono text-sm text-gray-700">{{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">لا توجد أوامر شراء</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr class="font-semibold">
                        <td colspan="3" class="px-4 py-3 text-gray-700">الإجمالي</td>
                        <td class="px-4 py-3 text-left font-mono">{{ number_format($data['total_orders'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Payments Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-5">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <x-heroicon-o-banknotes class="w-4 h-4 text-gray-400"/>
            <h2 class="font-semibold text-gray-700">المدفوعات</h2>
            <span class="text-xs bg-green-100 text-green-600 px-2 py-0.5 rounded-full">{{ $data['payments']->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">رقم الدفعة</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">تاريخ الدفع</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">طريقة الدفع</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">المبلغ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data['payments'] as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-green-700">{{ $payment->reference ?? '#' . $payment->id }}</td>
                        <td class="px-4 py-3 text-center text-gray-500 text-xs">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y/m/d') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $payment->payment_method ?? '-' }}</td>
                        <td class="px-4 py-3 text-left font-mono text-sm text-green-700">{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">لا توجد مدفوعات</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr class="font-semibold">
                        <td colspan="3" class="px-4 py-3 text-gray-700">الإجمالي</td>
                        <td class="px-4 py-3 text-left font-mono text-green-700">{{ number_format($data['total_payments'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Balance Footer --}}
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-center justify-between">
        <p class="font-semibold text-gray-700">الرصيد المستحق للمورد</p>
        <p class="text-xl font-bold {{ $data['balance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
            {{ number_format($data['balance'], 2) }} ج.س
        </p>
    </div>

    {{-- Export --}}
    <div class="mt-4 flex gap-3">
        <a href="{{ route('reports.export-pdf', 'supplier-statement') }}?{{ http_build_query(request()->all()) }}"
           class="flex items-center gap-2 border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4"/> تصدير PDF
        </a>
    </div>

    @else
    {{-- Prompt to select a supplier --}}
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <x-heroicon-o-truck class="w-12 h-12 text-gray-300 mx-auto mb-3"/>
        <p class="text-gray-500">اختر مورداً من القائمة أعلاه لعرض كشف الحساب</p>
    </div>
    @endif

</div>
@endsection
