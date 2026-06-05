{{-- resources/views/invoices/print.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة — {{ $invoice->invoice_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #fff;
            font-family: 'Tajawal', Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }

        /* ===== LAYOUT ===== */
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            display: flex;
            flex-direction: column;
        }

        /* ===== TOP HEADER STRIP ===== */
        .header-main {
            background: #1B4F72;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 10px 16px;
            gap: 10px;
        }
        .header-en {
            color: #fff;
            text-align: right;
        }
        .header-en .company-name-en {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .header-en .company-sub-en {
            font-size: 10px;
            font-weight: 400;
            color: #a8d4e6;
            margin-top: 2px;
        }

        /* Center logo */
        .header-logo {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }
        .logo-box {
            background: #fff;
            border-radius: 50%;
            width: 72px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
        }
        .logo-total {
            font-size: 22px;
            font-weight: 900;
            color: #1B4F72;
            letter-spacing: -1px;
            font-style: italic;
        }
        .logo-total span {
            color: #e74c3c;
        }
        .logo-sudan {
            font-size: 8px;
            font-weight: 700;
            color: #1B4F72;
            letter-spacing: 1px;
        }
        .logo-tagline {
            font-size: 8px;
            color: #a8d4e6;
            font-weight: 500;
            margin-top: 2px;
        }

        /* Right: Arabic name */
        .header-ar {
            color: #fff;
            text-align: left;
            direction: rtl;
        }
        .header-ar .company-name-ar {
            font-size: 20px;
            font-weight: 900;
            line-height: 1.2;
        }
        .header-ar .company-sub-ar {
            font-size: 10px;
            color: #a8d4e6;
            margin-top: 2px;
        }

        /* ===== TAX / INVOICE STRIP ===== */
        .subheader {
            background: #fff;
            border-bottom: 2px solid #1B4F72;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 4px 16px;
            border-top: 1px solid #d0d0d0;
        }
        .tax-box {
            direction: rtl;
            text-align: right;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .tax-label {
            font-size: 11px;
            font-weight: 700;
            color: #e74c3c;
        }
        .tax-number-fields {
            display: flex;
            gap: 3px;
            direction: ltr;
        }
        .tax-cell {
            width: 18px;
            height: 20px;
            border: 1px solid #e74c3c;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
        }
        .invoice-word {
            font-size: 16px;
            font-weight: 900;
            color: #e74c3c;
            text-align: center;
            font-style: italic;
        }
        .address-strip {
            text-align: left;
            direction: rtl;
            font-size: 10px;
            font-weight: 700;
            color: #e74c3c;
        }

        /* ===== RED DIVIDER ===== */
        .red-bar {
            background: #c0392b;
            height: 6px;
        }

        /* ===== DATE / CUSTOMER SECTION ===== */
        .meta-section {
            padding: 10px 16px 6px;
            direction: rtl;
        }
        .meta-row {
            display: flex;
            align-items: center;
            margin-bottom: 6px;
            gap: 8px;
        }
        .meta-label {
            font-size: 12px;
            font-weight: 700;
            color: #1B4F72;
            min-width: 70px;
            text-align: right;
        }
        .meta-dots {
            flex: 1;
            border-bottom: 1px dotted #555;
            margin: 0 6px;
            position: relative;
            bottom: 2px;
        }
        .meta-value {
            font-size: 12px;
            font-weight: 500;
            color: #000;
            min-width: 120px;
        }
        .meta-label-en {
            font-size: 11px;
            font-weight: 700;
            color: #1B4F72;
            direction: ltr;
        }
        .meta-row-dual {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 6px;
        }
        .meta-half {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ===== PRODUCTS TABLE ===== */
        .products-table {
            width: calc(100% - 32px);
            margin: 6px 16px;
            border-collapse: collapse;
            border: 1.5px solid #1B4F72;
            direction: rtl;
        }
        .products-table thead tr {
            background: #1B4F72;
        }
        .products-table thead th {
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 7px 6px;
            border: 1px solid #2980b9;
            text-align: center;
        }
        .products-table thead th.th-ar { }
        .products-table thead th.th-en {
            font-style: italic;
            font-weight: 400;
            font-size: 10px;
        }
        .products-table thead th.dual {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1px;
        }
        /* Two-line header cells */
        .products-table .th-dual-ar { font-size: 11px; font-weight: 700; }
        .products-table .th-dual-en { font-size: 9px; font-weight: 400; font-style: italic; color: #c8e6f5; }

        .products-table tbody tr {
            border-bottom: 1px solid #ccc;
        }
        .products-table tbody tr:last-child {
            border-bottom: 1.5px solid #1B4F72;
        }
        .products-table tbody td {
            padding: 6px 6px;
            font-size: 11px;
            border-left: 1px solid #d0d0d0;
            text-align: center;
            color: #000;
        }
        .products-table tbody td.td-desc {
            text-align: right;
            font-weight: 500;
        }
        .products-table tbody td.td-num {
            direction: ltr;
            font-weight: 600;
        }
        /* Stamp / logo column */
        .td-stamp {
            vertical-align: middle;
        }

        /* Row height for empty rows */
        .products-table tbody tr.empty-row td {
            height: 24px;
        }

        /* ===== TOTALS ROW ===== */
        .totals-bar {
            width: calc(100% - 32px);
            margin: 0 16px;
            border: 1.5px solid #1B4F72;
            border-top: none;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            direction: rtl;
        }
        .totals-right {
            padding: 7px 12px;
            font-size: 13px;
            font-weight: 900;
            color: #e74c3c;
            text-align: right;
        }
        .totals-center {
            border-left: 1px solid #1B4F72;
            border-right: 1px solid #1B4F72;
            padding: 7px 20px;
            font-size: 13px;
            font-weight: 900;
            color: #e74c3c;
            text-align: center;
        }
        .totals-left {
            padding: 7px 12px;
            font-size: 14px;
            font-weight: 900;
            color: #e74c3c;
            text-align: left;
            direction: ltr;
        }
        /* Extra totals (discount/tax/paid/remaining) */
        .extra-totals {
            width: calc(100% - 32px);
            margin: 0 16px;
            border: 1.5px solid #1B4F72;
            border-top: none;
            direction: rtl;
        }
        .extra-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 12px;
            font-size: 11px;
            border-bottom: 1px solid #e0e0e0;
        }
        .extra-row:last-child { border-bottom: none; }
        .extra-row.paid-row { background: #eafaf1; color: #1a7a40; font-weight: 700; }
        .extra-row.remaining-row { background: #fef9e7; color: #b7770d; font-weight: 700; }

        /* ===== NOTES ===== */
        .notes-section {
            width: calc(100% - 32px);
            margin: 8px 16px 4px;
            padding: 6px 10px;
            border: 1px solid #c0c0c0;
            border-radius: 4px;
            font-size: 10px;
            color: #333;
            direction: rtl;
        }
        .notes-section strong { color: #1B4F72; }

        /* ===== PAYMENTS TABLE ===== */
        .payments-section {
            width: calc(100% - 32px);
            margin: 6px 16px 4px;
            direction: rtl;
        }
        .payments-section h4 {
            font-size: 11px;
            font-weight: 700;
            color: #1B4F72;
            margin-bottom: 4px;
        }
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .payments-table th {
            background: #1B4F72;
            color: #fff;
            padding: 4px 6px;
            border: 1px solid #2980b9;
            text-align: center;
        }
        .payments-table td {
            padding: 3px 6px;
            border: 1px solid #d0d0d0;
            text-align: center;
        }

        /* ===== FOOTER STRIP ===== */
        .footer-strip {
            margin-top: auto;
            background: #c0392b;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 5px 14px;
            font-size: 10px;
            direction: rtl;
            gap: 6px;
            flex-wrap: wrap;
        }
        .footer-strip .f-item {
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }
        .footer-strip .f-icon {
            font-size: 11px;
        }
        .footer-strip .f-hotline {
            background: #1B4F72;
            color: #fff;
            padding: 3px 10px;
            border-radius: 4px;
            font-weight: 900;
            font-size: 13px;
            letter-spacing: 1px;
        }

        /* ===== NO-PRINT CONTROLS ===== */
        .no-print {
            background: #f4f7f8;
            padding: 12px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }
        .no-print a {
            color: #1B4F72;
            text-decoration: none;
            font-size: 14px;
            font-family: 'Tajawal', sans-serif;
        }
        .no-print button {
            background: #1B4F72;
            color: #fff;
            border: none;
            padding: 8px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Tajawal', sans-serif;
            cursor: pointer;
            font-weight: 600;
        }

        /* ===== PRINT ===== */
        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
            .page { width: 210mm; min-height: 297mm; }
            @page { size: A4; margin: 0; }
        }
    </style>
</head>
<body>

{{-- زر الطباعة --}}
<div class="no-print">
    <a href="{{ route('invoices.show', $invoice) }}">← رجوع للفاتورة</a>
    <button onclick="window.print()">🖨️ طباعة</button>
</div>

<div class="page">

    {{-- ══════════════════════════════════
         HEADER الرئيسي
    ══════════════════════════════════ --}}
    <div class="header-main">
        {{-- يمين: الاسم الإنجليزي --}}
        <div class="header-en">
            <div class="company-name-en">TOTAL TOOLS FOR</div>
            <div class="company-name-en">WORKSHOP EQUIPMENT</div>
        </div>

        {{-- وسط: الشعار --}}
        <div class="header-logo">
            <div class="logo-box">
                <div>
                    <div class="logo-total"><span>T</span>OTAL</div>
                    <div class="logo-sudan">SUDAN</div>
                </div>
            </div>
            <div class="logo-tagline">One Stop's Station</div>
        </div>

        {{-- يسار: الاسم العربي --}}
        <div class="header-ar">
            <div class="company-name-ar">توتال لمعدات الورش</div>
            <div class="company-sub-ar">الكنلة سوق اللفة</div>
        </div>
    </div>

    {{-- ══════════════════════════════════
         شريط الضريبة / Invoice
    ══════════════════════════════════ --}}
    <div class="subheader">
        {{-- يمين: الرقم الضريبي --}}
        <div class="tax-box">
            <span class="tax-label">الرقم الضريبي</span>
            <div class="tax-number-fields">
                @foreach(range(1,9) as $i)
                    <div class="tax-cell"></div>
                @endforeach
            </div>
        </div>

        {{-- وسط: كلمة Invoice --}}
        <div class="invoice-word">Invoice</div>

        {{-- يسار: العنوان --}}
        <div class="address-strip">الكلاكلة اللفة جوار بنك الخرطوم</div>
    </div>

    <div class="red-bar"></div>

    {{-- ══════════════════════════════════
         التاريخ والسيد
    ══════════════════════════════════ --}}
    <div class="meta-section">
        <div class="meta-row-dual">
            {{-- التاريخ عربي --}}
            <div class="meta-half" style="direction:rtl; justify-content:flex-end;">
                <span class="meta-label">التاريخ :</span>
                <span class="meta-dots"></span>
                <span class="meta-value">{{ $invoice->created_at->format('Y/m/d') }}</span>
            </div>
            {{-- Date إنجليزي --}}
            <div class="meta-half" style="direction:ltr; justify-content:flex-start;">
                <span class="meta-label-en">Date :</span>
                <span class="meta-dots"></span>
                <span class="meta-value" style="direction:ltr;">{{ $invoice->created_at->format('d/m/Y') }}</span>
            </div>
        </div>

        {{-- السيد --}}
        <div class="meta-row">
            <span class="meta-label">السيد :</span>
            <span class="meta-dots"></span>
            <span class="meta-value">
                @if($invoice->customer)
                    {{ $invoice->customer->name }}
                    @if($invoice->customer->phone) — {{ $invoice->customer->phone }}@endif
                @else
                    عميل نقدي
                @endif
            </span>
            <span style="width:40px; direction:ltr; font-size:11px; font-weight:700; color:#1B4F72;">M R :</span>
            <span class="meta-dots"></span>
        </div>
    </div>

    {{-- ══════════════════════════════════
         جدول المنتجات
    ══════════════════════════════════ --}}
    @php
        $items      = $invoice->items;
        $itemCount  = $items->count();
        $minRows    = 15;
        $emptyRows  = max(0, $minRows - $itemCount);
        $hasStamp   = false; // سيظهر الختم في الصف الأخير من الفراغ
    @endphp

    <table class="products-table">
        <thead>
            <tr>
                {{-- Cod No --}}
                <th style="width:42px;">
                    <div class="th-dual-ar">Cod</div>
                    <div class="th-dual-en">No</div>
                </th>
                {{-- البيان --}}
                <th>
                    <div class="th-dual-ar">البيـــان</div>
                </th>
                {{-- Description --}}
                <th style="width:110px;">
                    <div class="th-dual-en" style="color:#fff; font-size:11px; font-style:italic;">Decription</div>
                </th>
                {{-- العدد --}}
                <th style="width:50px;">
                    <div class="th-dual-ar">العدد</div>
                    <div class="th-dual-en">Qty</div>
                </th>
                {{-- سعر الوحدة --}}
                <th style="width:70px;">
                    <div class="th-dual-ar">سعر الوحدة</div>
                    <div class="th-dual-en">Unit Price</div>
                </th>
                {{-- سعر إجمالي --}}
                <th style="width:80px;">
                    <div class="th-dual-ar">سعر اجمالي</div>
                    <div class="th-dual-en">Total Price</div>
                </th>
            </tr>
        </thead>
        <tbody>
            {{-- صفوف البيانات --}}
            @foreach($items as $i => $item)
            <tr>
                <td>{{ $item->product?->sku ?? ($i + 1) }}</td>
                <td class="td-desc">{{ $item->product?->name_ar ?? $item->product?->name_en ?? '—' }}</td>
                <td class="td-desc" style="direction:ltr; text-align:left; font-size:10px;">{{ $item->product?->name_en ?? '' }}</td>
                <td class="td-num">{{ number_format($item->quantity) }}</td>
                <td class="td-num">{{ number_format($item->price, 2) }}</td>
                <td class="td-num" style="font-weight:700;">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach

            {{-- صفوف فارغة --}}
            @for($r = 0; $r < $emptyRows; $r++)
            <tr class="empty-row">
                @if($r === intval($emptyRows / 2))
                {{-- صف الختم في المنتصف --}}
                <td></td>
                <td></td>
                <td class="td-stamp" rowspan="1" style="text-align:center; padding:4px;">
                    <div style="
                        border: 2px solid #e74c3c;
                        border-radius: 6px;
                        padding: 4px 10px;
                        display: inline-block;
                        background: linear-gradient(135deg, #e8f4fd 0%, #fef9f9 100%);
                        position: relative;
                    ">
                        <div style="font-size:18px; font-weight:900; color:#c0392b; font-style:italic; letter-spacing:-1px;">توتال</div>
                        <div style="font-size:8px; font-weight:700; color:#1B4F72; letter-spacing:1px;">ممنتج قطاعي</div>
                    </div>
                </td>
                <td></td>
                <td></td>
                <td></td>
                @else
                <td></td><td></td><td></td><td></td><td></td><td></td>
                @endif
            </tr>
            @endfor
        </tbody>
    </table>

    {{-- ══════════════════════════════════
         شريط الإجماليات
    ══════════════════════════════════ --}}
    <div class="totals-bar">
        <div class="totals-right">إجمالي</div>
        <div class="totals-center">Total</div>
        <div class="totals-left">{{ number_format($invoice->total, 2) }} SDG</div>
    </div>

    {{-- خصم / ضريبة / مدفوع / متبقي --}}
    @if($invoice->discount_amount > 0 || $invoice->tax_amount > 0 || $invoice->paid_amount > 0 || $invoice->remaining_amount > 0)
    <div class="extra-totals">
        @if($invoice->discount_amount > 0)
        <div class="extra-row">
            <span>الخصم @if($invoice->discount_percent > 0)({{ $invoice->discount_percent }}%)@endif</span>
            <span style="color:#c62828; font-weight:700;">- {{ number_format($invoice->discount_amount, 2) }} SDG</span>
        </div>
        @endif
        @if($invoice->tax_amount > 0)
        <div class="extra-row">
            <span>الضريبة ({{ $invoice->tax_percent }}%)</span>
            <span style="color:#1565c0; font-weight:700;">{{ number_format($invoice->tax_amount, 2) }} SDG</span>
        </div>
        @endif
        @if($invoice->paid_amount > 0)
        <div class="extra-row paid-row">
            <span>المدفوع</span>
            <span>{{ number_format($invoice->paid_amount, 2) }} SDG</span>
        </div>
        @endif
        @if($invoice->remaining_amount > 0)
        <div class="extra-row remaining-row">
            <span>المتبقي</span>
            <span>{{ number_format($invoice->remaining_amount, 2) }} SDG</span>
        </div>
        @endif
    </div>
    @endif

    {{-- ══════════════════════════════════
         سجل الدفعات
    ══════════════════════════════════ --}}
    @if($invoice->payments->count())
    <div class="payments-section">
        <h4>سجل الدفعات</h4>
        <table class="payments-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>التاريخ</th>
                    <th>طريقة الدفع</th>
                    <th>ملاحظات</th>
                    <th>المبلغ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->payments as $i => $pay)
                @php $methods = ['cash'=>'نقدي','bank'=>'بنك','other'=>'أخرى']; @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->format('Y/m/d') : $pay->created_at->format('Y/m/d') }}</td>
                    <td>{{ $methods[$pay->method] ?? $pay->method }}</td>
                    <td>{{ $pay->notes ?? '—' }}</td>
                    <td style="font-weight:700; color:#1a7a40; direction:ltr;">{{ number_format($pay->amount, 2) }} SDG</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ملاحظات --}}
    @if($invoice->notes)
    <div class="notes-section">
        <strong>ملاحظات: </strong>{{ $invoice->notes }}
    </div>
    @endif

    {{-- ══════════════════════════════════
         FOOTER الأحمر
    ══════════════════════════════════ --}}
    <div class="footer-strip">
        <div class="f-item">
            <span class="f-hotline">3503</span>
            <span style="font-size:9px;">خط العميل</span>
        </div>
        <div class="f-item">
            <span class="f-icon">📘</span>
            <span class="f-icon">🐦</span>
            <span class="f-icon">▶️</span>
            <span class="f-icon">📷</span>
            <span class="f-icon">🎵</span>
            <span>Totalsd</span>
        </div>
        <div class="f-item">
            <span class="f-icon">📱</span>
            <span>0128282828</span>
        </div>
        <div class="f-item">
            <span class="f-icon">🌐</span>
            <span>WWW.TOTALSUDA.COM</span>
        </div>
        <div class="f-item">
            <span class="f-icon">✉️</span>
            <span>info@totalsudan.com</span>
        </div>
    </div>

</div>
</body>
</html>
