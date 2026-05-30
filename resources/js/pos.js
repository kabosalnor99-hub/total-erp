/**
 * المسار الكامل: resources/js/pos.js
 * منطق شاشة نقطة البيع — Alpine.js
 * توتال الكلاكلة
 */

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {

    Alpine.data('posApp', () => ({

        // ─── الحالة ──────────────────────────────────────────────
        sessionId:       null,
        sessionBalance:  0,

        // المنتجات
        products:        [],
        productsLoading: false,
        productsLoadingMore: false,
        hasMoreProducts: false,
        productsNextPage: 1,
        searchQuery:     '',
        selectedCat:     '',
        searchTimeout:   null,
        _scrollObserver: null,

        // السلة
        cart:            [],
        customer:        null,
        discountPercent: 0,
        taxPercent:      0,
        notes:           '',

        // النوافذ
        showPaymentModal:       false,
        showCustomerModal:      false,
        showAddCustomerModal:   false,
        showCloseSessionModal:  false,
        showCashModal:          false,
        cashModalType:          'in',   // 'in' | 'out'

        // نافذة الدفع
        paymentType:    'cash',
        cashReceived:   '',
        cashPartial:    '',
        customerSearch: '',
        customerResults:[],
        customerLoading:false,
        newCustomerName:    '',
        newCustomerPhone:   '',
        newCustomerAddress: '',
        cashAmount:     0,
        cashReason:     '',

        // إغلاق الجلسة
        closingBalance: '',
        closingNotes:   '',

        // معاملة أُنجزت
        lastTransaction: null,
        processing:      false,

        // ─── Init ─────────────────────────────────────────────────
        init() {
            this.sessionId      = this.$el.dataset.sessionId;
            this.sessionBalance = parseFloat(this.$el.dataset.sessionBalance || 0);
            this.taxPercent     = parseFloat(this.$el.dataset.taxPercent || 0);
            this.loadProducts();
            this.initBarcodeListener();
        },

        // ─── تحميل المنتجات (الصفحة الأولى) ────────────────────────
        async loadProducts() {
            this.productsLoading  = true;
            this.products         = [];
            this.productsNextPage = 1;
            this.hasMoreProducts  = false;

            // فك ربط الـ observer القديم
            if (this._scrollObserver) {
                this._scrollObserver.disconnect();
                this._scrollObserver = null;
            }

            try {
                const params = new URLSearchParams({ page: 1 });
                if (this.searchQuery) params.set('q', this.searchQuery);
                if (this.selectedCat && this.selectedCat !== '') params.set('category_id', this.selectedCat);

                const res  = await fetch(`/pos/products/search?${params}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();

                this.products         = data.products ?? [];
                this.hasMoreProducts  = data.hasMore  ?? false;
                this.productsNextPage = data.nextPage ?? null;

                // بعد ما يرسم Alpine المنتجات، ابدأ مراقبة الـ sentinel
                await this.$nextTick();
                this._initScrollObserver();

            } catch (e) {
                this.toast('خطأ في تحميل المنتجات', 'error');
            } finally {
                this.productsLoading = false;
            }
        },

        // ─── تحميل صفحة إضافية (Infinite Scroll) ───────────────────
        async _loadMoreProducts() {
            if (this.productsLoadingMore || !this.hasMoreProducts || !this.productsNextPage) return;

            this.productsLoadingMore = true;
            try {
                const params = new URLSearchParams({ page: this.productsNextPage });
                if (this.searchQuery) params.set('q', this.searchQuery);
                if (this.selectedCat && this.selectedCat !== '') params.set('category_id', this.selectedCat);

                const res  = await fetch(`/pos/products/search?${params}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();

                this.products         = [...this.products, ...(data.products ?? [])];
                this.hasMoreProducts  = data.hasMore  ?? false;
                this.productsNextPage = data.nextPage ?? null;

                if (!this.hasMoreProducts && this._scrollObserver) {
                    this._scrollObserver.disconnect();
                    this._scrollObserver = null;
                }
            } catch (e) {
                // فشل صامت — المستخدم يمكنه التمرير مجدداً
            } finally {
                this.productsLoadingMore = false;
            }
        },

        // ─── إنشاء IntersectionObserver على sentinel ────────────────
        _initScrollObserver() {
            if (!this.hasMoreProducts) return;

            const sentinel = document.getElementById('pos-products-sentinel');
            if (!sentinel) return;

            const grid = document.querySelector('.pos-grid');

            this._scrollObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) this._loadMoreProducts();
                });
            }, {
                root:       grid,        // الـ scroll container هو pos-grid نفسه
                rootMargin: '0px 0px 150px 0px',
                threshold:  0,
            });

            this._scrollObserver.observe(sentinel);
        },

        onSearchInput() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => this.loadProducts(), 350);
        },

        selectCategory(id) {
            this.selectedCat = id;
            this.loadProducts();
        },

        // ─── الباركود ─────────────────────────────────────────────
        initBarcodeListener() {
            let buffer = '';
            let timer  = null;

            document.addEventListener('keydown', (e) => {
                // تجاهل إذا كان المستخدم يكتب في حقل إدخال
                if (['INPUT','TEXTAREA','SELECT'].includes(e.target.tagName)) return;

                if (e.key === 'Enter') {
                    if (buffer.length >= 3) this.scanBarcode(buffer);
                    buffer = '';
                    clearTimeout(timer);
                    return;
                }

                if (e.key.length === 1) {
                    buffer += e.key;
                    clearTimeout(timer);
                    timer = setTimeout(() => { buffer = ''; }, 150);
                }
            });
        },

        async scanBarcode(barcode) {
            try {
                const res  = await fetch(`/pos/products/barcode?barcode=${encodeURIComponent(barcode)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();

                if (data.found) {
                    this.addToCart(data.product, true); // إضافة كعنصر جديد دائماً
                    this.toast(`تمت إضافة: ${data.product.name}`);
                } else {
                    this.toast(data.message || 'المنتج غير موجود', 'error');
                }
            } catch (e) {
                this.toast('خطأ في البحث بالباركود', 'error');
            }
        },

        // ─── السلة ────────────────────────────────────────────────
        addToCart(product, addAsNew = false) {
            if (product.quantity <= 0) {
                this.toast(`«${product.name}» نفد من المخزون`, 'warning');
                return;
            }

            const existing = this.cart.find(i => i.product_id === product.id);

            if (existing && !addAsNew) {
                if (existing.quantity >= product.quantity) {
                    this.toast(`الكمية المطلوبة تتجاوز المخزون المتاح (${product.quantity})`, 'warning');
                    return;
                }
                existing.quantity += 1;
                existing.total = this.calcItemTotal(existing);
            } else {
                this.cart.push({
                    product_id:       product.id,
                    name:             product.name,
                    sku:              product.sku,
                    price:            product.sale_price,
                    sale_price:       product.sale_price,
                    quantity:         1,
                    max_qty:          product.quantity,
                    discount_percent: 0,
                    total:            product.sale_price,
                });
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        updateQty(index, val) {
            const item = this.cart[index];
            const qty  = parseFloat(val) || 1;

            if (qty <= 0) { this.removeFromCart(index); return; }
            if (qty > item.max_qty) {
                this.toast(`الحد الأقصى المتاح: ${item.max_qty}`, 'warning');
                item.quantity = item.max_qty;
            } else {
                item.quantity = qty;
            }
            item.total = this.calcItemTotal(item);
        },

        changeQty(index, delta) {
            const item = this.cart[index];
            const newQ = item.quantity + delta;
            this.updateQty(index, newQ);
        },

        updateItemDiscount(index, val) {
            const item        = this.cart[index];
            item.discount_percent = Math.min(100, Math.max(0, parseFloat(val) || 0));
            item.total = this.calcItemTotal(item);
        },

        calcItemTotal(item) {
            const base = item.price * item.quantity;
            const disc = base * (item.discount_percent / 100);
            return Math.round((base - disc) * 100) / 100;
        },

        clearCart() {
            this.cart            = [];
            this.customer        = null;
            this.discountPercent = 0;
            this.notes           = '';
            this.lastTransaction = null;
        },

        // ─── حفظ فاتورة مبدئية ───────────────────────────────────────
        async saveDraft() {
            if (this.cart.length === 0) {
                this.toast('السلة فارغة', 'error');
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfToken) {
                    this.toast('خطأ في CSRF token', 'error');
                    return;
                }

                const res = await fetch('/pos/draft', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        items: this.cart.map(item => ({
                            product_id: item.id,
                            quantity: item.quantity,
                            price: item.price,
                            discount_percent: item.discount_percent
                        })),
                        customer_id: this.customer ? this.customer.id : null,
                        discount_percent: this.discountPercent,
                        tax_percent: this.taxPercent,
                        notes: this.notes
                    })
                });

                const data = await res.json();

                if (data.success) {
                    this.toast('تم حفظ الفاتورة المبدئية بنجاح', 'success');
                    this.clearCart();
                    window.location.href = data.invoice_url;
                } else {
                    this.toast(data.message || 'حدث خطأ', 'error');
                }

            } catch (err) {
                console.error('Save draft error:', err);
                this.toast('حدث خطأ في الاتصال', 'error');
            }
        },

        // ─── الحسابات ─────────────────────────────────────────────
        get subtotal() {
            return this.cart.reduce((s, i) => s + i.price * i.quantity, 0);
        },
        get itemsDiscount() {
            return this.cart.reduce((s, i) => s + (i.price * i.quantity * (i.discount_percent / 100)), 0);
        },
        get invoiceDiscount() {
            return (this.subtotal - this.itemsDiscount) * (parseFloat(this.discountPercent) / 100);
        },
        get afterDiscount() {
            return this.subtotal - this.itemsDiscount - this.invoiceDiscount;
        },
        get taxAmount() {
            return this.afterDiscount * (parseFloat(this.taxPercent) / 100);
        },
        get grandTotal() {
            return this.afterDiscount + this.taxAmount;
        },
        get totalDiscount() {
            return this.itemsDiscount + this.invoiceDiscount;
        },
        get cartCount() {
            return this.cart.reduce((s, i) => s + i.quantity, 0);
        },

        get changeAmount() {
            if (this.paymentType === 'cash') {
                return Math.max(0, (parseFloat(this.cashReceived) || 0) - this.grandTotal);
            }
            if (this.paymentType === 'split') {
                return Math.max(0, (parseFloat(this.cashReceived) || 0) - (parseFloat(this.cashPartial) || 0));
            }
            return 0;
        },

        // ─── العملاء ──────────────────────────────────────────────
        openCustomerModal() {
            this.customerSearch  = '';
            this.customerResults = [];
            this.showCustomerModal = true;
        },

        openAddCustomerModal() {
            this.newCustomerName    = '';
            this.newCustomerPhone   = '';
            this.newCustomerAddress = '';
            this.showAddCustomerModal = true;
        },

        async createCustomer() {
            if (!this.newCustomerName.trim()) {
                this.toast('الاسم مطلوب', 'error');
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfToken) {
                    this.toast('خطأ في CSRF token', 'error');
                    return;
                }

                const res = await fetch('/customers', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: this.newCustomerName,
                        phone: this.newCustomerPhone,
                        address: this.newCustomerAddress,
                        type: 'individual',
                        classification: 'regular',
                        is_active: true,
                    })
                });

                const data = await res.json();

                if (res.ok) {
                    this.customer = data.customer;
                    this.showAddCustomerModal = false;
                    this.toast(`تم إضافة العميل: ${this.newCustomerName}`);
                } else {
                    this.toast(data.message || 'خطأ في إضافة العميل', 'error');
                }
            } catch (e) {
                console.error('Error creating customer:', e);
                this.toast('خطأ في الاتصال', 'error');
            }
        },

        async searchCustomers() {
            if (this.customerSearch.length < 2) { this.customerResults = []; return; }
            this.customerLoading = true;
            try {
                const res  = await fetch(`/pos/customers/search?q=${encodeURIComponent(this.customerSearch)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.customerResults = data.customers ?? [];
            } finally {
                this.customerLoading = false;
            }
        },

        selectCustomer(c) {
            this.customer         = c;
            this.showCustomerModal = false;
            this.toast(`تم تحديد العميل: ${c.name}`);
        },

        removeCustomer() {
            this.customer = null;
        },

        // ─── نافذة الدفع ──────────────────────────────────────────
        openPaymentModal() {
            if (this.cart.length === 0) {
                this.toast('أضف منتجات إلى السلة أولاً', 'warning');
                return;
            }
            this.cashReceived       = this.grandTotal;
            this.cashPartial        = '';
            this.paymentType        = 'cash';
            this.showPaymentModal   = true;
        },

        // ─── إتمام البيع ──────────────────────────────────────────
        async completeSale() {
            if (this.processing) return;

            // تحقق نقدي
            if (this.paymentType === 'cash') {
                const received = parseFloat(this.cashReceived) || 0;
                if (received < this.grandTotal) {
                    this.toast('المبلغ المستلم أقل من الإجمالي', 'error');
                    return;
                }
            }

            // تحقق آجل بدون عميل
            if (this.paymentType === 'credit' && !this.customer) {
                this.toast('يجب تحديد عميل للبيع الآجل', 'warning');
                return;
            }

            this.processing = true;

            const payload = {
                session_id:  this.sessionId,
                customer_id: this.customer?.id ?? null,
                items: this.cart.map(i => ({
                    product_id:       i.product_id,
                    quantity:         i.quantity,
                    price:            i.price,
                    discount_percent: i.discount_percent,
                })),
                payment: {
                    payment_type:     this.paymentType,
                    cash_received:    parseFloat(this.cashReceived) || 0,
                    cash_amount:      parseFloat(this.cashPartial)  || 0,
                    discount_percent: parseFloat(this.discountPercent) || 0,
                    tax_percent:      parseFloat(this.taxPercent) || 0,
                    notes:            this.notes,
                },
            };

            try {
                const res  = await fetch('/pos/sale', {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();

                if (data.success) {
                    this.lastTransaction    = data;
                    this.showPaymentModal   = false;
                    this.toast(`✓ إيصال: ${data.receipt_number}`);

                    // فتح الإيصال في نافذة جديدة
                    if (data.receipt_url) {
                        setTimeout(() => window.open(data.receipt_url, '_blank', 'width=400,height=600'), 400);
                    }

                    this.clearCart();
                } else {
                    this.toast(data.message || 'حدث خطأ في عملية البيع', 'error');
                }
            } catch (e) {
                this.toast('خطأ في الاتصال بالخادم', 'error');
            } finally {
                this.processing = false;
            }
        },

        // ─── الصندوق ──────────────────────────────────────────────
        openCashModal(type) {
            this.cashModalType  = type;
            this.cashAmount     = '';
            this.cashReason     = '';
            this.showCashModal  = true;
        },

        async submitCash() {
            const route = this.cashModalType === 'in'
                ? `/pos/sessions/${this.sessionId}/cash-in`
                : `/pos/sessions/${this.sessionId}/cash-out`;

            try {
                const res  = await fetch(route, {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ amount: this.cashAmount, reason: this.cashReason }),
                });
                const data = await res.json();

                if (data.success) {
                    this.sessionBalance   = parseFloat(data.expected_balance);
                    this.showCashModal    = false;
                    this.toast(data.message);
                } else {
                    this.toast(data.message || 'خطأ', 'error');
                }
            } catch (e) {
                this.toast('خطأ في الاتصال', 'error');
            }
        },

        // ─── إغلاق الجلسة ─────────────────────────────────────────
        openCloseSession() {
            this.closingBalance = '';
            this.closingNotes   = '';
            this.showCloseSessionModal = true;
        },

        // ─── Toast ────────────────────────────────────────────────
        toastTimeout: null,
        toast(msg, type = 'success') {
            const el = document.getElementById('pos-toast');
            if (!el) return;
            el.textContent = msg;
            el.className   = `pos-toast ${type} show`;
            clearTimeout(this.toastTimeout);
            this.toastTimeout = setTimeout(() => {
                el.className = 'pos-toast';
            }, 3000);
        },

        // ─── Formatters ───────────────────────────────────────────
        fmt(n) {
            return Number(n || 0).toLocaleString('ar-SD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
    }));
});

Alpine.start();
