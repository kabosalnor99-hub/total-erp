{{-- المسار: resources/views/customers/show.blade.php --}}
@extends('layouts.app')

@section('title', 'بيانات العميل — ' . $customer->name)

@section('content')
<div class="space-y-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $customer->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">بيانات وسجل العميل</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('customers.statement', $customer) }}"
               class="flex items-center gap-2 border border-primary text-primary px-4 py-2 rounded-lg hover:bg-primary hover:text-white transition">
                <i class="fa fa-file-lines"></i><span>كشف حساب</span>
            </a>
            @canPermission('customers.edit')
            <a href="{{ route('customers.edit', $customer) }}"
               class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition">
                <i class="fa fa-edit"></i><span>تعديل</span>
            </a>
            @endcanPermission
        </div>
    </div>

    {{-- رسالة نجاح --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- بطاقة البيانات --}}
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <div class="flex items-center gap-3 pb-3 border-b">
                <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center">
                    <i class="fa fa-user text-primary text-2xl"></i>
                </div>
                <div>
                    <p class="font-bold text-gray-800">{{ $customer->name }}</p>
                    @php
                        $cls = ['vip'=>'bg-yellow-100 text-yellow-700','regular'=>'bg-gray-100 text-gray-700','inactive'=>'bg-red-100 text-red-700'];
                        $lbl = ['vip'=>'VIP','regular'=>'عادي','inactive'=>'غير نشط'];
                    @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $cls[$customer->classification] }}">
                        {{ $lbl[$customer->classification] }}
                    </span>
                </div>
            </div>

            @foreach([
                ['icon'=>'fa-phone','label'=>'الهاتف','value'=>$customer->phone ?? '—'],
                ['icon'=>'fa-phone-volume','label'=>'هاتف بديل','value'=>$customer->phone_alt ?? '—'],
                ['icon'=>'fa-envelope','label'=>'البريد','value'=>$customer->email ?? '—'],
                ['icon'=>'fa-location-dot','label'=>'العنوان','value'=>$customer->address ?? '—'],
                ['icon'=>'fa-building','label'=>'الشركة','value'=>$customer->company_name ?? '—'],
                ['icon'=>'fa-receipt','label'=>'الرقم الضريبي','value'=>$customer->tax_number ?? '—'],
            ] as $row)
            <div class="flex items-start gap-3">
                <i class="fa {{ $row['icon'] }} text-gray-400 mt-0.5 w-4"></i>
                <div>
                    <p class="text-xs text-gray-400">{{ $row['label'] }}</p>
                    <p class="text-sm text-gray-700">{{ $row['value'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- الإحصائيات والسجل --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- بطاقات الإحصائيات --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-4 shadow-sm text-center">
                    <p class="text-xs text-gray-500 mb-1">إجمالي الفواتير</p>
                    <p class="text-xl font-bold text-gray-800">{{ number_format($stats['invoices_count']) }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm text-center">
                    <p class="text-xs text-gray-500 mb-1">إجمالي المبيعات</p>
                    <p class="text-xl font-bold text-primary">{{ number_format($stats['total_invoiced'], 2) }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm text-center">
                    <p class="text-xs text-gray-500 mb-1">إجمالي المدفوع</p>
                    <p class="text-xl font-bold text-green-600">{{ number_format($stats['total_paid'], 2) }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm text-center">
                    <p class="text-xs text-gray-500 mb-1">الرصيد المتبقي</p>
                    <p class="text-xl font-bold {{ $stats['balance'] > 0 ? 'text-red-600' : 'text-gray-700' }}">
                        {{ number_format($stats['balance'], 2) }}
                    </p>
                </div>
            </div>

            {{-- آخر الفواتير --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-700">آخر الفواتير</h2>
                    <a href="{{ route('invoices.index', ['customer_id'=>$customer->id]) }}"
                       class="text-xs text-primary hover:underline">عرض الكل</a>
                </div>
                @if($customer->invoices->isEmpty())
                    <p class="text-center text-gray-400 py-6 text-sm">لا توجد فواتير</p>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-right font-medium text-gray-600">رقم</th>
                                <th class="px-3 py-2 text-right font-medium text-gray-600">التاريخ</th>
                                <th class="px-3 py-2 text-right font-medium text-gray-600">الإجمالي</th>
                                <th class="px-3 py-2 text-right font-medium text-gray-600">الحالة</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($customer->invoices as $inv)
                            @php
                                $sc = ['draft'=>'bg-gray-100 text-gray-600','confirmed'=>'bg-blue-100 text-blue-700',
                                       'paid'=>'bg-green-100 text-green-700','partial'=>'bg-yellow-100 text-yellow-700',
                                       'cancelled'=>'bg-red-100 text-red-700'];
                                $sl = ['draft'=>'مسودة','confirmed'=>'مؤكدة','paid'=>'مدفوعة','partial'=>'جزئي','cancelled'=>'ملغاة'];
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-mono text-xs">{{ $inv->invoice_number }}</td>
                                <td class="px-3 py-2 text-gray-500">{{ $inv->created_at->format('Y/m/d') }}</td>
                                <td class="px-3 py-2 font-medium">{{ number_format($inv->total, 2) }}</td>
                                <td class="px-3 py-2">
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $sc[$inv->status] ?? '' }}">
                                        {{ $sl[$inv->status] ?? $inv->status }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <a href="{{ route('invoices.show', $inv) }}" class="text-primary hover:underline text-xs">عرض</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
