{{-- المسار الكامل: resources/views/purchase-requests/show.blade.php --}}

@extends('layouts.app')

@section('title', 'طلب شراء ' . $purchaseRequest->request_number)

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('purchase-requests.index') }}" class="hover:text-teal-600">طلبات الشراء</a>
                <span>/</span>
                <span class="text-gray-700 font-medium">{{ $purchaseRequest->request_number }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">طلب شراء</h1>
        </div>

        <div class="flex items-center gap-2">
            @can('purchase-requests.approve')
            @if($purchaseRequest->status === 'pending')

            {{-- زر الاعتماد (POST - بدون @method) --}}
            <form action="{{ route('purchase-requests.approve', $purchaseRequest) }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    اعتماد
                </button>
            </form>

            {{-- زر الرفض يفتح modal --}}
            <button type="button" onclick="document.getElementById('rejectModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-4 py-2 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                رفض
            </button>

            @endif
            @endcan

            @if($purchaseRequest->status === 'approved')
            @can('purchase-orders.create')
            <a href="{{ route('purchase-orders.create', ['from_request' => $purchaseRequest->id]) }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                إنشاء أمر شراء
            </a>
            @endcan
            @endif

            <a href="{{ route('purchase-requests.index') }}"
               class="border border-gray-200 text-gray-500 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm transition">
                رجوع
            </a>
        </div>
    </div>

    {{-- بطاقة التفاصيل --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div>
                <p class="text-xs text-gray-400 mb-1">رقم الطلب</p>
                <p class="font-mono text-teal-700 font-semibold">{{ $purchaseRequest->request_number }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-400 mb-1">الطالب</p>
                <p class="text-gray-800">{{ $purchaseRequest->user->name }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-400 mb-1">الحالة</p>
                @php
                    $colors = [
                        'pending'  => 'bg-yellow-100 text-yellow-700',
                        'approved' => 'bg-green-100 text-green-700',
                        'rejected' => 'bg-red-100 text-red-700',
                        'ordered'  => 'bg-blue-100 text-blue-700',
                    ];
                    $labels = [
                        'pending'  => 'معلق',
                        'approved' => 'معتمد',
                        'rejected' => 'مرفوض',
                        'ordered'  => 'تم الطلب',
                    ];
                @endphp
                <span class="px-2 py-0.5 text-xs rounded-full {{ $colors[$purchaseRequest->status] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ $labels[$purchaseRequest->status] ?? $purchaseRequest->status }}
                </span>
            </div>

            <div>
                <p class="text-xs text-gray-400 mb-1">مطلوب بتاريخ</p>
                <p class="text-gray-700">{{ $purchaseRequest->needed_by ? $purchaseRequest->needed_by->format('Y/m/d') : '—' }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-400 mb-1">المعتمد من</p>
                <p class="text-gray-700">{{ $purchaseRequest->approver?->name ?? '—' }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-400 mb-1">تاريخ الإنشاء</p>
                <p class="text-gray-700">{{ $purchaseRequest->created_at->format('Y/m/d H:i') }}</p>
            </div>

            @if($purchaseRequest->notes)
            <div class="md:col-span-3">
                <p class="text-xs text-gray-400 mb-1">ملاحظات</p>
                <p class="text-gray-700 text-sm">{{ $purchaseRequest->notes }}</p>
            </div>
            @endif

            @if($purchaseRequest->rejection_reason)
            <div class="md:col-span-3">
                <p class="text-xs text-red-400 mb-1">سبب الرفض</p>
                <p class="text-red-600 text-sm">{{ $purchaseRequest->rejection_reason }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- جدول الأصناف --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700">الأصناف المطلوبة</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-right font-medium">#</th>
                        <th class="px-4 py-3 text-right font-medium">المنتج</th>
                        <th class="px-4 py-3 text-right font-medium">الكمية</th>
                        <th class="px-4 py-3 text-right font-medium">السعر التقديري</th>
                        <th class="px-4 py-3 text-right font-medium">الإجمالي التقديري</th>
                        <th class="px-4 py-3 text-right font-medium">ملاحظات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($purchaseRequest->items as $i => $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $item->product->name_ar }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ number_format($item->quantity, 2) }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $item->estimated_price ? number_format($item->estimated_price, 2) : '—' }}</td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ ($item->estimated_price && $item->quantity) ? number_format($item->estimated_price * $item->quantity, 2) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $item->notes ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- رابط أمر الشراء إن وجد --}}
    @if($purchaseRequest->purchaseOrder)
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
        <span class="text-blue-700 text-sm">تم إنشاء أمر شراء مرتبط بهذا الطلب</span>
        <a href="{{ route('purchase-orders.show', $purchaseRequest->purchaseOrder) }}"
           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            عرض الأمر {{ $purchaseRequest->purchaseOrder->order_number }}
        </a>
    </div>
    @endif

</div>

{{-- Modal الرفض --}}
<div id="rejectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">رفض طلب الشراء</h3>

        <form action="{{ route('purchase-requests.reject', $purchaseRequest) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">سبب الرفض <span class="text-red-500">*</span></label>
                <textarea name="rejection_reason" rows="3" required maxlength="500"
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-400 focus:border-red-400"
                          placeholder="اكتب سبب الرفض..."></textarea>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')"
                        class="border border-gray-200 text-gray-500 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm transition">
                    إلغاء
                </button>
                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    تأكيد الرفض
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
