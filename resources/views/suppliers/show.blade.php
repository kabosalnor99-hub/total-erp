{{-- المسار الكامل: resources/views/suppliers/show.blade.php --}}

@extends('layouts.app')

@section('title', $supplier->name)

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('suppliers.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $supplier->name }}</h1>
                @if($supplier->company_name)
                    <p class="text-sm text-gray-500">{{ $supplier->company_name }}</p>
                @endif
            </div>
            <span class="px-2 py-1 text-xs font-medium rounded-full
                {{ $supplier->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $supplier->status_label }}
            </span>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('suppliers.statement', $supplier) }}"
               class="inline-flex items-center gap-2 border border-teal-600 text-teal-600 hover:bg-teal-50 px-4 py-2 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                كشف الحساب
            </a>
            @can('suppliers.edit')
            <a href="{{ route('suppliers.edit', $supplier) }}"
               class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                تعديل
            </a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- بيانات المورد --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-700 border-b pb-2">بيانات المورد</h2>

            @if($supplier->phone)
            <div class="flex items-center gap-3 text-sm">
                <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <span class="text-gray-600">{{ $supplier->phone }}</span>
            </div>
            @endif

            @if($supplier->email)
            <div class="flex items-center gap-3 text-sm">
                <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span class="text-gray-600">{{ $supplier->email }}</span>
            </div>
            @endif

            @if($supplier->address)
            <div class="flex items-start gap-3 text-sm">
                <svg class="w-4 h-4 text-teal-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="text-gray-600">{{ $supplier->address }}</span>
            </div>
            @endif

            @if($supplier->tax_number)
            <div class="flex items-center gap-3 text-sm">
                <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-gray-600">الرقم الضريبي: {{ $supplier->tax_number }}</span>
            </div>
            @endif

            <div class="flex items-center gap-3 text-sm">
                <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-gray-600">شروط الدفع: {{ $supplier->payment_terms_label }}</span>
            </div>

            <div class="flex items-center gap-3 text-sm">
                <svg class="w-4 h-4 text-yellow-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span class="text-gray-600">التقييم: {{ $supplier->rating_stars }}</span>
            </div>

            @if($supplier->notes)
            <div class="mt-3 p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
                {{ $supplier->notes }}
            </div>
            @endif
        </div>

        {{-- الإحصائيات المالية --}}
        <div class="lg:col-span-2 grid grid-cols-2 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm text-gray-500">إجمالي المشتريات</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($stats['total_purchases'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">جنيه سوداني</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm text-gray-500">إجمالي المدفوع</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($stats['total_paid'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">جنيه سوداني</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm text-gray-500">المستحق للمورد</p>
                <p class="text-2xl font-bold text-red-500 mt-1">{{ number_format($stats['outstanding'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">جنيه سوداني</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm text-gray-500">عدد الأوامر</p>
                <p class="text-2xl font-bold text-teal-600 mt-1">{{ $stats['orders_count'] }}</p>
                <p class="text-xs text-gray-400 mt-1">أمر شراء</p>
            </div>

            {{-- تسجيل دفعة --}}
            @can('suppliers.pay')
            <div class="col-span-2 bg-teal-50 rounded-xl border border-teal-100 p-5" x-data="{ open: false }">
                <button @click="open = !open"
                        class="w-full flex items-center justify-between text-teal-700 font-medium text-sm">
                    <span>تسجيل دفعة للمورد</span>
                    <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <form x-show="open" x-cloak action="{{ route('suppliers.pay', $supplier) }}" method="POST"
                      class="mt-4 grid grid-cols-2 gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">المبلغ <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01" required
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">طريقة الدفع <span class="text-red-500">*</span></label>
                        <select name="method" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                            <option value="cash">نقدي</option>
                            <option value="bank_transfer">تحويل بنكي</option>
                            <option value="check">شيك</option>
                            <option value="other">أخرى</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">تاريخ الدفع <span class="text-red-500">*</span></label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">رقم المرجع</label>
                        <input type="text" name="reference"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-600 mb-1">ملاحظات</label>
                        <input type="text" name="notes"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <div class="col-span-2 flex justify-end">
                        <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                            تسجيل الدفعة
                        </button>
                    </div>
                </form>
            </div>
            @endcan
        </div>
    </div>

    {{-- آخر أوامر الشراء --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between p-5 border-b">
            <h2 class="text-base font-semibold text-gray-700">آخر أوامر الشراء</h2>
            <a href="{{ route('purchase-orders.index', ['supplier_id' => $supplier->id]) }}"
               class="text-sm text-teal-600 hover:underline">عرض الكل</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-right font-medium">رقم الأمر</th>
                        <th class="px-4 py-3 text-right font-medium">التاريخ</th>
                        <th class="px-4 py-3 text-right font-medium">الإجمالي</th>
                        <th class="px-4 py-3 text-right font-medium">المدفوع</th>
                        <th class="px-4 py-3 text-right font-medium">الحالة</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($supplier->purchaseOrders as $order)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-mono text-xs text-teal-700">{{ $order->order_number }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $order->created_at->format('Y/m/d') }}</td>
                        <td class="px-4 py-3 font-medium">{{ number_format($order->total, 2) }}</td>
                        <td class="px-4 py-3 text-green-600">{{ number_format($order->amount_paid, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs rounded-full
                                @switch($order->status)
                                    @case('draft') bg-gray-100 text-gray-600 @break
                                    @case('sent') bg-blue-100 text-blue-600 @break
                                    @case('partial') bg-yellow-100 text-yellow-600 @break
                                    @case('received') bg-green-100 text-green-600 @break
                                    @case('cancelled') bg-red-100 text-red-600 @break
                                @endswitch">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('purchase-orders.show', $order) }}" class="text-teal-600 hover:underline text-xs">عرض</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">لا توجد أوامر شراء بعد</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
