{{-- المسار الكامل: resources/views/pos/receipt.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال رقم {{ $transaction->receipt_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Courier New', 'DejaVu Sans Mono', monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            direction: rtl;
        }

        /* ── حاوية الإيصال 72mm ── */
        .receipt {
            width: 72mm;
            max-width: 72mm;
            margin: 0;
            padding: 3mm 2mm;
        }

        /* ── الرأس ── */
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .header .company-name {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .header .company-sub {
            font-size: 10px;
            margin-top: 2px;
            color: #333;
        }
        .header .logo-line {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 3px;
            margin-bottom: 2px;
        }

        /* ── معلومات الإيصال ── */
        .meta {
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
            font-size: 11px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .meta-row .label { color: #555; }
        .meta-row .value { font-weight: bold; }

        /* ── جدول المنتجات ── */
        .items-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 10px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            margin-bottom: 3px;
        }
        .item-row {
            margin-bottom: 4px;
            font-size: 11px;
        }
        .item-name {
            font-weight: bold;
            margin-bottom: 1px;
            word-break: break-word;
        }
        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #333;
        }
        .item-total {
            font-weight: bold;
            color: #000;
        }

        /* ── المجاميع ── */
        .totals {
            border-top: 1px dashed #000;
            padding-top: 6px;
            margin-top: 6px;
            font-size: 11px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .total-row.grand {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 4px 0;
            margin: 4px 0;
        }
        .total-row.change {
            font-size: 13px;
            font-weight: bold;
            color: #000;
        }

        /* ── التذييل ── */
        .footer {
            border-top: 1px dashed #000;
            margin-top: 8px;
            padding-top: 6px;
            text-align: center;
            font-size: 10px;
            color: #444;
            line-height: 1.6;
        }
        .footer .thanks {
            font-size: 12px;
            font-weight: bold;
            color: #000;
            margin-bottom: 3px;
        }

        /* ── الباركود ── */
        .barcode-section {
            text-align: center;
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px dashed #000;
        }
        .barcode-section canvas {
            max-width: 100%;
            height: auto;
        }
        .barcode-label {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
        }

        /* ── حالة الإيصال الملغي ── */
        .cancelled-stamp {
            text-align: center;
            border: 3px solid #000;
            color: #000;
            font-size: 16px;
            font-weight: 900;
            padding: 4px;
            margin: 6px 0;
            letter-spacing: 2px;
        }

        /* ── أزرار التحكم (لا تُطبع) ── */
        .no-print {
            display: flex;
            gap: 8px;
            justify-content: center;
            padding: 12px;
            background: #f5f5f5;
        }
        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-family: 'Cairo', sans-serif;
        }
        .btn-print  { background: #00838F; color: #fff; }
        .btn-close  { background: #6c757d; color: #fff; }
        .btn-whatsapp { background: #25D366; color: #fff; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff; margin: 0; padding: 0; }
            .receipt { margin: 0; padding: 1mm 1mm; width: 72mm; }
            @page { margin: 0; size: 72mm auto; }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>
<body>

{{-- ── أزرار التحكم ── --}}
<div class="no-print">
    <button class="btn btn-print" onclick="window.print()">🖨️ طباعة</button>
    <a href="{{ route('pos.index') }}" class="btn btn-close">← العودة للكاشير</a>
    @if($transaction->customer && $transaction->customer->phone)
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $transaction->customer->phone) }}?text={{ urlencode('إيصال شراء رقم: ' . $transaction->receipt_number . ' — المبلغ: ' . number_format($transaction->total, 2) . ' ج.س') }}"
       target="_blank" class="btn btn-whatsapp">📲 واتساب</a>
    @endif
</div>

{{-- ── الإيصال ── --}}
<div class="receipt">

    {{-- الرأس --}}
    <div class="header">
        <div class="logo-line">TOTAL</div>
        <div class="company-name">
            {{ $settings['company_name'] ?? 'توتال الكلاكلة' }}
        </div>
        <div class="company-sub">
            {{ $settings['company_address'] ?? 'الكلاكلة — الخرطوم، السودان' }}
        </div>
        @if(!empty($settings['company_phone']))
        <div class="company-sub">هاتف: {{ $settings['company_phone'] }}</div>
        @endif
        @if(!empty($settings['company_tax_number']))
        <div class="company-sub">الرقم الضريبي: {{ $settings['company_tax_number'] }}</div>
        @endif
    </div>

    {{-- ختم الإلغاء إن وجد --}}
    @if($transaction->status === 'cancelled')
    <div class="cancelled-stamp">** ملغي **</div>
    @endif

    {{-- معلومات الإيصال --}}
    <div class="meta">
        <div class="meta-row">
            <span class="label">رقم الإيصال:</span>
            <span class="value">{{ $transaction->receipt_number }}</span>
        </div>
        <div class="meta-row">
            <span class="label">التاريخ:</span>
            <span class="value">{{ $transaction->created_at->format('Y/m/d') }}</span>
        </div>
        <div class="meta-row">
            <span class="label">الوقت:</span>
            <span class="value">{{ $transaction->created_at->format('h:i A') }}</span>
        </div>
        <div class="meta-row">
            <span class="label">الكاشير:</span>
            <span class="value">{{ $transaction->user->name ?? '—' }}</span>
        </div>
        @if($transaction->customer)
        <div class="meta-row">
            <span class="label">العميل:</span>
            <span class="value">{{ $transaction->customer->name }}</span>
        </div>
        @endif
    </div>

    {{-- رأس جدول المنتجات --}}
    <div class="items-header">
        <span>الصنف</span>
        <span>الإجمالي</span>
    </div>

    {{-- المنتجات --}}
    @foreach($transaction->items as $item)
    <div class="item-row">
        <div class="item-name">{{ $item->product->name_ar ?? $item->product->name_en ?? '—' }}</div>
        <div class="item-details">
            <span>{{ number_format($item->quantity, 2) }} × {{ number_format($item->price, 2) }}
                @if($item->discount > 0)
                    (خصم {{ number_format($item->discount, 2) }})
                @endif
            </span>
            <span class="item-total">{{ number_format($item->total, 2) }}</span>
        </div>
    </div>
    @endforeach

    {{-- المجاميع --}}
    <div class="totals">
        <div class="total-row">
            <span>المجموع الفرعي:</span>
            <span>{{ number_format($transaction->subtotal, 2) }} ج.س</span>
        </div>

        @if($transaction->discount_amount > 0)
        <div class="total-row">
            <span>الخصم
                @if($transaction->discount_percent > 0)
                ({{ number_format($transaction->discount_percent, 1) }}%)
                @endif:
            </span>
            <span>- {{ number_format($transaction->discount_amount, 2) }} ج.س</span>
        </div>
        @endif

        @if($transaction->tax_amount > 0)
        <div class="total-row">
            <span>ضريبة القيمة المضافة ({{ $transaction->tax_percent }}%):</span>
            <span>{{ number_format($transaction->tax_amount, 2) }} ج.س</span>
        </div>
        @endif

        <div class="total-row grand">
            <span>الإجمالي:</span>
            <span>{{ number_format($transaction->total, 2) }} ج.س</span>
        </div>

        @if($transaction->payment_type === 'cash' || $transaction->payment_type === 'split')
        <div class="total-row">
            <span>المبلغ المستلم:</span>
            <span>{{ number_format($transaction->cash_received, 2) }} ج.س</span>
        </div>
        @if($transaction->change_amount > 0)
        <div class="total-row change">
            <span>المبلغ المرتجع:</span>
            <span>{{ number_format($transaction->change_amount, 2) }} ج.س</span>
        </div>
        @endif
        @endif

        @if($transaction->payment_type === 'credit' || $transaction->payment_type === 'split')
        <div class="total-row">
            <span>المبلغ الآجل:</span>
            <span>{{ number_format($transaction->credit_amount, 2) }} ج.س</span>
        </div>
        @endif

        <div class="total-row" style="margin-top:4px; font-size:10px; color:#555;">
            <span>طريقة الدفع:</span>
            <span>{{ $transaction->payment_type_label }}</span>
        </div>
    </div>

    {{-- عدد الأصناف --}}
    <div style="text-align:center; font-size:10px; color:#666; margin-top:4px;">
        عدد الأصناف: {{ $transaction->items->count() }} |
        إجمالي القطع: {{ number_format($transaction->items->sum('quantity'), 0) }}
    </div>

    {{-- التذييل --}}
    <div class="footer">
        <div class="thanks">شكراً لتسوقكم من توتال الكلاكلة</div>
        <div>أدوات كهربائية ومعدات عالية الجودة</div>
        @if(!empty($settings['company_website']))
        <div>{{ $settings['company_website'] }}</div>
        @endif
        <div style="margin-top:4px; font-size:9px; color:#888;">
            هذا الإيصال وثيقة رسمية — يُرجى الاحتفاظ به
        </div>
    </div>

    {{-- الباركود --}}
    <div class="barcode-section">
        <canvas id="barcode"></canvas>
        <div class="barcode-label">{{ $transaction->receipt_number }}</div>
    </div>

</div>{{-- .receipt --}}

<script>
    // توليد الباركود
    document.addEventListener('DOMContentLoaded', function() {
        try {
            JsBarcode("#barcode", "{{ $transaction->receipt_number }}", {
                format: "CODE128",
                lineColor: "#000",
                width: 2,
                height: 40,
                displayValue: false,
                margin: 0
            });
        } catch (e) {
            console.error('Error generating barcode:', e);
        }
    });

    // طباعة تلقائية عند فتح الصفحة إذا جاء من POS مباشرة
    @if(request()->has('auto_print'))
    window.onload = function() { window.print(); }
    @endif
</script>
</body>
</html>