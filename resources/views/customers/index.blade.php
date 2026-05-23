{{-- المسار: resources/views/customers/index.blade.php --}}
@extends('layouts.app')

@section('title', 'العملاء')

@section('content')
<div class="space-y-6">

    {{-- ─── رأس الصفحة ─────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">إدارة العملاء</h1>
            <p class="text-sm text-gray-500 mt-1">{{ number_format($stats['total']) }} عميل مسجل</p>
        </div>
        @canPermission('customers.create')
        <button onclick="openAddCustomerModal()"
           class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition font-medium">
            <i class="fa fa-plus"></i>
            <span>إضافة عميل</span>
        </button>
        @endcanPermission
    </div>

    {{-- ─── بطاقات الإحصائيات ───────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 mb-1">إجمالي العملاء</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-yellow-100">
            <p class="text-xs text-gray-500 mb-1">عملاء VIP</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['vip']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-red-100">
            <p class="text-xs text-gray-500 mb-1">لديهم مديونية</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($stats['with_balance']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-blue-100">
            <p class="text-xs text-gray-500 mb-1">إجمالي المديونية</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_debt'], 2) }}</p>
        </div>
    </div>

    {{-- ─── فلاتر البحث ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">بحث</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="الاسم، الهاتف، البريد..."
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">التصنيف</label>
                <select name="classification" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <option value="">الكل</option>
                    <option value="vip"      {{ request('classification') === 'vip'      ? 'selected' : '' }}>VIP</option>
                    <option value="regular"  {{ request('classification') === 'regular'  ? 'selected' : '' }}>عادي</option>
                    <option value="inactive" {{ request('classification') === 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">النوع</label>
                <select name="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <option value="">الكل</option>
                    <option value="individual" {{ request('type') === 'individual' ? 'selected' : '' }}>أفراد</option>
                    <option value="company"    {{ request('type') === 'company'    ? 'selected' : '' }}>شركات</option>
                </select>
            </div>
            <div class="flex items-center gap-2 self-end pb-0.5">
                <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                    <input type="checkbox" name="has_balance" value="1" {{ request('has_balance') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-primary">
                    <span>لديهم مديونية</span>
                </label>
            </div>
            <div class="flex gap-2 self-end">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-dark transition">
                    <i class="fa fa-search me-1"></i> بحث
                </button>
                <a href="{{ route('customers.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                    إعادة تعيين
                </a>
            </div>
        </form>
    </div>

    {{-- ─── جدول العملاء ────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">#</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">العميل</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الهاتف</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">التصنيف</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">النوع</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الفواتير</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الرصيد</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الحالة</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $customer->id }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                                    <span class="text-primary font-bold text-sm">{{ mb_substr($customer->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $customer->name }}</p>
                                    @if($customer->company_name)
                                        <p class="text-xs text-gray-400">{{ $customer->company_name }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dir-ltr text-right">{{ $customer->phone ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $colors = ['vip'=>'yellow','regular'=>'blue','inactive'=>'gray'];
                                $color  = $colors[$customer->classification] ?? 'gray';
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                bg-{{ $color }}-100 text-{{ $color }}-700">
                                {{ $customer->classification_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $customer->type_label }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs font-medium">
                                {{ number_format($customer->invoices_count) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-medium {{ $customer->balance > 0 ? 'text-red-600' : 'text-gray-500' }}">
                            {{ $customer->balance > 0 ? number_format($customer->balance, 2) : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($customer->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> نشط
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> متوقف
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('customers.show', $customer) }}"
                                   class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="عرض">
                                    <i class="fa fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('customers.statement', $customer) }}"
                                   class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition" title="كشف حساب">
                                    <i class="fa fa-file-lines text-xs"></i>
                                </a>
                                @canPermission('customers.edit')
                                <a href="{{ route('customers.edit', $customer) }}"
                                   class="p-1.5 text-orange-500 hover:bg-orange-50 rounded-lg transition" title="تعديل">
                                    <i class="fa fa-pen text-xs"></i>
                                </a>
                                @endcanPermission
                                @canPermission('customers.delete')
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا العميل؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف">
                                        <i class="fa fa-trash text-xs"></i>
                                    </button>
                                </form>
                                @endcanPermission
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                            <i class="fa fa-users text-4xl mb-3 block opacity-30"></i>
                            لا يوجد عملاء
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($customers->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modal: إضافة عميل --}}
<div id="addCustomerModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-bold text-gray-800">إضافة عميل جديد</h3>
            <button onclick="closeAddCustomerModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <form id="addCustomerForm" class="p-4 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الاسم *</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الهاتف</label>
                <input type="text" name="phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">العنوان</label>
                <input type="text" name="address" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <input type="hidden" name="type" value="individual">
            <input type="hidden" name="classification" value="regular">
            <input type="hidden" name="is_active" value="1">
            <div class="flex gap-2 pt-2">
                <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition font-medium">
                    إضافة
                </button>
                <button type="button" onclick="closeAddCustomerModal()" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition font-medium">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddCustomerModal() {
    document.getElementById('addCustomerModal').classList.remove('hidden');
    document.getElementById('addCustomerModal').classList.add('flex');
}

function closeAddCustomerModal() {
    document.getElementById('addCustomerModal').classList.add('hidden');
    document.getElementById('addCustomerModal').classList.remove('flex');
    document.getElementById('addCustomerForm').reset();
}

document.getElementById('addCustomerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch('/customers', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            closeAddCustomerModal();
            window.location.reload();
        } else {
            alert('حدث خطأ أثناء إضافة العميل');
        }
    } catch (error) {
        alert('حدث خطأ في الاتصال');
    }
});
</script>

@endsection
