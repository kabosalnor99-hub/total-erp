{{-- المسار: resources/views/pdf/invoice.blade.php --}}
{{-- ✅ محسَّن بالكامل لـ DomPDF — يعتمد على HTML tables فقط --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة — {{ $invoice->invoice_number }}</title>
    <style>
        /* ── Reset ─────────────────────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #1a2e35;
            background: #fff;
            direction: rtl;
        }

        .page {
            padding: 22px 28px;
            width: 100%;
        }

        /* ── Header ─────────────────────────────────────────────── */
        .header-table {
            width: 100%;
            border-bottom: 3px solid #00838F;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .logo-circle {
            width: 50px;
            height: 50px;
            background: #00838F;
            border-radius: 25px;
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            text-align: center;
            vertical-align: middle;
            padding-top: 11px;
            display: inline-block;
        }

        .company-name {
            font-size: 17px;
            font-weight: 700;
            color: #00838F;
        }

        .company-sub {
            font-size: 10px;
            color: #6b8c94;
            margin-top: 2px;
        }

        .inv-title {
            font-size: 17px;
            font-weight: 700;
            color: #1a2e35;
            text-align: left;
        }

        .inv-number {
            font-size: 13px;
            color: #00838F;
            font-weight: 600;
            margin-top: 3px;
            text-align: left;
        }

        .inv-date {
            font-size: 10px;
            color: #6b8c94;
            margin-top: 2px;
            text-align: left;
        }

        /* ── Badge ──────────────────────────────────────────────── */
        .badge {
            padding: 2px 9px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
        }

        .badge-paid      { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .badge-confirmed { background: #e3f2fd; color: #1565c0; border: 1px solid #90caf9; }
        .badge-partial   { background: #fff8e1; color: #f57f17; border: 1px solid #ffe082; }
        .badge-draft     { background: #f5f5f5; color: #616161; border: 1px solid #e0e0e0; }
        .badge-cancelled { background: #fce4ec; color: #c62828; border: 1px solid #ef9a9a; }

        /* ── Info Boxes ─────────────────────────────────────────── */
        .info-outer {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 6px 0;
        }

        .info-box {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 11px 12px;
            width: 50%;
            vertical-align: top;
        }

        .info-box-title {
            font-size: 9px;
            font-weight: 700;
            color: #00838F;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .info-row-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }

        .info-label {
            font-size: 10px;
            color: #6b8c94;
            width: 42%;
            padding: 1px 0;
        }

        .info-value {
            font-size: 11px;
            color: #1a2e35;
            font-weight: 500;
            padding: 1px 0;
        }

        /* ── Products Table ─────────────────────────────────────── */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .products-table thead tr {
            background: #00838F;
            color: #fff;
        }

        .products-table thead th {
            padding: 8px 10px;
            font-size: 11px;
            font-weight: 600;
            text-align: right;
        }

        .products-table thead th.center {
            text-align: center;
        }

        .products-table tbody tr {
            border-bottom: 1px solid #f0f0f0;
        }

        .products-table tbody tr.even {
            background: #f9fafb;
        }

        .products-table tbody td {
            padding: 8px 10px;
            font-size: 11px;
            vertical-align: middle;
        }

        .products-table tbody td.center {
            text-align: center;
        }

        .products-table tbody td.ltr {
            direction: ltr;
            text-align: right;
        }

        .product-sku {
            font-size: 9px;
            color: #9e9e9e;
            margin-top: 2px;
        }

        /* ── Totals ─────────────────────────────────────────────── */
        .totals-outer {
            width: 100%;
            margin-bottom: 16px;
        }

        .totals-inner {
            width: 290px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            border-collapse: collapse;
            overflow: hidden;
            float: left;
        }

        .totals-row td {
            padding: 7px 12px;
            font-size: 11px;
            border-bottom: 1px solid #f0f0f0;
        }

        .totals-row td.t-label {
            color: #6b8c94;
        }

        .totals-row td.t-value {
            text-align: left;
            font-weight: 500;
            direction: ltr;
        }

        .totals-row.grand td {
            background: #00838F;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            border-bottom: none;
        }

        .totals-row.paid-row td {
            background: #e8f5e9;
            color: #2e7d32;
            font-weight: 600;
        }

        .totals-row.rem-row td {
            background: #fff3e0;
            color: #e65100;
            font-weight: 600;
        }

        .totals-row.disc-row td {
            color: #c62828;
        }

        .totals-row.tax-row td {
            color: #1565c0;
        }

        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        /* ── Payments Table ─────────────────────────────────────── */
        .pay-title {
            font-size: 11px;
            font-weight: 700;
            color: #1a2e35;
            margin-bottom: 7px;
        }

        .pay-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .pay-table thead tr {
            background: #005F6B;
            color: #fff;
        }

        .pay-table thead th {
            padding: 6px 10px;
            font-size: 10px;
            font-weight: 600;
            text-align: right;
        }

        .pay-table tbody td {
            padding: 6px 10px;
            font-size: 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .pay-table tbody tr.even td {
            background: #f9fafb;
        }

        /* ── Notes ──────────────────────────────────────────────── */
        .notes-box {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 9px 12px;
            margin-bottom: 16px;
        }

        .notes-title {
            font-size: 10px;
            font-weight: 700;
            color: #6b8c94;
            margin-bottom: 4px;
        }

        .notes-text {
            font-size: 11px;
            color: #1a2e35;
        }

        /* ── Footer ─────────────────────────────────────────────── */
        .footer {
            border-top: 2px solid #00838F;
            padding-top: 12px;
            text-align: center;
        }

        .footer-thanks {
            font-size: 12px;
            font-weight: 700;
            color: #00838F;
            margin-bottom: 3px;
        }

        .footer-contact {
            font-size: 10px;
            color: #6b8c94;
            margin-bottom: 2px;
        }

        .footer-small {
            font-size: 9px;
            color: #bdbdbd;
            margin-top: 5px;
        }

        /* ── Divider ────────────────────────────────────────────── */
        .spacer { height: 6px; }
    </style>
</head>
<body>
<div class="page">

    {{-- ══ HEADER ══════════════════════════════════════════════ --}}
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            {{-- شعار + اسم الشركة --}}
            <td style="width:60%; vertical-align:middle;">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="vertical-align:middle; padding-left:10px;">
                            <div class="logo-circle">T</div>
                        </td>
                        <td style="vertical-align:middle;">
                            <div class="company-name">توتال الكلاكلة</div>
                            <div class="company-sub">تجارة وتوزيع أدوات كهربائية ومعدات</div>
                            <div class="company-sub">الخرطوم، السودان</div>
                        </td>
                    </tr>
                </table>
            </td>
            {{-- عنوان الفاتورة --}}
            <td style="width:40%; vertical-align:middle; text-align:left;">
                <div class="inv-title">فاتورة مبيعات</div>
                <div class="inv-number">{{ $invoice->invoice_number }}</div>
                <div class="inv-date">{{ $invoice->created_at->format('Y/m/d') }}</div>
                @php
                    $bmap = ['paid'=>'paid','confirmed'=>'confirmed','partial'=>'partial','draft'=>'draft','cancelled'=>'cancelled'];
                @endphp
                <span class="badge badge-{{ $bmap[$invoice->status] ?? 'draft' }}">{{ $invoice->status_label }}</span>
            </td>
        </tr>
    </table>

    {{-- ══ INFO BOXES ═══════════════════════════════════════════ --}}
    <table class="info-outer" cellpadding="0" cellspacing="0">
        <tr>
            {{-- بيانات العميل --}}
            <td class="info-box">
                <div class="info-box-title">بيانات العميل</div>
                @if($invoice->customer)
                    <table class="info-row-table" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="info-label">الاسم</td>
                            <td class="info-value" style="font-weight:700;">{{ $invoice->customer->name }}</td>
                        </tr>
                        @if($invoice->customer->phone)
                        <tr>
                            <td class="info-label">الهاتف</td>
                            <td class="info-value">{{ $invoice->customer->phone }}</td>
                        </tr>
                        @endif
                        @if($invoice->customer->address)
                        <tr>
                            <td class="info-label">العنوان</td>
                            <td class="info-value">{{ $invoice->customer->address }}</td>
                        </tr>
                        @endif
                        @if($invoice->customer->tax_number)
                        <tr>
                            <td class="info-label">الرقم الضريبي</td>
                            <td class="info-value">{{ $invoice->customer->tax_number }}</td>
                        </tr>
                        @endif
                    </table>
                @else
                    <div class="info-value">عميل نقدي</div>
                @endif
            </td>

            <td style="width:6px;"></td>

            {{-- تفاصيل الفاتورة --}}
            <td class="info-box">
                <div class="info-box-title">تفاصيل الفاتورة</div>
                <table class="info-row-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="info-label">رقم الفاتورة</td>
                        <td class="info-value" style="color:#00838F;">{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">تاريخ الإصدار</td>
                        <td class="info-value">{{ $invoice->created_at->format('Y/m/d') }}</td>
                    </tr>
                    @if($invoice->due_date)
                    <tr>
                        <td class="info-label">تاريخ الاستحقاق</td>
                        <td class="info-value" style="{{ $invoice->is_overdue ? 'color:#c62828;' : '' }}">
                            {{ $invoice->due_date->format('Y/m/d') }}
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="info-label">نوع الدفع</td>
                        <td class="info-value">{{ $invoice->type_label }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">المستخدم</td>
                        <td class="info-value">{{ $invoice->user?->name }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ══ PRODUCTS TABLE ═══════════════════════════════════════ --}}
    @php $hasDiscount = $invoice->items->where('discount', '>', 0)->count() > 0; @endphp
    <table class="products-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width:28px;" class="center">#</th>
                <th>المنتج</th>
                <th style="width:65px;" class="center">الكمية</th>
                <th style="width:105px;">سعر الوحدة</th>
                @if($hasDiscount)
                <th style="width:80px;">الخصم</th>
                @endif
                <th style="width:105px;">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
            <tr class="{{ $i % 2 === 1 ? 'even' : '' }}">
                <td class="center">{{ $i + 1 }}</td>
                <td>
                    <div style="font-weight:500;">{{ $item->product?->name_ar ?? $item->product?->name_en ?? '—' }}</div>
                    @if($item->product?->sku)
                        <div class="product-sku">{{ $item->product->sku }}</div>
                    @endif
                </td>
                <td class="center">{{ number_format($item->quantity) }}</td>
                <td class="ltr">{{ number_format($item->price, 2) }}</td>
                @if($hasDiscount)
                <td class="ltr" style="color:#c62828;">
                    {{ $item->discount > 0 ? number_format($item->discount, 2) : '—' }}
                </td>
                @endif
                <td class="ltr" style="font-weight:600;">{{ number_format($item->total, 2) }} SDG</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ══ TOTALS ════════════════════════════════════════════════ --}}
    <div class="clearfix">
        <table class="totals-inner" cellpadding="0" cellspacing="0" style="float:left;">
            <tr class="totals-row">
                <td class="t-label">المجموع الفرعي</td>
                <td class="t-value">{{ number_format($invoice->subtotal, 2) }} SDG</td>
            </tr>
            @if($invoice->discount_amount > 0)
            <tr class="totals-row disc-row">
                <td class="t-label">الخصم @if($invoice->discount_percent > 0)({{ $invoice->discount_percent }}%)@endif</td>
                <td class="t-value">- {{ number_format($invoice->discount_amount, 2) }} SDG</td>
            </tr>
            @endif
            @if($invoice->tax_amount > 0)
            <tr class="totals-row tax-row">
                <td class="t-label">الضريبة ({{ $invoice->tax_percent }}%)</td>
                <td class="t-value">{{ number_format($invoice->tax_amount, 2) }} SDG</td>
            </tr>
            @endif
            <tr class="totals-row grand">
                <td class="t-label">الإجمالي النهائي</td>
                <td class="t-value">{{ number_format($invoice->total, 2) }} SDG</td>
            </tr>
            @if($invoice->paid_amount > 0)
            <tr class="totals-row paid-row">
                <td class="t-label">المدفوع</td>
                <td class="t-value">{{ number_format($invoice->paid_amount, 2) }} SDG</td>
            </tr>
            @endif
            @if($invoice->remaining_amount > 0)
            <tr class="totals-row rem-row">
                <td class="t-label">المتبقي</td>
                <td class="t-value">{{ number_format($invoice->remaining_amount, 2) }} SDG</td>
            </tr>
            @endif
        </table>
    </div>
    <div class="spacer" style="height:20px; clear:both;"></div>

    {{-- ══ PAYMENTS ══════════════════════════════════════════════ --}}
    @if($invoice->payments->count())
    <div class="pay-title">سجل الدفعات</div>
    <table class="pay-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width:28px; text-align:center;">#</th>
                <th>التاريخ</th>
                <th>طريقة الدفع</th>
                <th>ملاحظات</th>
                <th>المبلغ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->payments as $i => $pay)
            @php $methods = ['cash'=>'نقدي','bank'=>'بنك','other'=>'أخرى']; @endphp
            <tr class="{{ $i % 2 === 1 ? 'even' : '' }}">
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td>{{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->format('Y/m/d') : $pay->created_at->format('Y/m/d') }}</td>
                <td>{{ $methods[$pay->method] ?? $pay->method }}</td>
                <td>{{ $pay->notes ?? '—' }}</td>
                <td style="font-weight:600; color:#2e7d32; direction:ltr; text-align:right;">
                    {{ number_format($pay->amount, 2) }} SDG
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ══ NOTES ═════════════════════════════════════════════════ --}}
    @if($invoice->notes)
    <div class="notes-box">
        <div class="notes-title">ملاحظات</div>
        <div class="notes-text">{{ $invoice->notes }}</div>
    </div>
    @endif

    {{-- ══ FOOTER ════════════════════════════════════════════════ --}}
    <div class="footer">
        <div class="footer-thanks">شكراً لتعاملكم مع توتال الكلاكلة</div>
        <div class="footer-contact">الخرطوم، السودان • هاتف: 0900000000</div>
        <div class="footer-small">
            تم إصدار هذه الفاتورة إلكترونياً بواسطة نظام ERP توتال الكلاكلة — {{ now()->format('Y/m/d H:i') }}
        </div>
    </div>

</div>
</body>
</html>
