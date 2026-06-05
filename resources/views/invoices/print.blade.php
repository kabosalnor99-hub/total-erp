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

        /* ═══════════════════════════════════
           PAGE — A4
        ═══════════════════════════════════ */
        .page {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            background: #fff;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ═══════════════════════════════════
           HEADER الأزرق
        ═══════════════════════════════════ */
        .header-main {
            background: #1B4F72;
            display: grid;
            grid-template-columns: 1fr 94px 1fr;
            align-items: center;
            padding: 7px 14px;
            gap: 8px;
            flex-shrink: 0;
        }

        /* يسار: إنجليزي */
        .header-en {
            color: #fff;
            text-align: left;
            direction: ltr;
        }
        .header-en .en-line1 {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 0.3px;
            line-height: 1.3;
        }
        .header-en .en-line2 {
            font-size: 12px;
            font-weight: 500;
            color: #a8d4e6;
            line-height: 1.3;
        }

        /* وسط: الشعار الدائري */
        .header-logo {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid #a8d4e6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 6px 4px 4px;
            gap: 2px;
        }
        .logo-brand-row {
            display: flex;
            align-items: baseline;
            gap: 3px;
            line-height: 1;
        }
        .logo-total-text {
            font-size: 26px;
            font-weight: 900;
            font-style: italic;
            color: #1B4F72;
            letter-spacing: -1px;
            line-height: 1;
        }
        .logo-total-text .t-red { color: #c0392b; }
        .logo-sudan-text {
            font-size: 10px;
            font-weight: 800;
            color: #1B4F72;
            letter-spacing: 0.5px;
            line-height: 1;
            align-self: flex-end;
            margin-bottom: 3px;
        }
        .logo-tagline {
            font-size: 7.5px;
            font-weight: 600;
            color: #1B4F72;
            letter-spacing: 0.3px;
            text-align: center;
            line-height: 1;
        }

        /* يمين: عربي */
        .header-ar {
            color: #fff;
            text-align: right;
            direction: rtl;
        }
        .header-ar .ar-line1 {
            font-size: 20px;
            font-weight: 900;
            line-height: 1.3;
        }
        .header-ar .ar-line2 {
            font-size: 11px;
            color: #a8d4e6;
            line-height: 1.3;
        }

        /* ═══════════════════════════════════
           SUBHEADER
        ═══════════════════════════════════ */
        .subheader {
            background: #fff;
            border-bottom: 2px solid #1B4F72;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 4px 14px;
            flex-shrink: 0;
        }
        .tax-box {
            display: flex;
            align-items: center;
            gap: 6px;
            direction: rtl;
            justify-content: flex-start;
        }
        .tax-label {
            font-size: 10px;
            font-weight: 700;
            color: #c0392b;
            white-space: nowrap;
        }
        .tax-cells {
            display: flex;
            gap: 2px;
            direction: ltr;
        }
        .tax-cell {
            width: 16px;
            height: 18px;
            border: 1px solid #c0392b;
            font-size: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .invoice-word {
            font-size: 17px;
            font-weight: 900;
            color: #c0392b;
            font-style: italic;
            text-align: center;
        }
        .address-strip {
            font-size: 10px;
            font-weight: 700;
            color: #c0392b;
            text-align: left;
            direction: rtl;
        }

        /* ═══════════════════════════════════
           الشريط الأحمر الفاصل
        ═══════════════════════════════════ */
        .red-bar {
            background: #c0392b;
            height: 5px;
            flex-shrink: 0;
        }

        /* ═══════════════════════════════════
           META — التاريخ والسيد
        ═══════════════════════════════════ */
        .meta-section {
            padding: 7px 14px 4px;
            direction: rtl;
            flex-shrink: 0;
        }
        .meta-date-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 5px;
        }
        .meta-date-ar {
            display: flex;
            align-items: center;
            gap: 6px;
            direction: rtl;
        }
        .meta-date-en {
            display: flex;
            align-items: center;
            gap: 6px;
            direction: ltr;
        }
        .meta-lbl {
            font-size: 12px;
            font-weight: 700;
            color: #1B4F72;
            white-space: nowrap;
        }
        .meta-val {
            font-size: 12px;
            font-weight: 600;
            color: #000;
            white-space: nowrap;
        }
        .meta-line {
            flex: 1;
            height: 0;
            border-bottom: 1px solid #aaa;
        }
        .meta-mr-row {
            display: flex;
            align-items: center;
            gap: 6px;
            direction: rtl;
        }
        .meta-mr-lbl-ar {
            font-size: 12px;
            font-weight: 700;
            color: #1B4F72;
            white-space: nowrap;
        }
        .meta-mr-lbl-en {
            font-size: 11px;
            font-weight: 700;
            color: #1B4F72;
            direction: ltr;
            white-space: nowrap;
        }
        .meta-mr-val {
            font-size: 12px;
            font-weight: 600;
            color: #000;
        }

        /* ═══════════════════════════════════
           حاوية الجدول + الإجمالي — إطار واحد
           لا فجوة بين الجدول وشريط الإجمالي
        ═══════════════════════════════════ */
        .table-section {
            flex: 1;
            margin: 4px 14px 0;
            border: 1.5px solid #1B4F72;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* الجدول */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            direction: rtl;
            table-layout: fixed;
            flex-shrink: 0;
        }

        /* رأس الجدول */
        .products-table thead tr {
            background: #1B4F72;
        }
        .products-table thead th {
            color: #fff;
            padding: 5px 4px;
            border-right: 1px solid #2980b9;
            border-left: 1px solid #2980b9;
            text-align: center;
            vertical-align: middle;
        }
        .products-table thead th:first-child { border-right: none; }
        .products-table thead th:last-child  { border-left: none; }
        .th-ar { font-size: 11px; font-weight: 700; display: block; }
        .th-en { font-size: 9px;  font-weight: 400; font-style: italic; color: #c8e6f5; display: block; }

        /* صفوف البيانات */
        .products-table tbody td {
            padding: 3px 5px;
            font-size: 11px;
            border-right: 1px solid #7fb3c8;
            border-bottom: 1px solid #7fb3c8;
            text-align: center;
            color: #000;
            vertical-align: middle;
            height: 21px;
        }
        .products-table tbody td:first-child { border-right: none; }
        .td-desc { text-align: right; font-weight: 500; }
        .td-num  { direction: ltr; font-weight: 600; }

        /* ═══════════════════════════════════
           SPACER — يملأ الفراغ بخطوط مطابقة
           لارتفاع الصفوف (21px بيانات + 1px border)
        ═══════════════════════════════════ */
        .table-spacer {
            flex: 1;
            border-top: 1px solid #7fb3c8;
            background: repeating-linear-gradient(
                to bottom,
                #fff      0px,
                #fff      21px,
                #7fb3c8   21px,
                #7fb3c8   22px
            );
        }

        /* ═══════════════════════════════════
           شريط الإجمالي — داخل table-section
           ملاصق تماماً لآخر صف
        ═══════════════════════════════════ */
        .totals-bar {
            border-top: 1.5px solid #1B4F72;
            display: grid;
            grid-template-columns: 1fr 1.5px 1fr;
            direction: rtl;
            flex-shrink: 0;
        }
        .totals-right {
            padding: 6px 14px;
            font-size: 14px;
            font-weight: 900;
            color: #c0392b;
            text-align: right;
        }
        .totals-divider {
            background: #1B4F72;
        }
        .totals-left {
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 900;
            color: #c0392b;
            text-align: left;
            direction: ltr;
        }

        /* ═══════════════════════════════════
           خصم / ضريبة / مدفوع / متبقي
        ═══════════════════════════════════ */
        .extra-totals {
            margin: 0 14px;
            border: 1.5px solid #1B4F72;
            border-top: none;
            direction: rtl;
            flex-shrink: 0;
        }
        .extra-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 12px;
            font-size: 11px;
            border-bottom: 1px solid #e0e0e0;
        }
        .extra-row:last-child { border-bottom: none; }
        .extra-row.paid-row      { background: #eafaf1; color: #1a7a40; font-weight: 700; }
        .extra-row.remaining-row { background: #fef9e7; color: #b7770d; font-weight: 700; }

        /* ═══════════════════════════════════
           ملاحظات
        ═══════════════════════════════════ */
        .notes-section {
            margin: 6px 14px 0;
            padding: 5px 10px;
            border: 1px solid #c0c0c0;
            border-radius: 4px;
            font-size: 10px;
            color: #333;
            direction: rtl;
            flex-shrink: 0;
        }
        .notes-section strong { color: #1B4F72; }

        /* ═══════════════════════════════════
           سجل الدفعات
        ═══════════════════════════════════ */
        .payments-section {
            margin: 6px 14px 0;
            direction: rtl;
            flex-shrink: 0;
        }
        .payments-section h4 {
            font-size: 11px;
            font-weight: 700;
            color: #1B4F72;
            margin-bottom: 3px;
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
            border: 1px solid #2471a3;
            text-align: center;
        }
        .payments-table td {
            padding: 3px 6px;
            border: 1px solid #d0d0d0;
            text-align: center;
        }

        /* ═══════════════════════════════════
           FOOTER الأحمر — ملاصق للمحتوى
        ═══════════════════════════════════ */
        .footer-strip {
            background: #c0392b;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 5px 12px;
            font-size: 10px;
            direction: rtl;
            gap: 4px;
            flex-wrap: nowrap;
            flex-shrink: 0;
            margin-top: auto;
        }
        .f-item {
            display: flex;
            align-items: center;
            gap: 3px;
            white-space: nowrap;
        }
        .f-hotline {
            background: #1B4F72;
            color: #fff;
            padding: 2px 8px;
            border-radius: 3px;
            font-weight: 900;
            font-size: 13px;
            letter-spacing: 1px;
        }
        .f-icon { font-size: 10px; }

        /* ═══════════════════════════════════
           NO-PRINT زر الطباعة
        ═══════════════════════════════════ */
        .no-print {
            background: #f4f7f8;
            padding: 10px 28px;
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

        /* ═══════════════════════════════════
           PRINT — A4 بدون هوامش
        ═══════════════════════════════════ */
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .no-print { display: none !important; }
            .page { width: 210mm; height: 297mm; margin: 0; }
            @page { size: A4; margin: 0; }
        }
    </style>
</head>
<body>

{{-- زر الطباعة (لا يظهر عند الطباعة) --}}
<div class="no-print">
    <a href="{{ route('invoices.show', $invoice) }}">← رجوع للفاتورة</a>
    <button onclick="window.print()">🖨️ طباعة</button>
</div>

<div class="page">

    {{-- ══════════════════════════════════════
         HEADER الأزرق
    ══════════════════════════════════════ --}}
    <div class="header-main">

        {{-- يسار: إنجليزي --}}
        <div class="header-en">
            <div class="en-line1">TOTAL TOOLS FOR</div>
            <div class="en-line2">WORKSHOP EQUIPMENT</div>
        </div>

        {{-- وسط: الشعار الدائري --}}
        <div class="header-logo">
            <div class="logo-circle">
                <div class="logo-brand-row">
                    <span class="logo-total-text"><span class="t-red">T</span>OTAL</span>
                    <span class="logo-sudan-text">SUDAN</span>
                </div>
                <div class="logo-tagline">One Stop's Station</div>
            </div>
        </div>

        {{-- يمين: عربي --}}
        <div class="header-ar">
            <div class="ar-line1">توتال لمعدات الورش</div>
            <div class="ar-line2">الكنلة سوق اللفة</div>
        </div>

    </div>

    {{-- ══════════════════════════════════════
         SUBHEADER — ضريبة / Invoice / عنوان
    ══════════════════════════════════════ --}}
    <div class="subheader">
        {{-- يمين: الرقم الضريبي --}}
        <div class="tax-box">
            <span class="tax-label">الرقم الضريبي</span>
            <div class="tax-cells">
                @foreach(range(1,9) as $n)
                    <div class="tax-cell"></div>
                @endforeach
            </div>
        </div>

        {{-- وسط --}}
        <div class="invoice-word">Invoice</div>

        {{-- يسار: العنوان --}}
        <div class="address-strip">الكلاكلة اللفة جوار بنك الخرطوم</div>
    </div>

    <div class="red-bar"></div>

    {{-- ══════════════════════════════════════
         META — التاريخ والسيد
    ══════════════════════════════════════ --}}
    <div class="meta-section">

        <div class="meta-date-row">

            <div class="meta-date-ar">
                <span class="meta-lbl">التاريخ :</span>
                <span class="meta-val">{{ $invoice->created_at->format('Y/m/d') }}</span>
                <span class="meta-line"></span>
            </div>

            <div class="meta-date-en">
                <span class="meta-lbl" style="direction:ltr;">Date :</span>
                <span class="meta-val" style="direction:ltr;">{{ $invoice->created_at->format('d/m/Y') }}</span>
                <span class="meta-line"></span>
            </div>

        </div>

        <div class="meta-mr-row">
            <span class="meta-mr-lbl-ar">السيد :</span>
            <span class="meta-mr-val">
                @if($invoice->customer)
                    {{ $invoice->customer->name }}@if($invoice->customer->phone) — {{ $invoice->customer->phone }}@endif
                @else
                    عميل نقدي
                @endif
            </span>
            <span class="meta-line"></span>
            <span class="meta-mr-lbl-en">M R :</span>
            <span class="meta-line"></span>
        </div>

    </div>

    {{-- ══════════════════════════════════════
         جدول المنتجات + الإجمالي في حاوية واحدة
         (بدون أي فجوة — spacer يملأ الفراغ بخطوط)
    ══════════════════════════════════════ --}}
    <div class="table-section">

        <table class="products-table">
            <thead>
                <tr>
                    <th style="width:42px;">
                        <span class="th-ar">Cod</span>
                        <span class="th-en">No</span>
                    </th>
                    <th>
                        <span class="th-ar">البيـــان</span>
                    </th>
                    <th style="width:100px;">
                        <span class="th-en" style="color:#fff; font-size:11px;">Decription</span>
                    </th>
                    <th style="width:46px;">
                        <span class="th-ar">العدد</span>
                        <span class="th-en">Qty</span>
                    </th>
                    <th style="width:70px;">
                        <span class="th-ar">سعر الوحدة</span>
                        <span class="th-en">Unit Price</span>
                    </th>
                    <th style="width:78px;">
                        <span class="th-ar">سعر اجمالي</span>
                        <span class="th-en">Total Price</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $i => $item)
                <tr>
                    <td>{{ $item->product?->sku ?? ($i + 1) }}</td>
                    <td class="td-desc">{{ $item->product?->name_ar ?? $item->product?->name_en ?? '—' }}</td>
                    <td class="td-desc" style="direction:ltr; text-align:left; font-size:10px;">{{ $item->product?->name_en ?? '' }}</td>
                    <td class="td-num">{{ number_format($item->quantity) }}</td>
                    <td class="td-num">{{ number_format($item->price, 2) }}</td>
                    <td class="td-num" style="font-weight:700;">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- spacer يملأ الفراغ المتبقي بخطوط أفقية مطابقة لصفوف الجدول --}}
        <div class="table-spacer"></div>

        {{-- شريط الإجمالي ملاصق دائماً لآخر خط --}}
        <div class="totals-bar">
            <div class="totals-right">إجمالي</div>
            <div class="totals-divider"></div>
            <div class="totals-left">Total &nbsp;&nbsp; {{ number_format($invoice->total, 2) }} SDG</div>
        </div>

    </div>{{-- end table-section --}}

    {{-- خصم / ضريبة / مدفوع / متبقي --}}
    @if($invoice->discount_amount > 0 || $invoice->tax_amount > 0 || $invoice->paid_amount > 0 || $invoice->remaining_amount > 0)
    <div class="extra-totals">
        @if($invoice->discount_amount > 0)
        <div class="extra-row">
            <span>الخصم@if($invoice->discount_percent > 0) ({{ $invoice->discount_percent }}%)@endif</span>
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
            <span>المبلغ المسدَّد</span>
            <span>{{ number_format($invoice->paid_amount, 2) }} SDG</span>
        </div>
        @endif
        @if($invoice->remaining_amount > 0)
        <div class="extra-row remaining-row">
            <span>الرصيد المتبقي</span>
            <span>{{ number_format($invoice->remaining_amount, 2) }} SDG</span>
        </div>
        @endif
    </div>
    @endif

    {{-- سجل الدفعات --}}
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

    {{-- ══════════════════════════════════════
         FOOTER الأحمر — ملاصق للمحتوى
    ══════════════════════════════════════ --}}
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

</div>{{-- end .page --}}
</body>
</html>
