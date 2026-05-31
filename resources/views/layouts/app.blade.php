<!DOCTYPE html>
<html lang="{{ $lang ?? 'ar' }}" dir="{{ $dir ?? 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('common.dashboard')) — توتال الكلاكلة</title>

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary:  { DEFAULT: '#146E6E', dark: '#0D5050', light: '#1A8F8F' },
                        sidebar:  '#146E6E',
                    },
                    fontFamily: {
                        arabic: ['"Noto Sans Arabic"', 'Tajawal', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    {{-- Google Fonts Arabic --}}
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">

    {{-- Alpine.js --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * { font-family: 'Tajawal', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #146E6E; border-radius: 3px; }

        /* Sidebar active */
        .sidebar-link.active {
            background: rgba(255,255,255,0.2);
            border-right: 3px solid #fff;
        }
        html[dir="ltr"] .sidebar-link.active {
            border-right: none;
            border-left: 3px solid #fff;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-100 text-gray-800" x-data="{ sidebarOpen: true }">

    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- Sidebar                                                  --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <aside
        :class="sidebarOpen ? 'w-64' : 'w-16'"
        class="fixed top-0 h-full bg-primary text-white transition-all duration-300 z-50 flex flex-col overflow-hidden shadow-xl {{ ($lang ?? 'ar') === 'ar' ? 'right-0' : 'left-0' }}"
        x-cloak
    >
        {{-- Logo --}}
        <div class="border-b border-white/20 flex-shrink-0">
            {{-- Expanded: full logo --}}
            <div x-show="sidebarOpen" x-transition class="px-3 py-3">
                <img src="{{ asset('logo.svg') }}" alt="شعار النظام"
                     class="w-full h-auto max-h-20 object-contain">
            </div>
            {{-- Collapsed: small icon --}}
            <div x-show="!sidebarOpen" class="flex items-center justify-center py-4">
                <img src="{{ asset('logo.svg') }}" alt="شعار النظام"
                     class="w-10 h-10 object-contain">
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-2">

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/20 transition {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa fa-gauge-high w-5 text-center"></i>
                <span x-show="sidebarOpen" x-transition class="text-sm font-medium">{{ __('common.dashboard') }}</span>
            </a>

            {{-- Inventory --}}
            @canPermission('products.view')
            <div x-data="{ open: {{ request()->routeIs('products.*','categories.*','warehouses.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/20 transition">
                    <i class="fa fa-boxes-stacked w-5 text-center"></i>
                    <span x-show="sidebarOpen" x-transition class="text-sm font-medium flex-1 {{ ($lang ?? 'ar') === 'ar' ? 'text-right' : 'text-left' }}">{{ __('common.inventory') }}</span>
                    <i x-show="sidebarOpen" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa text-xs"></i>
                </button>
                <div x-show="open" x-collapse class="{{ ($lang ?? 'ar') === 'ar' ? 'mr-6' : 'ml-6' }} space-y-1 mt-1">
                    <a href="{{ route('products.index') }}" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/20 text-sm {{ request()->routeIs('products.*') ? 'active' : '' }}">
                        <i class="fa fa-box w-4 text-center text-xs"></i>
                        <span x-show="sidebarOpen">{{ __('common.products') }}</span>
                    </a>
                    <a href="{{ route('categories.index') }}" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/20 text-sm {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <i class="fa fa-tags w-4 text-center text-xs"></i>
                        <span x-show="sidebarOpen">{{ __('common.categories') }}</span>
                    </a>
                    <a href="{{ route('warehouses.index') }}" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/20 text-sm {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                        <i class="fa fa-warehouse w-4 text-center text-xs"></i>
                        <span x-show="sidebarOpen">{{ __('common.warehouses') }}</span>
                    </a>
                </div>
            </div>
            @endcanPermission

            {{-- Sales --}}
            @canPermission('invoices.view')
            <div x-data="{ open: {{ request()->routeIs('invoices.*','customers.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/20 transition">
                    <i class="fa fa-file-invoice-dollar w-5 text-center"></i>
                    <span x-show="sidebarOpen" x-transition class="text-sm font-medium flex-1 {{ ($lang ?? 'ar') === 'ar' ? 'text-right' : 'text-left' }}">{{ __('common.sales') }}</span>
                    <i x-show="sidebarOpen" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa text-xs"></i>
                </button>
                <div x-show="open" x-collapse class="{{ ($lang ?? 'ar') === 'ar' ? 'mr-6' : 'ml-6' }} space-y-1 mt-1">
                    <a href="{{ route('invoices.index') }}" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/20 text-sm">
                        <i class="fa fa-receipt w-4 text-center text-xs"></i>
                        <span x-show="sidebarOpen">{{ __('common.invoices') }}</span>
                    </a>
                    <a href="{{ route('customers.index') }}" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/20 text-sm">
                        <i class="fa fa-users w-4 text-center text-xs"></i>
                        <span x-show="sidebarOpen">{{ __('common.customers') }}</span>
                    </a>
                </div>
            </div>
            @endcanPermission

            {{-- POS --}}
            @canPermission('pos.access')
            <a href="{{ route('pos.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/20 transition {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                <i class="fa fa-cash-register w-5 text-center"></i>
                <span x-show="sidebarOpen" x-transition class="text-sm font-medium">{{ __('common.pos') }}</span>
            </a>
            @endcanPermission

            {{-- Purchases --}}
            @canPermission('purchases.view')
            <div x-data="{ open: {{ request()->routeIs('suppliers.*','purchase*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/20 transition">
                    <i class="fa fa-truck w-5 text-center"></i>
                    <span x-show="sidebarOpen" x-transition class="text-sm font-medium flex-1 {{ ($lang ?? 'ar') === 'ar' ? 'text-right' : 'text-left' }}">{{ __('common.purchases') }}</span>
                    <i x-show="sidebarOpen" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa text-xs"></i>
                </button>
                <div x-show="open" x-collapse class="{{ ($lang ?? 'ar') === 'ar' ? 'mr-6' : 'ml-6' }} space-y-1 mt-1">
                    <a href="{{ route('purchase-orders.index') }}" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/20 text-sm">
                        <i class="fa fa-clipboard-list w-4 text-center text-xs"></i>
                        <span x-show="sidebarOpen">{{ __('common.purchase_orders') }}</span>
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/20 text-sm">
                        <i class="fa fa-industry w-4 text-center text-xs"></i>
                        <span x-show="sidebarOpen">{{ __('common.suppliers') }}</span>
                    </a>
                </div>
            </div>
            @endcanPermission

            {{-- Accounting --}}
            @canPermission('accounts.view')
            <div x-data="{ open: {{ request()->routeIs('accounts.*','journal.*','vouchers.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/20 transition">
                    <i class="fa fa-calculator w-5 text-center"></i>
                    <span x-show="sidebarOpen" x-transition class="text-sm font-medium flex-1 {{ ($lang ?? 'ar') === 'ar' ? 'text-right' : 'text-left' }}">{{ __('common.accounting') }}</span>
                    <i x-show="sidebarOpen" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa text-xs"></i>
                </button>
                <div x-show="open" x-collapse class="{{ ($lang ?? 'ar') === 'ar' ? 'mr-6' : 'ml-6' }} space-y-1 mt-1">
                    <a href="{{ route('accounts.index') }}" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/20 text-sm">
                        <i class="fa fa-book w-4 text-center text-xs"></i>
                        <span x-show="sidebarOpen">{{ __('common.chart_of_accounts') }}</span>
                    </a>
                    <a href="{{ route('journal.index') }}" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/20 text-sm">
                        <i class="fa fa-journal-whills w-4 text-center text-xs"></i>
                        <span x-show="sidebarOpen">{{ __('common.journal_entries') }}</span>
                    </a>
                    <a href="{{ route('vouchers.index') }}" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/20 text-sm">
                        <i class="fa fa-file-invoice w-4 text-center text-xs"></i>
                        <span x-show="sidebarOpen">{{ __('common.vouchers') }}</span>
                    </a>
                </div>
            </div>
            @endcanPermission

            {{-- HR --}}
            @canPermission('hr.view')
            <div x-data="{ open: {{ request()->routeIs('employees.*','payroll.*','leaves.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/20 transition">
                    <i class="fa fa-id-badge w-5 text-center"></i>
                    <span x-show="sidebarOpen" x-transition class="text-sm font-medium flex-1 {{ ($lang ?? 'ar') === 'ar' ? 'text-right' : 'text-left' }}">{{ __('common.hr') }}</span>
                    <i x-show="sidebarOpen" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa text-xs"></i>
                </button>
                <div x-show="open" x-collapse class="{{ ($lang ?? 'ar') === 'ar' ? 'mr-6' : 'ml-6' }} space-y-1 mt-1">
                    <a href="{{ route('employees.index') }}" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/20 text-sm">
                        <i class="fa fa-user-tie w-4 text-center text-xs"></i>
                        <span x-show="sidebarOpen">{{ __('common.employees') }}</span>
                    </a>
                    <a href="{{ route('payroll.index') }}" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/20 text-sm">
                        <i class="fa fa-money-check-dollar w-4 text-center text-xs"></i>
                        <span x-show="sidebarOpen">{{ __('common.payroll') }}</span>
                    </a>
                    <a href="{{ route('leaves.index') }}" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/20 text-sm">
                        <i class="fa fa-calendar-days w-4 text-center text-xs"></i>
                        <span x-show="sidebarOpen">{{ __('common.leaves') }}</span>
                    </a>
                </div>
            </div>
            @endcanPermission

            {{-- Reports --}}
            @canPermission('reports.view')
            <a href="{{ route('reports.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/20 transition {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="fa fa-chart-pie w-5 text-center"></i>
                <span x-show="sidebarOpen" x-transition class="text-sm font-medium">{{ __('common.reports') }}</span>
            </a>
            @endcanPermission

            {{-- Users --}}
            @canPermission('users.view')
            <a href="{{ route('users.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/20 transition {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fa fa-users-gear w-5 text-center"></i>
                <span x-show="sidebarOpen" x-transition class="text-sm font-medium">{{ __('common.users') }}</span>
            </a>
            @endcanPermission

            {{-- Settings --}}
            @canPermission('settings.view')
            <a href="{{ route('settings.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/20 transition {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="fa fa-gear w-5 text-center"></i>
                <span x-show="sidebarOpen" x-transition class="text-sm font-medium">{{ __('common.settings') }}</span>
            </a>
            @endcanPermission

        </nav>

        {{-- Toggle sidebar button --}}
        <button @click="sidebarOpen = !sidebarOpen"
            class="p-3 border-t border-white/20 hover:bg-white/20 transition text-center">
            @if(($lang ?? 'ar') === 'ar')
                <i :class="sidebarOpen ? 'fa-angles-right' : 'fa-angles-left'" class="fa text-sm"></i>
            @else
                <i :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'" class="fa text-sm"></i>
            @endif
        </button>
    </aside>

    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- Main content                                             --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div
        :class="sidebarOpen ? '{{ ($lang ?? 'ar') === 'ar' ? 'mr-64' : 'ml-64' }}' : '{{ ($lang ?? 'ar') === 'ar' ? 'mr-16' : 'ml-16' }}'"
        class="transition-all duration-300 min-h-screen flex flex-col"
        x-cloak
    >

        {{-- Top bar --}}
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="flex items-center justify-between px-6 py-3">

                {{-- Page title --}}
                <h1 class="text-lg font-bold text-gray-700">@yield('page-title', __('common.dashboard'))</h1>

                {{-- Right: notifications + user --}}
                <div class="flex items-center gap-4">

                    {{-- Language switch --}}
                    <a href="{{ route('lang.switch', ($lang ?? 'ar') === 'ar' ? 'en' : 'ar') }}"
                       class="text-sm text-gray-500 hover:text-primary transition font-medium">
                        {{ ($lang ?? 'ar') === 'ar' ? 'EN' : 'ع' }}
                    </a>

                    {{-- Notifications --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative p-2 text-gray-500 hover:text-primary transition">
                            <i class="fa fa-bell text-lg"></i>
                            @php $unreadCount = 0; @endphp
                            @if($unreadCount > 0)
                            <span class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                                {{ $unreadCount }}
                            </span>
                            @endif
                        </button>
                        <div x-show="open" @click.outside="open = false"
                            class="{{ ($lang ?? 'ar') === 'ar' ? 'left-0' : 'right-0' }} absolute mt-2 w-72 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden">
                            <div class="px-4 py-3 border-b bg-gray-50">
                                <p class="font-bold text-sm text-gray-700">{{ __('common.notifications') }}</p>
                            </div>
                            <div class="py-6 text-center text-gray-400 text-sm">
                                <i class="fa fa-bell-slash text-2xl mb-2 block"></i>
                                {{ __('common.no_new_notifications') }}
                            </div>
                        </div>
                    </div>

                    {{-- User --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 hover:opacity-80 transition">
                            <img src="{{ auth()->user()->avatar_url }}"
                                 class="w-8 h-8 rounded-full object-cover border-2 border-primary"
                                 alt="{{ auth()->user()->name }}">
                            <div class="hidden sm:block {{ ($lang ?? 'ar') === 'ar' ? 'text-right' : 'text-left' }}">
                                <p class="text-sm font-medium text-gray-700 leading-tight">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-400">{{ auth()->user()->role_name }}</p>
                            </div>
                        </button>
                        <div x-show="open" @click.outside="open = false"
                            class="{{ ($lang ?? 'ar') === 'ar' ? 'left-0' : 'right-0' }} absolute mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden">
                            <a href="{{ route('profile') }}" class="flex items-center gap-2 px-4 py-3 hover:bg-gray-50 text-sm text-gray-700">
                                <i class="fa fa-user-circle w-4"></i> {{ __('common.profile') }}
                            </a>
                            <hr>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="w-full flex items-center gap-2 px-4 py-3 hover:bg-red-50 text-sm text-red-600">
                                    <i class="fa fa-right-from-bracket w-4"></i> {{ __('common.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        {{-- Breadcrumb --}}
        @hasSection('breadcrumb')
        <div class="bg-white border-b px-6 py-2">
            <nav class="text-sm text-gray-500 flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">{{ __('common.home') }}</a>
                @yield('breadcrumb')
            </nav>
        </div>
        @endif

        {{-- Flash Messages --}}
        <div class="px-6 pt-4 space-y-2">
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3">
                <i class="fa fa-circle-check text-green-500"></i>
                <span class="text-sm flex-1">{{ session('success') }}</span>
                <button @click="show = false" class="text-green-500 hover:text-green-700"><i class="fa fa-xmark"></i></button>
            </div>
            @endif

            @if(session('error'))
            <div x-data="{ show: true }" x-show="show"
                 class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3">
                <i class="fa fa-circle-xmark text-red-500"></i>
                <span class="text-sm flex-1">{{ session('error') }}</span>
                <button @click="show = false" class="text-red-500 hover:text-red-700"><i class="fa fa-xmark"></i></button>
            </div>
            @endif

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fa fa-triangle-exclamation text-red-500"></i>
                    <span class="text-sm font-medium">{{ __('common.data_errors') }}</span>
                </div>
                <ul class="text-sm list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        {{-- Content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="text-center text-xs text-gray-400 py-4 border-t">
            توتال الكلاكلة &copy; {{ date('Y') }} — {{ __('common.erp_system') }}
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
