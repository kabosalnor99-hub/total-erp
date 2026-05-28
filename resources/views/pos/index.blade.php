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
    <style>
/* ═══════════════════════════════════════════════════════════
   نقطة البيع — توتال الكلاكلة | لون النظام #0d9488
═══════════════════════════════════════════════════════════ */
:root {
    --teal:          #0d9488;
    --teal-dark:     #0f766e;
    --teal-darker:   #115e59;
    --teal-light:    #14b8a6;
    --teal-glow:     rgba(13,148,136,.18);
    --teal-soft:     rgba(13,148,136,.08);
    --bg:            #0f1923;
    --bg-2:          #151f2b;
    --bg-3:          #1b2838;
    --card:          #1e3040;
    --card-hover:    #243848;
    --border:        #2a3f52;
    --border-light:  #344e63;
    --text:          #e8f4f6;
    --text-muted:    #7ea8b8;
    --success:       #10b981;
    --danger:        #ef4444;
    --warning:       #f59e0b;
    --shadow-teal:   0 4px 20px rgba(13,148,136,.3);
    --radius-sm:     8px;
    --radius-md:     12px;
    --radius-lg:     16px;
    --cart-w:        330px;
    --header-h:      58px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
[x-cloak]{display:none !important}
html,body{height:100%;overflow:hidden;font-family:'Cairo','Tajawal',sans-serif;background:var(--bg);color:var(--text);direction:rtl}
.num-ltr{direction:ltr;text-align:right;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif}
.num-ltr input{direction:ltr;text-align:right}

/* Layout */
.pos-wrapper{display:grid;grid-template-columns:1fr var(--cart-w);grid-template-rows:var(--header-h) 1fr;height:100vh}

/* Header */
.pos-header{grid-column:1/-1;background:linear-gradient(135deg,var(--teal-darker) 0%,var(--teal-dark) 60%,var(--teal) 100%);border-bottom:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:space-between;padding:0 18px;gap:12px;z-index:10;box-shadow:0 2px 20px rgba(13,148,136,.4)}
.pos-header .logo{display:flex;align-items:center;gap:8px;font-weight:800;font-size:16px;color:#fff;text-decoration:none;white-space:nowrap}
.pos-header .session-info{display:flex;align-items:center;gap:6px;font-size:12px;color:rgba(255,255,255,.8);background:rgba(255,255,255,.1);border-radius:20px;padding:4px 12px;border:1px solid rgba(255,255,255,.15)}
.pos-header .session-badge{background:rgba(255,255,255,.25);color:#fff;border-radius:20px;padding:2px 10px;font-size:10px;font-weight:700;border:1px solid rgba(255,255,255,.2)}
.pos-header .header-actions{display:flex;align-items:center;gap:6px}
.pos-header .header-actions .btn-pos-secondary{background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.2);color:rgba(255,255,255,.9);padding:5px 12px;font-size:11px;backdrop-filter:blur(4px)}
.pos-header .header-actions .btn-pos-secondary:hover{background:rgba(255,255,255,.22);color:#fff;transform:translateY(-1px)}

/* Products Panel */
.pos-products{background:var(--bg);display:flex;flex-direction:column;overflow:hidden}

/* Search */
.pos-search-bar{padding:10px 14px;background:var(--bg-2);border-bottom:1px solid var(--border);display:flex;gap:8px;align-items:center}
.pos-search-input{flex:1;background:var(--bg-3);border:1.5px solid var(--border);border-radius:10px;padding:9px 14px;color:var(--text);font-size:14px;font-family:inherit;outline:none;transition:all .2s}
.pos-search-input:focus{border-color:var(--teal);box-shadow:0 0 0 3px var(--teal-glow);background:var(--card)}
.pos-search-input::placeholder{color:var(--text-muted)}

/* Categories */
.pos-categories{display:flex;gap:6px;padding:8px 14px;overflow-x:auto;background:var(--bg-2);border-bottom:1px solid var(--border);scrollbar-width:none}
.pos-categories::-webkit-scrollbar{display:none}
.pos-cat-btn{white-space:nowrap;padding:5px 16px;border-radius:20px;font-size:12px;font-weight:700;border:1.5px solid var(--border);background:transparent;color:var(--text-muted);cursor:pointer;transition:all .2s;font-family:inherit}
.pos-cat-btn:hover{border-color:var(--teal);color:var(--teal-light);background:var(--teal-soft)}
.pos-cat-btn.active{background:linear-gradient(135deg,var(--teal) 0%,var(--teal-dark) 100%);border-color:var(--teal);color:#fff;box-shadow:var(--shadow-teal)}

/* Product Grid */
.pos-grid{flex:1;overflow-y:auto;padding:12px 14px;display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;align-content:start}
.pos-grid::-webkit-scrollbar{width:4px}
.pos-grid::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px}

/* Product Card */
.product-card{background:var(--card);border:1.5px solid var(--border);border-radius:var(--radius-md);cursor:pointer;transition:all .18s cubic-bezier(0.4,0,0.2,1);overflow:hidden;position:relative;user-select:none;box-shadow:0 2px 8px rgba(0,0,0,.25)}
.product-card:hover{background:var(--card-hover);border-color:var(--teal);transform:translateY(-3px);box-shadow:var(--shadow-teal)}
.product-card:active{transform:scale(.96)}
.product-card.out-of-stock{opacity:.4;cursor:not-allowed;pointer-events:none}
.product-card-img-placeholder{width:100%;height:120px;background:linear-gradient(135deg,var(--bg-3) 0%,var(--border) 100%);display:flex;align-items:center;justify-content:center;font-size:40px;color:var(--text-muted);border-bottom:1px solid var(--border);overflow:hidden;position:relative}
.product-card-img-placeholder img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block}
.product-card-body{padding:10px 12px;background:#1e3040}
.product-card-name{font-size:13px;font-weight:700;line-height:1.4;color:#e8f4f6 !important;margin-bottom:5px;display:block;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;max-width:100%}
.product-card-category{font-size:10px;color:#7ea8b8;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block}
.product-card-price{font-size:14px;font-weight:800;color:#14b8a6 !important;display:block}
.product-card-stock{font-size:11px;color:#7ea8b8;margin-top:3px;display:block}
.stock-badge{position:absolute;top:5px;left:5px;background:linear-gradient(135deg,var(--danger) 0%,#b91c1c 100%);color:#fff;font-size:9px;font-weight:800;padding:2px 7px;border-radius:6px}
.stock-badge.low{background:linear-gradient(135deg,var(--warning) 0%,#d97706 100%)}

/* Cart Panel */
.pos-cart{background:var(--bg-2);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden;box-shadow:-4px 0 20px rgba(0,0,0,.2)}
.pos-cart-header{padding:13px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--bg-3)}
.pos-cart-header h2{font-size:15px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:6px}
.cart-count{background:linear-gradient(135deg,var(--teal) 0%,var(--teal-dark) 100%);color:#fff;border-radius:50%;width:22px;height:22px;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow-teal)}

/* Customer Row */
.pos-customer-row{padding:9px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-muted);background:rgba(13,148,136,.04)}
.pos-customer-row .customer-name{flex:1;color:var(--teal-light);font-weight:700;font-size:13px;cursor:pointer}
.pos-customer-row .btn-remove-customer{background:none;border:none;color:var(--danger);cursor:pointer;font-size:14px;padding:2px;opacity:.7;transition:opacity .15s}
.pos-customer-row .btn-remove-customer:hover{opacity:1}

/* Cart Items */
.pos-cart-items{flex:1;overflow-y:auto;padding:6px 0}
.pos-cart-items::-webkit-scrollbar{width:3px}
.pos-cart-items::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px}
.cart-empty{height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;color:var(--text-muted);font-size:13px;padding:20px}
.cart-empty-icon{font-size:42px;opacity:.3}
.cart-item{padding:9px 16px;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(42,63,82,.6);transition:background .15s}
.cart-item:hover{background:rgba(255,255,255,.03)}
.cart-item-name{font-size:12px;font-weight:700;color:var(--text);line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.cart-item-sub{font-size:10px;color:var(--text-muted);margin-top:2px}
.cart-item-total{font-size:13px;font-weight:800;color:var(--teal-light);white-space:nowrap;min-width:52px;text-align:left}

/* Qty Control */
.qty-control{display:flex;align-items:center;gap:4px}
.qty-btn{width:22px;height:22px;border-radius:50%;border:1.5px solid var(--border);background:var(--bg-3);color:var(--text);font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;line-height:1}
.qty-btn:hover{background:var(--teal);border-color:var(--teal);color:#fff}
.qty-input{width:36px;text-align:center;background:var(--bg-3);border:1.5px solid var(--border);border-radius:6px;color:var(--text);font-size:13px;font-weight:700;padding:2px 0;font-family:inherit;outline:none}
.qty-input:focus{border-color:var(--teal)}
.btn-remove-item{background:none;border:none;color:var(--danger);cursor:pointer;font-size:14px;opacity:.5;padding:4px;transition:opacity .15s}
.btn-remove-item:hover{opacity:1}

/* Cart Summary */
.pos-cart-summary{border-top:1px solid var(--border);padding:12px 16px;background:var(--bg-3)}
.summary-row{display:flex;justify-content:space-between;align-items:center;font-size:12px;color:var(--text-muted);padding:3px 0}
.summary-row.discount{color:var(--danger)}
.summary-row.tax{color:var(--warning)}
.summary-row.total{font-size:20px;font-weight:800;color:var(--text);border-top:1px solid var(--border);margin-top:8px;padding-top:10px}
.summary-row.total span:last-child{color:var(--teal-light)}
.discount-row{display:flex;align-items:center;gap:6px;padding:5px 0}
.discount-row label{font-size:11px;color:var(--text-muted);white-space:nowrap}
.discount-input{width:60px;background:var(--bg);border:1.5px solid var(--border);border-radius:8px;padding:4px 6px;color:var(--text);font-size:12px;font-family:inherit;outline:none;text-align:center;transition:border-color .2s}
.discount-input:focus{border-color:var(--teal)}

/* Buttons */
.btn-checkout{width:100%;padding:13px;background:linear-gradient(135deg,var(--teal) 0%,var(--teal-dark) 100%);color:#fff;font-size:16px;font-weight:800;border:none;border-radius:var(--radius-md);cursor:pointer;margin-top:10px;font-family:inherit;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:var(--shadow-teal)}
.btn-checkout:hover:not(:disabled){background:linear-gradient(135deg,var(--teal-light) 0%,var(--teal) 100%);transform:translateY(-1px);box-shadow:0 6px 24px rgba(13,148,136,.45)}
.btn-checkout:disabled{opacity:.35;cursor:not-allowed;box-shadow:none}
.pos-action-btns{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:8px}
.btn-pos-secondary{padding:8px 12px;background:var(--card);border:1.5px solid var(--border);border-radius:var(--radius-sm);color:var(--text-muted);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;transition:all .15s;text-align:center;white-space:nowrap}
.btn-pos-secondary:hover{border-color:var(--teal);color:var(--teal-light);background:var(--teal-soft)}

/* Modals */
.pos-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.75);display:flex;align-items:center;justify-content:center;z-index:100;padding:16px;backdrop-filter:blur(4px)}
.pos-modal{background:var(--bg-3);border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:24px;width:100%;max-width:440px;max-height:90vh;overflow-y:auto;box-shadow:0 8px 40px rgba(0,0,0,.45)}
.pos-modal h3{font-size:17px;font-weight:800;margin-bottom:18px;color:var(--text);display:flex;align-items:center;gap:8px;border-bottom:1px solid var(--border);padding-bottom:14px}
.pos-modal-lg{max-width:560px}
.payment-types{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:16px}
.pay-type-btn{padding:12px 8px;border-radius:var(--radius-sm);border:2px solid var(--border);background:var(--card);color:var(--text-muted);font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s;text-align:center}
.pay-type-btn .icon{display:block;font-size:22px;margin-bottom:5px}
.pay-type-btn:hover{border-color:var(--teal);color:var(--teal-light);background:var(--teal-soft)}
.pay-type-btn.active{border-color:var(--teal);background:rgba(13,148,136,.15);color:var(--teal-light);box-shadow:0 0 0 3px var(--teal-glow)}
.pos-label{display:block;font-size:11px;color:var(--text-muted);margin-bottom:5px;margin-top:12px;font-weight:600}
.pos-input{width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:10px 12px;color:var(--text);font-size:14px;font-family:inherit;outline:none;transition:all .2s}
.pos-input:focus{border-color:var(--teal);box-shadow:0 0 0 3px var(--teal-glow)}
.pos-input::placeholder{color:var(--text-muted)}
.pos-input-lg{font-size:16px;padding:12px 14px}
.pos-input-textarea{resize:vertical;min-height:60px}
.change-display{background:rgba(13,148,136,.12);border:1.5px solid var(--teal);border-radius:var(--radius-sm);padding:12px 16px;text-align:center;margin-top:12px}
.change-display .label{font-size:11px;color:var(--text-muted);margin-bottom:4px}
.change-display .amount{font-size:24px;font-weight:800;color:var(--teal-light)}
.customer-search-results{max-height:240px;overflow-y:auto;margin-top:8px;border-radius:var(--radius-sm);border:1.5px solid var(--border);background:var(--card)}
.customer-result-item{padding:10px 14px;cursor:pointer;border-bottom:1px solid var(--border);transition:background .15s}
.customer-result-item:last-child{border-bottom:none}
.customer-result-item:hover{background:var(--card-hover)}
.customer-result-item .cname{font-size:13px;font-weight:700;color:var(--text)}
.customer-result-item .cphone{font-size:11px;color:var(--text-muted);margin-top:2px}
.pos-modal-header-danger{display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--border)}
.pos-modal-icon{font-size:28px}
.pos-modal-title h3{font-size:17px;font-weight:800;color:var(--text);border:none;padding:0;margin:0}
.pos-modal-title p{font-size:12px;color:var(--text-muted);margin-top:2px}
.pos-modal-body{margin-bottom:4px}
.session-summary{margin-bottom:16px}
.session-summary-title{font-size:12px;color:var(--text-muted);font-weight:700;letter-spacing:.05em;text-transform:uppercase;margin-bottom:10px}
.session-summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.session-stat-card{background:linear-gradient(135deg,var(--card) 0%,var(--card-hover) 100%);border:1.5px solid var(--border);border-radius:var(--radius-md);padding:12px;display:flex;align-items:center;gap:10px;transition:all .2s}
.session-stat-card:hover{border-color:var(--teal);transform:translateY(-2px);box-shadow:var(--shadow-teal)}
.session-stat-icon{font-size:22px;flex-shrink:0}
.session-stat-value{font-size:15px;font-weight:800;color:var(--teal-light);line-height:1.2}
.session-stat-label{font-size:10px;color:var(--text-muted);margin-top:2px}
.close-session-form{background:rgba(245,158,11,.06);border:1.5px solid rgba(245,158,11,.3);border-radius:var(--radius-md);padding:16px}
.form-row{margin-bottom:12px}
.form-row:last-child{margin-bottom:0}
.balance-diff{margin-top:8px;padding:8px 12px;border-radius:8px;font-size:12px;font-weight:700}
.balance-diff-warning{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.balance-diff-ok{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#6ee7b7}
.pos-modal-footer{display:flex;gap:8px;margin-top:20px}
.btn-pos-primary{flex:1;padding:11px;background:linear-gradient(135deg,var(--teal) 0%,var(--teal-dark) 100%);color:#fff;border:none;border-radius:var(--radius-sm);font-size:14px;font-weight:800;cursor:pointer;font-family:inherit;transition:all .2s;box-shadow:var(--shadow-teal)}
.btn-pos-primary:hover{background:linear-gradient(135deg,var(--teal-light) 0%,var(--teal) 100%);transform:translateY(-1px)}
.btn-pos-primary:disabled{opacity:.4;cursor:not-allowed}
.btn-pos-cancel{padding:11px 18px;background:var(--card);border:1.5px solid var(--border);border-radius:var(--radius-sm);color:var(--text-muted);font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;transition:all .15s}
.btn-pos-cancel:hover{border-color:var(--danger);color:#fca5a5;background:rgba(239,68,68,.08)}
.btn-pos-danger{background:linear-gradient(135deg,var(--danger) 0%,#b91c1c 100%);color:#fff;border:none;padding:11px 20px;border-radius:var(--radius-sm);font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .2s;box-shadow:0 4px 16px rgba(239,68,68,.3)}
.btn-pos-danger:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(239,68,68,.4)}

/* Toast */
.pos-toast{position:fixed;top:72px;left:50%;transform:translateX(-50%) translateY(-20px);background:linear-gradient(135deg,var(--teal) 0%,var(--teal-dark) 100%);color:#fff;padding:10px 28px;border-radius:30px;font-size:13px;font-weight:700;box-shadow:var(--shadow-teal);z-index:200;opacity:0;transition:all .3s cubic-bezier(0.4,0,0.2,1);pointer-events:none}
.pos-toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
.pos-toast.error{background:linear-gradient(135deg,var(--danger) 0%,#b91c1c 100%)}
.pos-toast.warning{background:linear-gradient(135deg,var(--warning) 0%,#d97706 100%)}

/* Spinner */
.pos-spinner{width:36px;height:36px;border:3px solid var(--border);border-top-color:var(--teal);border-radius:50%;animation:spin .7s linear infinite;margin:20px auto}
@keyframes spin{to{transform:rotate(360deg)}}
.loading-overlay{position:absolute;inset:0;background:rgba(15,25,35,.8);display:flex;align-items:center;justify-content:center;z-index:50;border-radius:inherit;backdrop-filter:blur(2px)}

/* Print */
@media print{*{-webkit-print-color-adjust:exact !important;print-color-adjust:exact !important}body{background:#fff;color:#000;direction:rtl;font-family:'Cairo',monospace}.no-print{display:none !important}.receipt-wrap{width:80mm;margin:0 auto}}

/* Responsive Tablet */
@media(max-width:768px){
    :root{--cart-w:100%}
    .pos-wrapper{grid-template-columns:1fr;grid-template-rows:var(--header-h) 1fr auto}
    .pos-cart{max-height:44vh;border-right:none;border-top:2px solid var(--teal)}
}

/* Responsive Mobile */
@media(max-width:480px){
    :root{--header-h:52px}
    .pos-header{padding:0 10px;gap:6px}
    .pos-header .logo{font-size:13px}
    .pos-header .logo span{display:none}
    .pos-header .session-info{font-size:10px;padding:3px 8px;gap:4px}
    .pos-header .session-badge{font-size:9px;padding:1px 6px}
    .pos-header .header-actions{gap:4px}
    .pos-header .header-actions .btn-pos-secondary{padding:4px 8px;font-size:10px}
    .pos-search-bar{padding:7px 10px}
    .pos-search-input{font-size:13px;padding:7px 10px}
    .pos-categories{padding:6px 10px;gap:5px}
    .pos-cat-btn{padding:4px 11px;font-size:11px}
    .pos-grid{padding:8px 10px;gap:8px;grid-template-columns:repeat(2,1fr)}
    .product-card-img-placeholder{height:60px;font-size:24px}
    .product-card-body{padding:6px 8px}
    .product-card-name{font-size:11px}
    .product-card-price{font-size:12px}
    .pos-cart-header{padding:8px 12px}
    .pos-cart-header h2{font-size:13px}
    .pos-customer-row{padding:7px 12px;font-size:11px}
    .cart-item{padding:7px 12px;gap:8px}
    .cart-item-name{font-size:11px}
    .cart-item-total{font-size:12px;min-width:44px}
    .pos-cart-summary{padding:10px 12px}
    .summary-row.total{font-size:17px}
    .btn-checkout{font-size:14px;padding:11px}
    .pos-modal{padding:18px}
    .pos-modal h3{font-size:15px}
    .session-summary-grid{grid-template-columns:1fr 1fr}
    .session-summary-grid .session-stat-card:last-child{grid-column:1/-1}
}
    </style>
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
            <span>جلسة #<span class="num-ltr">{{ $session->id }}</span></span>
            <span class="session-badge">مفتوحة</span>
            <span class="num-ltr">{{ $session->opened_at->format('H:i') }}</span>
        </div>

        <div class="session-info" style="gap:4px">
            <span>الصندوق:</span>
            <span class="num-ltr" style="color:#4FB3C0;font-weight:700" x-text="fmt(sessionBalance) + ' ج.س'"></span>
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
                    <div class="product-card-img-placeholder">
                        <template x-if="p.image_url">
                            <img :src="p.image_url" :alt="p.name">
                        </template>
                        <template x-if="!p.image_url">
                            <span>🔧</span>
                        </template>
                    </div>

                    <div class="product-card-body">
                        <p class="product-card-name" x-text="p.name" style="color:#e8f4f6"></p>
                        <template x-if="p.category">
                            <p class="product-card-category" x-text="p.category"></p>
                        </template>
                        <p class="product-card-price num-ltr" x-text="fmt(p.sale_price) + ' ج.س'" style="color:#14b8a6"></p>
                        <p class="product-card-stock num-ltr" x-text="'متاح: ' + p.quantity" style="color:#7ea8b8"></p>
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
                <span class="cart-count num-ltr" x-text="cartCount"></span>
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
                <div style="display:flex;gap:8px;align-items:center">
                    <button class="customer-name" @click="openCustomerModal()" style="font-size:12px;background:none;border:none;text-align:right">
                        + تحديد عميل (اختياري)
                    </button>
                    <button class="btn-pos-secondary" @click="openAddCustomerModal()" style="font-size:11px;padding:4px 8px">
                        + إضافة عميل
                    </button>
                </div>
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
                        <div class="cart-item-sub num-ltr">
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
                                class="qty-input num-ltr"
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
                            class="qty-input num-ltr"
                            :value="item.quantity"
                            @change="updateQty(idx, $event.target.value)"
                        >
                        <button class="qty-btn" @click="changeQty(idx, 1)">+</button>
                    </div>

                    <div class="cart-item-total num-ltr" x-text="fmt(item.total)"></div>

                    <button class="btn-remove-item" @click="removeFromCart(idx)" title="حذف">✕</button>
                </div>
            </template>
        </div>

        {{-- الملخص --}}
        <div class="pos-cart-summary">
            <div class="summary-row">
                <span>الإجمالي الفرعي</span>
                <span class="num-ltr" x-text="fmt(subtotal) + ' ج.س'"></span>
            </div>

            {{-- خصم الفاتورة --}}
            <div class="discount-row">
                <label>خصم الفاتورة %</label>
                <input type="number" min="0" max="100" step="0.5" class="discount-input num-ltr" x-model="discountPercent" placeholder="0">
                <span class="num-ltr" style="font-size:11px;color:#E53935" x-text="totalDiscount > 0 ? '−' + fmt(totalDiscount) + ' ج.س' : ''"></span>
            </div>

            <template x-if="taxPercent > 0">
                <div class="summary-row tax">
                    <span>ضريبة (<span class="num-ltr" x-text="taxPercent"></span>%)</span>
                    <span class="num-ltr" x-text="fmt(taxAmount) + ' ج.س'"></span>
                </div>
            </template>

            <div class="summary-row total">
                <span>الإجمالي</span>
                <span class="num-ltr" x-text="fmt(grandTotal) + ' ج.س'"></span>
            </div>

            <button
                class="btn-checkout"
                :disabled="cart.length === 0"
                @click="openPaymentModal()"
            >
                💳 الدفع
            </button>

            <button
                class="btn-pos-secondary"
                style="margin-top:8px"
                :disabled="cart.length === 0"
                @click="saveDraft()"
            >
                📋 فاتورة مبدئية
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
                    <span class="num-ltr" style="color:#4FB3C0;font-size:18px;font-weight:800" x-text="fmt(grandTotal) + ' ج.س'"></span>
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
                    <input type="number" min="0" step="0.01" class="pos-input num-ltr" x-model="cashReceived" placeholder="0.00" autofocus>
                    <template x-if="(parseFloat(cashReceived)||0) >= grandTotal">
                        <div class="change-display">
                            <div class="label">المبلغ المرتجع</div>
                            <div class="amount num-ltr" x-text="fmt(changeAmount) + ' ج.س'"></div>
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
                    <input type="number" min="0" step="0.01" class="pos-input num-ltr" x-model="cashPartial" placeholder="0.00">
                    <div style="font-size:12px;color:#7EADB8;margin-top:6px;display:flex;justify-content:space-between">
                        <span>الجزء الآجل:</span>
                        <span class="num-ltr" style="color:#4FB3C0" x-text="fmt(Math.max(0, grandTotal - (parseFloat(cashPartial)||0))) + ' ج.س'"></span>
                    </div>
                    <label class="pos-label">المبلغ المستلم نقداً (ج.س)</label>
                    <input type="number" min="0" step="0.01" class="pos-input num-ltr" x-model="cashReceived" placeholder="0.00">
                    <template x-if="changeAmount > 0">
                        <div class="change-display">
                            <div class="label">الباقي</div>
                            <div class="amount num-ltr" x-text="fmt(changeAmount) + ' ج.س'"></div>
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

    {{-- ═══════════════════ MODAL: إضافة عميل ═════════════════════════ --}}
    <div class="pos-modal-overlay" x-show="showAddCustomerModal" x-cloak @click.self="showAddCustomerModal=false">
        <div class="pos-modal">
            <h3>👤 إضافة عميل جديد</h3>
            <label class="pos-label">الاسم *</label>
            <input type="text" class="pos-input" x-model="newCustomerName" placeholder="اسم العميل">
            <label class="pos-label">الهاتف</label>
            <input type="text" class="pos-input" x-model="newCustomerPhone" placeholder="رقم الهاتف">
            <label class="pos-label">العنوان</label>
            <input type="text" class="pos-input" x-model="newCustomerAddress" placeholder="العنوان">
            <div class="pos-modal-footer">
                <button class="btn-pos-primary" @click="createCustomer()">إضافة</button>
                <button class="btn-pos-cancel" @click="showAddCustomerModal=false">إلغاء</button>
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
        <div class="pos-modal pos-modal-lg">
            <div class="pos-modal-header-danger">
                <div class="pos-modal-icon">🔴</div>
                <div class="pos-modal-title">
                    <h3>إغلاق الجلسة</h3>
                    <p>تأكيد إغلاق جلسة #{{ $session->id }}</p>
                </div>
            </div>

            <div class="pos-modal-body">
                {{-- ملخص الجلسة --}}
                <div class="session-summary">
                    <div class="session-summary-title">ملخص الجلسة</div>
                    <div class="session-summary-grid">
                        <div class="session-stat-card">
                            <div class="session-stat-icon">💰</div>
                            <div class="session-stat-content">
                                <div class="session-stat-value num-ltr">{{ number_format($session->total_sales, 2) }}</div>
                                <div class="session-stat-label">إجمالي المبيعات</div>
                            </div>
                        </div>
                        <div class="session-stat-card">
                            <div class="session-stat-icon">📄</div>
                            <div class="session-stat-content">
                                <div class="session-stat-value num-ltr">{{ $session->transactions_count }}</div>
                                <div class="session-stat-label">عدد الفواتير</div>
                            </div>
                        </div>
                        <div class="session-stat-card">
                            <div class="session-stat-icon">🏦</div>
                            <div class="session-stat-content">
                                <div class="session-stat-value num-ltr" x-text="fmt(sessionBalance)"></div>
                                <div class="session-stat-label">رصيد متوقع</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- نموذج الإغلاق --}}
                <form method="POST" action="{{ route('pos.sessions.close', $session) }}" class="close-session-form" id="closeSessionForm">
                    @csrf
                    <div class="form-row">
                        <label class="pos-label">رصيد الصندوق الفعلي (ج.س) *</label>
                        <input type="number" name="closing_balance" min="0" step="0.01" class="pos-input pos-input-lg num-ltr" x-model="closingBalance" placeholder="0.00" required>
                        <template x-if="closingBalance && sessionBalance">
                            <div class="balance-diff" :class="Math.abs(parseFloat(closingBalance) - sessionBalance) > 0.01 ? 'balance-diff-warning' : 'balance-diff-ok'">
                                <span x-text="Math.abs(parseFloat(closingBalance) - sessionBalance) > 0.01 ? '⚠️ فرق: ' + fmt(Math.abs(parseFloat(closingBalance) - sessionBalance)) + ' ج.س' : '✓ متطابق'"></span>
                            </div>
                        </template>
                    </div>
                    <div class="form-row">
                        <label class="pos-label">ملاحظات (اختياري)</label>
                        <textarea name="closing_notes" class="pos-input pos-input-textarea" x-model="closingNotes" placeholder="ملاحظات الإغلاق..." rows="2"></textarea>
                    </div>
                </form>
            </div>

            <div class="pos-modal-footer">
                <button type="button" class="btn-pos-cancel" @click="showCloseSessionModal=false">إلغاء</button>
                <button type="submit" form="closeSessionForm" class="btn-pos-danger">🔴 إغلاق الجلسة</button>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div id="pos-toast" class="pos-toast"></div>

</div>{{-- end pos-wrapper --}}

<script src="{{ asset('js/pos.js') }}"></script>

</body>
</html>
