{{-- المسار الكامل: resources/views/accounts/index.blade.php --}}
@extends('layouts.app')

@section('title', 'دليل الحسابات')

@section('content')
<div class="p-6" x-data="{ viewMode: 'table', showCreate: false }">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">دليل الحسابات</h1>
            <p class="text-sm text-gray-500 mt-1">إدارة شجرة الحسابات المحاسبية</p>
        </div>
        <div class="flex gap-2">
            <button @click="viewMode = viewMode === 'table' ? 'tree' : 'table'"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm">
                <i :class="viewMode === 'table' ? 'fa fa-sitemap' : 'fa fa-list'"></i>
                <span x-text="viewMode === 'table' ? 'عرض شجري' : 'عرض جدول'"></span>
            </button>
            @canPermission('accounts.create')
            <a href="{{ route('accounts.create') }}"
               class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition text-sm">
                <i class="fa fa-plus"></i>
                حساب جديد
            </a>
            @endcanPermission
        </div>
    </div>

    {{-- بطاقات الإحصاء --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border-r-4 border-blue-500">
            <p class="text-xs text-gray-500">إجمالي الحسابات</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-r-4 border-blue-400">
            <p class="text-xs text-gray-500">الأصول</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['assets'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-r-4 border-red-400">
            <p class="text-xs text-gray-500">الخصوم</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['liabilities'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-r-4 border-green-400">
            <p class="text-xs text-gray-500">الإيرادات</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['revenues'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-r-4 border-orange-400">
            <p class="text-xs text-gray-500">المصروفات</p>
            <p class="text-2xl font-bold text-orange-600">{{ $stats['expenses'] }}</p>
        </div>
    </div>

    {{-- فلاتر البحث --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs text-gray-500 mb-1">بحث</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="كود أو اسم الحساب..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>
            <div class="w-44">
                <label class="block text-xs text-gray-500 mb-1">النوع</label>
                <select name="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <option value="">الكل</option>
                    <option value="asset"     {{ request('type')=='asset'     ? 'selected' : '' }}>أصول</option>
                    <option value="liability" {{ request('type')=='liability' ? 'selected' : '' }}>خصوم</option>
                    <option value="equity"    {{ request('type')=='equity'    ? 'selected' : '' }}>حقوق ملكية</option>
                    <option value="revenue"   {{ request('type')=='revenue'   ? 'selected' : '' }}>إيرادات</option>
                    <option value="expense"   {{ request('type')=='expense'   ? 'selected' : '' }}>مصروفات</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-dark transition">
                    <i class="fa fa-search ml-1"></i> بحث
                </button>
                <a href="{{ route('accounts.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                    مسح
                </a>
            </div>
        </form>
    </div>

    {{-- عرض الجدول --}}
    <div x-show="viewMode === 'table'" class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="px-4 py-3 text-right">الكود</th>
                    <th class="px-4 py-3 text-right">اسم الحساب</th>
                    <th class="px-4 py-3 text-center">النوع</th>
                    <th class="px-4 py-3 text-center">الطبيعة</th>
                    <th class="px-4 py-3 text-center">الحساب الأب</th>
                    <th class="px-4 py-3 text-center">تفصيلي</th>
                    <th class="px-4 py-3 text-center">الرصيد الافتتاحي</th>
                    <th class="px-4 py-3 text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($accounts as $account)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-mono font-bold text-primary">{{ $account->code }}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $account->name_ar }}</div>
                        @if($account->name_en)
                            <div class="text-xs text-gray-400">{{ $account->name_en }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $colors = ['asset'=>'blue','liability'=>'red','equity'=>'purple','revenue'=>'green','expense'=>'orange'];
                            $c = $colors[$account->type] ?? 'gray';
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs bg-{{ $c }}-100 text-{{ $c }}-700 font-medium">
                            {{ $account->type_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs {{ $account->normal_balance === 'debit' ? 'text-blue-600' : 'text-green-600' }}">
                            {{ $account->normal_balance === 'debit' ? 'مدين' : 'دائن' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-500 text-xs">
                        {{ $account->parent?->code ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($account->is_leaf)
                            <span class="text-green-600"><i class="fa fa-check-circle"></i></span>
                        @else
                            <span class="text-gray-300"><i class="fa fa-minus-circle"></i></span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center font-mono text-sm">
                        {{ number_format($account->opening_balance, 2) }}
                        <span class="text-xs text-gray-400">{{ $account->opening_balance_type === 'debit' ? 'م' : 'د' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1 justify-center">
                            @if($account->is_leaf)
                            <a href="{{ route('accounts.ledger', $account) }}"
                               class="text-blue-600 hover:text-blue-800 p-1.5 rounded hover:bg-blue-50 transition" title="دفتر الأستاذ">
                                <i class="fa fa-book-open text-xs"></i>
                            </a>
                            @endif
                            @canPermission('accounts.edit')
                            <a href="{{ route('accounts.edit', $account) }}"
                               class="text-amber-600 hover:text-amber-800 p-1.5 rounded hover:bg-amber-50 transition" title="تعديل">
                                <i class="fa fa-pen text-xs"></i>
                            </a>
                            @endcanPermission
                            @canPermission('accounts.delete')
                            <form method="POST" action="{{ route('accounts.destroy', $account) }}"
                                  onsubmit="return confirm('حذف الحساب {{ $account->code }}؟')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="text-red-500 hover:text-red-700 p-1.5 rounded hover:bg-red-50 transition" title="حذف">
                                    <i class="fa fa-trash text-xs"></i>
                                </button>
                            </form>
                            @endcanPermission
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                        <i class="fa fa-folder-open text-4xl mb-3 block"></i>
                        لا توجد حسابات. <a href="{{ route('accounts.create') }}" class="text-primary hover:underline">أضف أول حساب</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $accounts->links() }}
        </div>
    </div>

    {{-- عرض الشجرة --}}
    <div x-show="viewMode === 'tree'" x-cloak class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fa fa-sitemap text-primary"></i> الهيكل الشجري لدليل الحسابات
        </h3>
        <div class="space-y-1 text-sm">
            @foreach($tree as $root)
                @include('accounts._tree_node', ['account' => $root, 'depth' => 0])
            @endforeach
        </div>
    </div>

</div>
@endsection
