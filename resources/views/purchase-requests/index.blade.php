{{-- المسار الكامل: resources/views/purchase-requests/index.blade.php --}}

@extends('layouts.app')

@section('title', 'طلبات الشراء')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">طلبات الشراء</h1>
            <p class="text-sm text-gray-500 mt-1">إجمالي {{ $requests->total() }} طلب</p>
        </div>
        @can('purchase-requests.create')
        <a href="{{ route('purchase-requests.create') }}"
           class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            طلب شراء جديد
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="رقم الطلب..."
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                <option value="">كل الحالات</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>معلق</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>معتمد</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>مرفوض</option>
                <option value="ordered"  {{ request('status') === 'ordered'  ? 'selected' : '' }}>تم الطلب</option>
            </select>
            <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">بحث</button>
            @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('purchase-requests.index') }}" class="border border-gray-200 text-gray-500 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm transition">مسح</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-teal-600 text-white text-xs">
                    <tr>
                        <th class="px-4 py-3 text-right font-medium">رقم الطلب</th>
                        <th class="px-4 py-3 text-right font-medium">الطالب</th>
                        <th class="px-4 py-3 text-right font-medium">عدد الأصناف</th>
                        <th class="px-4 py-3 text-right font-medium">مطلوب بتاريخ</th>
                        <th class="px-4 py-3 text-right font-medium">الحالة</th>
                        <th class="px-4 py-3 text-right font-medium">المعتمد من</th>
                        <th class="px-4 py-3 text-right font-medium">تاريخ الطلب</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($requests as $req)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-mono text-xs text-teal-700">{{ $req->request_number }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $req->user->name }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">
                                {{ $req->items_count ?? $req->items()->count() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ $req->needed_by ? $req->needed_by->format('Y/m/d') : '—' }}
                        </td>
                        <td class="px-4 py-3">
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
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $colors[$req->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $labels[$req->status] ?? $req->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $req->approver?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $req->created_at->format('Y/m/d') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 justify-end">
                                <a href="{{ route('purchase-requests.show', $req) }}"
                                   class="text-teal-600 hover:text-teal-800 text-xs font-medium">عرض</a>

                                @can('purchase-requests.approve')
                                @if($req->status === 'pending')
                                <form action="{{ route('purchase-requests.approve', $req) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium">اعتماد</button>
                                </form>
                                @endif
                                @endcan

                                @if($req->status === 'approved')
                                <a href="{{ route('purchase-orders.create', ['from_request' => $req->id]) }}"
                                   class="text-blue-600 hover:text-blue-800 text-xs font-medium">إنشاء أمر</a>
                                @endif

                                @can('purchase-requests.delete')
                                @if($req->status === 'pending')
                                <form action="{{ route('purchase-requests.destroy', $req) }}" method="POST" class="inline"
                                      onsubmit="return confirm('هل أنت متأكد؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs">حذف</button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <div class="text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-sm">لا توجد طلبات شراء</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
        <div class="p-4 border-t">
            {{ $requests->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
