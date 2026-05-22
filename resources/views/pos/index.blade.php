{{-- المسار الكامل: resources/views/pos/index.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>كاشير | توتال الكلاكلة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/pos.css'])
</head>
<body>

<div
    class="pos-wrapper"
    x-data="posApp()"
    x-init="init()"
    data-session-id="{{ $session->id }}"
    data-session-balance="{{ $session->expected_balance }}"
    data-tax-percent="{{ \App\Models\Setting::get('tax_percent', 0) }}"
>

    {{-- ═══════════════════════════ HEADER ═══════════════════════════ --}}
    <header class="pos-header">
        <a href="{{ route('dashboard') }}" class="logo" title="الرئيسية">
            🔧 <span>توتال الكلاكلة</span>
        </a>

        <div class="session-info">
            <span>جلسة #{{ $session->id }}</span>
            <span class="session-badge">مفتوحة</span>
            <span>{{ $session->opened_at->format('H:i') }}</span>
        </div>

        <div class="session-info" style="gap:4px">
            <span>الصندوق:</span>
            <span style="color:#4FB3C0;font-weight:700" x-text="fmt(sessionBalance) + ' ج.س'"></span>
        </div>

        <div class="session-info">
            <span>{{ auth()->user()->name }}</span>
        </div>

        <div class="header-actions">
            {{-- صندوق --}}
            <button class="btn-pos-secondary" style="padding:6px 12px;font-size:11px" @click="openCashModal('in')" title="إضافة نقدي">
                💵 إضافة
            </button>
            <button class="btn-pos-secondary" style="padding:6px 12px;font-size:11px" @click="openCashModal('out')" title="سحب نقدي">
                💸 سحب
            </button>

            {{-- تقرير الجلسات --}}
            <a href="{{ route('pos.sessions.index') }}" class="btn-pos-secondary" style="padding:6px 12px;font-size:11px;text-decoration:none;display:inline-block">
                📋 الجلسات
            </a>

            {{-- إغلاق الجلسة --}}
            <button class="btn-pos-secondary" style="padding:6px 12px;font-size:11px;border-color:#E53935;color:#EF9A9A" @click="openCloseSession()">
                🔴 إغلاق الجلسة
            </button>
        </div>
    </header>

    {{-- ═══════════════════════════ PRODUCTS PANEL ═══════════════════ --}}
    <div class="pos-products">

        {{-- بحث --}}
        <div class="pos-search-bar">
            <input
                type="search"
                class="pos-search-input"
                placeholder="🔍  ابحث بالاسم أو الكود أو الباركود..."
                x-model="searchQuery"
                @input="onSearchInput()"
                autocomplete="off"
            >
        </div>

        {{-- فئات --}}
        <div class="pos-categories">
            <button class="pos-cat-btn" :class="selectedCat==='' ? 'active' : ''" @click="selectCategory('')">
                الكل
            </button>
            @foreach($categories as $cat)
            <button
                class="pos-cat-btn"
                :class="selectedCat==='{{ $cat->id }}' ? 'active' : ''"
                @click="selectCategory('{{ $cat->id }}')"
            >{{ $cat->name_ar }}</button>
            @endforeach
        </div>

        {{-- شبكة المنتجات --}}
        <div class="pos-grid">

            {{-- تحميل --}}
            <template x-if="productsLoading">
                <div style="grid-column:1/-1;display:flex;align-items:center;justify-content:center;height:200px">
                    <div class="pos-spinner"></div>
                </div>
            </template>

            {{-- لا يوجد نتائج --}}
            <template x-if="!productsLoading && products.length === 0">
                <div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:#7EADB8">
                    <div style="font-size:48px;margin-bottom:12px">📦</div>
                    <p>لا توجد منتجات مطابقة</p>
                </div>
            </template>

            {{-- المنتجات --}}
            <template x-for="p in products" :key="p.id">
                <div
                    class="product-card"
                    :class="p.quantity <= 0 ? 'out-of-stock' : ''"
                    @click="addToCart(p)"
                    :title="p.name"
                >
                    {{-- شارة النفاد --}}
                    <template x-if="p.quantity <= 0">
                        <span class="stock-badge">نفد</span>
                    </template>
                    <template x-if="p.quantity > 0 && p.stock_status === 'low'">
                        <span class="stock-badge low">كمية قليلة</span>
                    </template>

                    {{-- صورة --}}
                    <template x-if="p.image_url">
                        <img :src="p.image_url" :alt="p.name" loading="lazy">
                    </template>
                    <template x-if="!p.image_url">
                        <div class="product-card-img-placeholder">🔧</div>
                    </template>

                    <div class="product-card-body">
                        <div class="product-card-name" x-text="p.name"></div>
                        <div class="product-card-price" x-text="fmt(p.sale_price) + ' ج.س'"></div>
                        <div class="product-card-stock" x-text="'متاح: ' + p.quantity"></div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ═══════════════════════════ CART PANEL ════════════════════════ --}}
    <aside class="pos-cart">

        {{-- رأس السلة --}}
        <div class="pos-cart-header">
            <h2>🛒 السلة</h2>
            <div style="display:flex;align-items:center;gap:8px">
                <span class="cart-count" x-text="cartCount"></span>
                <button
                    class="btn-pos-secondary"
                    style="padding:4px 10px;font-size:11px"
                    @click="clearCart()"
                    x-show="cart.length > 0"
                    title="مسح السلة"
                >🗑️</button>
            </div>
        </div>

        {{-- العميل --}}
        <div class="pos-customer-row">
            <span>👤</span>
            <template x-if="!customer">
                <button class="customer-name" @click="openCustomerModal()" style="font-size:12px;background:none;border:none;text-align:right">
                    + تحديد عميل (اختياري)
                </button>
            </template>
            <template x-if="customer">
                <span class="customer-name" x-text="customer.name"></span>
            </template>
            <button class="btn-remove-customer" @click="removeCustomer()" x-show="customer" title="إزالة العميل">✕</button>
        </div>

        {{-- بنود السلة --}}
        <div class="pos-cart-items">
            <template x-if="cart.length === 0">
                <div class="cart-empty">
                    <div class="cart-empty-icon">🛍️</div>
                    <p>السلة فارغة</p>
                    <p style="font-size:11px">اضغط على منتج أو امسح الباركود</p>
                </div>
            </template>

            <template x-for="(item, idx) in cart" :key="item.product_id">
                <div class="cart-item">
                    <div style="flex:1;min-width:0">
                        <div class="cart-item-name" x-text="item.name"></div>
                        <div class="cart-item-sub">
                            <span x-text="fmt(item.price) + ' × ' + item.quantity"></span>
                            <template x-if="item.discount_percent > 0">
                                <span style="color:#FB8C00;margin-right:4px" x-text="' خصم ' + item.discount_percent + '%'"></span>
                            </template>
                        </div>
                        {{-- خصم على المنتج --}}
                        <div style="display:flex;align-items:center;gap:4px;margin-top:4px">
                            <label style="font-size:10px;color:#7EADB8">خصم%</label>
                            <input
                                type="number" min="0" max="100" step="1"
                                class="qty-input"
                                style="width:44px"
                                :value="item.discount_percent"
                                @change="updateItemDiscount(idx, $event.target.value)"
                            >
                        </div>
                    </div>

                    {{-- التحكم بالكمية --}}
                    <div class="qty-control">
                        <button class="qty-btn" @click="changeQty(idx, -1)">−</button>
                        <input
                            type="number" min="0.001" step="1"
                            class="qty-input"
                            :value="item.quantity"
                            @change="updateQty(idx, $event.target.value)"
                        >
                        <button class="qty-btn" @click="changeQty(idx, 1)">+</button>
                    </div>

                    <div class="cart-item-total" x-text="fmt(item.total)"></div>

                    <button class="btn-remove-item" @click="removeFromCart(idx)" title="حذف">✕</button>
                </div>
            </template>
        </div>

        {{-- الملخص --}}
        <div class="pos-cart-summary">
            <div class="summary-row">
                <span>الإجمالي الفرعي</span>
                <span x-text="fmt(subtotal) + ' ج.س'"></span>
            </div>

            {{-- خصم الفاتورة --}}
            <div class="discount-row">
                <label>خصم الفاتورة %</label>
                <input type="number" min="0" max="100" step="0.5" class="discount-input" x-model="discountPercent" placeholder="0">
                <span style="font-size:11px;color:#E53935" x-text="totalDiscount > 0 ? '−' + fmt(totalDiscount) + ' ج.س' : ''"></span>
            </div>

            <template x-if="taxPercent > 0">
                <div class="summary-row tax">
                    <span>ضريبة (<span x-text="taxPercent"></span>%)</span>
                    <span x-text="fmt(taxAmount) + ' ج.س'"></span>
                </div>
            </template>

            <div class="summary-row total">
                <span>الإجمالي</span>
                <span x-text="fmt(grandTotal) + ' ج.س'"></span>
            </div>

            <button
                class="btn-checkout"
                :disabled="cart.length === 0"
                @click="openPaymentModal()"
            >
                💳 الدفع
            </button>

            <div class="pos-action-btns">
                <button class="btn-pos-secondary" @click="openCustomerModal()">👤 عميل</button>
                <a href="{{ route('pos.report') }}" class="btn-pos-secondary" style="text-decoration:none;display:flex;align-items:center;justify-content:center">📊 تقرير</a>
            </div>
        </div>
    </aside>

    {{-- ═══════════════════ MODAL: الدفع ═══════════════════════════ --}}
    <div class="pos-modal-overlay" x-show="showPaymentModal" x-cloak @click.self="showPaymentModal=false">
        <div class="pos-modal">
            <h3>💳 إتمام الدفع</h3>

            {{-- ملخص --}}
            <div style="background:#0F1E24;border-radius:10px;padding:12px;margin-bottom:16px">
                <div class="summary-row" style="font-size:13px">
                    <span style="color:#7EADB8">الإجمالي</span>
                    <span style="color:#4FB3C0;font-size:18px;font-weight:800" x-text="fmt(grandTotal) + ' ج.س'"></span>
                </div>
                <template x-if="customer">
                    <div class="summary-row" style="font-size:12px;margin-top:4px">
                        <span style="color:#7EADB8">العميل</span>
                        <span style="color:#E8F4F6" x-text="customer.name"></span>
                    </div>
                </template>
            </div>

            {{-- نوع الدفع --}}
            <div class="payment-types">
                <button class="pay-type-btn" :class="paymentType==='cash'?'active':''" @click="paymentType='cash'">
                    <span class="icon">💵</span> نقدي
                </button>
                <button class="pay-type-btn" :class="paymentType==='credit'?'active':''" @click="paymentType='credit'">
                    <span class="icon">📋</span> آجل
                </button>
                <button class="pay-type-btn" :class="paymentType==='split'?'active':''" @click="paymentType='split'">
                    <span class="icon">🔀</span> مختلط
                </button>
            </div>

            {{-- حقول نقدي --}}
            <template x-if="paymentType==='cash'">
                <div>
                    <label class="pos-label">المبلغ المستلم (ج.س)</label>
                    <input type="number" min="0" step="0.01" class="pos-input" x-model="cashReceived" placeholder="0.00" autofocus>
                    <template x-if="(parseFloat(cashReceived)||0) >= grandTotal">
                        <div class="change-display">
                            <div class="label">المبلغ المرتجع</div>
                            <div class="amount" x-text="fmt(changeAmount) + ' ج.س'"></div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- حقول آجل --}}
            <template x-if="paymentType==='credit'">
                <div>
                    <template x-if="!customer">
                        <div style="background:rgba(251,140,0,.15);border:1px solid rgba(251,140,0,.4);border-radius:8px;padding:10px 14px;font-size:12px;color:#FFCC80;margin-top:8px">
                            ⚠️ يجب تحديد عميل للبيع الآجل
                            <button class="btn-pos-secondary" style="margin-top:8px;width:100%;font-size:12px" @click="showPaymentModal=false;openCustomerModal()">
                                + تحديد عميل
                            </button>
                        </div>
                    </template>
                    <template x-if="customer">
                        <div style="background:rgba(0,131,143,.1);border-radius:8px;padding:10px;font-size:12px;color:#4FB3C0;margin-top:8px">
                            ✓ الفاتورة ستُضاف لحساب: <strong x-text="customer.name"></strong>
                        </div>
                    </template>
                </div>
            </template>

            {{-- حقول مختلط --}}
            <template x-if="paymentType==='split'">
                <div>
                    <label class="pos-label">المبلغ النقدي (ج.س)</label>
                    <input type="number" min="0" step="0.01" class="pos-input" x-model="cashPartial" placeholder="0.00">
                    <div style="font-size:12px;color:#7EADB8;margin-top:6px;display:flex;justify-content:space-between">
                        <span>الجزء الآجل:</span>
                        <span style="color:#4FB3C0" x-text="fmt(Math.max(0, grandTotal - (parseFloat(cashPartial)||0))) + ' ج.س'"></span>
                    </div>
                    <label class="pos-label">المبلغ المستلم نقداً (ج.س)</label>
                    <input type="number" min="0" step="0.01" class="pos-input" x-model="cashReceived" placeholder="0.00">
                    <template x-if="changeAmount > 0">
                        <div class="change-display">
                            <div class="label">الباقي</div>
                            <div class="amount" x-text="fmt(changeAmount) + ' ج.س'"></div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- ملاحظات --}}
            <label class="pos-label">ملاحظات (اختياري)</label>
            <input type="text" class="pos-input" x-model="notes" placeholder="أي ملاحظة على الفاتورة...">

            <div class="pos-modal-footer">
                <button
                    class="btn-pos-primary"
                    @click="completeSale()"
                    :disabled="processing"
                    x-text="processing ? 'جاري المعالجة...' : 'تأكيد البيع ✓'"
                ></button>
                <button class="btn-pos-cancel" @click="showPaymentModal=false">إلغاء</button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ MODAL: العميل ═════════════════════════ --}}
    <div class="pos-modal-overlay" x-show="showCustomerModal" x-cloak @click.self="showCustomerModal=false">
        <div class="pos-modal">
            <h3>👤 تحديد العميل</h3>
            <input
                type="search"
                class="pos-input"
                placeholder="ابحث بالاسم أو الهاتف..."
                x-model="customerSearch"
                @input="searchCustomers()"
                autofocus
            >
            <template x-if="customerLoading">
                <div class="pos-spinner"></div>
            </template>
            <div class="customer-search-results" x-show="customerResults.length > 0">
                <template x-for="c in customerResults" :key="c.id">
                    <div class="customer-result-item" @click="selectCustomer(c)">
                        <div class="cname" x-text="c.name"></div>
                        <div class="cphone">
                            <span x-text="c.phone"></span>
                            <template x-if="c.balance > 0">
                                <span style="color:#E53935;margin-right:8px" x-text="'رصيد مستحق: ' + fmt(c.balance) + ' ج.س'"></span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
            <div class="pos-modal-footer">
                <button class="btn-pos-cancel" style="flex:1" @click="showCustomerModal=false">إغلاق</button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ MODAL: الصندوق ════════════════════════ --}}
    <div class="pos-modal-overlay" x-show="showCashModal" x-cloak @click.self="showCashModal=false">
        <div class="pos-modal">
            <h3 x-text="cashModalType==='in' ? '💵 إضافة نقدي للصندوق' : '💸 سحب نقدي من الصندوق'"></h3>
            <label class="pos-label">المبلغ (ج.س)</label>
            <input type="number" min="0.01" step="0.01" class="pos-input" x-model="cashAmount" placeholder="0.00">
            <label class="pos-label">السبب</label>
            <input type="text" class="pos-input" x-model="cashReason" placeholder="سبب الإضافة أو السحب...">
            <div class="pos-modal-footer">
                <button class="btn-pos-primary" @click="submitCash()">تأكيد</button>
                <button class="btn-pos-cancel" @click="showCashModal=false">إلغاء</button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ MODAL: إغلاق الجلسة ═══════════════════ --}}
    <div class="pos-modal-overlay" x-show="showCloseSessionModal" x-cloak @click.self="showCloseSessionModal=false">
        <div class="pos-modal">
            <h3>🔴 إغلاق الجلسة</h3>
            <div class="session-summary-grid">
                <div class="session-stat">
                    <div class="value" style="font-size:13px">{{ number_format($session->total_sales, 2) }}</div>
                    <div class="label">إجمالي المبيعات</div>
                </div>
                <div class="session-stat">
                    <div class="value" style="font-size:13px">{{ $session->transactions_count }}</div>
                    <div class="label">عدد الفواتير</div>
                </div>
                <div class="session-stat">
                    <div class="value" style="font-size:13px" x-text="fmt(sessionBalance)"></div>
                    <div class="label">رصيد متوقع</div>
                </div>
            </div>
            <form method="POST" action="{{ route('pos.sessions.close', $session) }}">
                @csrf
                <label class="pos-label">رصيد الصندوق الفعلي (ج.س)</label>
                <input type="number" name="closing_balance" min="0" step="0.01" class="pos-input" x-model="closingBalance" placeholder="0.00">
                <label class="pos-label" style="margin-top:10px">ملاحظات (اختياري)</label>
                <input type="text" name="closing_notes" class="pos-input" x-model="closingNotes" placeholder="ملاحظات الإغلاق...">
                <div class="pos-modal-footer">
                    <button type="submit" class="btn-pos-primary" style="background:#E53935">إغلاق الجلسة</button>
                    <button type="button" class="btn-pos-cancel" @click="showCloseSessionModal=false">إلغاء</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Toast --}}
    <div id="pos-toast" class="pos-toast"></div>

</div>{{-- end pos-wrapper --}}

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@vite(['resources/js/pos.js'])

</body>
</html>
