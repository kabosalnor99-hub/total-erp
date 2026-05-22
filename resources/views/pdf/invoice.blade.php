{{-- المسار الكامل: resources/views/pdf/invoice.blade.php --}}
{{-- المسار: resources/views/pdf/invoice.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة — {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 13px;
            color: #1a2e35;
            background: #fff;
            direction: rtl;
        }
        .page { padding: 25px 30px; }

        /* Header */
        .header { display: table; width: 100%; border-bottom: 3px solid #00838F; padding-bottom: 16px; margin-bottom: 20px; }
        .header-right { display: table-cell; width: 60%; vertical-align: middle; }
        .header-left  { display: table-cell; width: 40%; vertical-align: middle; text-align: left; }
        .logo-circle {
            display: inline-block; width: 52px; height: 52px;
            background: #00838F; border-radius: 50%;
            text-align: center; line-height: 52px;
            color: #fff; font-size: 22px; font-weight: 700;
            vertical-align: middle;
        }
        .company-text { display: inline-block; vertical-align: middle; margin-right: 10px; }
        .company-text h1 { font-size: 18px; font-weight: 700; color: #00838F; }
        .company-text p  { font-size: 11px; color: #6b8c94; margin-top: 2px; }
        .invoice-title { font-size: 18px; font-weight: 700; color: #1a2e35; }
        .invoice-number { font-size: 14px; color: #00838F; font-weight: 600; margin-top: 3px; }
        .invoice-date   { font-size: 11px; color: #6b8c94; margin-top: 3px; }

        /* Badge */
        .badge { display: inline-block; padding: 2px 9px; border-radius: 10px; font-size: 11px; font-weight: 600; margin-top: 4px; }
        .badge-paid      { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .badge-confirmed { background: #e3f2fd; color: #1565c0; border: 1px solid #90caf9; }
        .badge-partial   { background: #fff8e1; color: #f57f17; border: 1px solid #ffe082; }
        .badge-draft     { background: #f5f5f5; color: #616161; border: 1px solid #e0e0e0; }
        .badge-cancelled { background: #fce4ec; color: #c62828; border: 1px solid #ef9a9a; }

        /* Info grid */
        .info-table { width: 100%; margin-bottom: 18px; border-collapse: collapse; }
        .info-cell  { width: 50%; vertical-align: top; padding: 0 5px 0 0; }
        .info-box   { border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px; }
        .info-box h3 { font-size: 10px; font-weight: 700; color: #00838F; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.4px; }
        .info-row   { display: table; width: 100%; margin-bottom: 4px; }
        .info-label { display: table-cell; font-size: 11px; color: #6b8c94; width: 45%; }
        .info-value { display: table-cell; font-size: 12px; color: #1a2e35; font-weight: 500; }

        /* Products table */
        .products-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .products-table thead tr { background: #00838F; color: #fff; }
        .products-table thead th { padding: 9px 10px; font-size: 12px; font-weight: 600; text-align: right; }
        .products-table thead th.center { text-align: center; }
        .products-table tbody tr { border-bottom: 1px solid #f0f0f0; }
        .products-table tbody tr.even { background: #f9fafb; }
        .products-table tbody td { padding: 9px 10px; font-size: 12px; }
        .products-table tbody td.center { text-align: center; }
        .products-table tbody td.ltr    { direction: ltr; text-align: right; }

        /* Totals */
        .totals-wrap  { text-align: left; margin-bottom: 18px; }
        .totals-inner { display: inline-block; width: 280px; border: 1px solid #e0e0e0; border-radius: 6px; overflow: hidden; }
        .t-row { display: table; width: 100%; padding: 8px 12px; border-bottom: 1px solid #f0f0f0; }
        .t-row:last-child { border-bottom: none; }
        .t-label { display: table-cell; font-size: 12px; color: #6b8c94; }
        .t-value { display: table-cell; font-size: 12px; text-align: left; font-weight: 500; }
        .t-row.grand { background: #00838F; }
        .t-row.grand .t-label, .t-row.grand .t-value { color: #fff; font-weight: 700; font-size: 14px; }
        .t-row.paid-row  { background: #e8f5e9; }
        .t-row.paid-row .t-label, .t-row.paid-row .t-value { color: #2e7d32; font-weight: 600; }
        .t-row.rem-row   { background: #fff3e0; }
        .t-row.rem-row .t-label, .t-row.rem-row .t-value  { color: #e65100; font-weight: 600; }
        .t-row.disc-row .t-label, .t-row.disc-row .t-value { color: #c62828; }
        .t-row.tax-row  .t-label, .t-row.tax-row  .t-value { color: #1565c0; }

        /* Notes */
        .notes-box { border: 1px solid #e0e0e0; border-radius: 6px; padding: 10px 12px; margin-bottom: 18px; }
        .notes-box h4 { font-size: 11px; font-weight: 700; color: #6b8c94; margin-bottom: 5px; }
        .notes-box p  { font-size: 12px; color: #1a2e35; }

        /* Payments */
        .pay-title { font-size: 12px; font-weight: 700; color: #1a2e35; margin-bottom: 8px; }
        .pay-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .pay-table th { background: #005F6B; color: #fff; padding: 7px 10px; font-size: 11px; text-align: right; }
        .pay-table td { padding: 7px 10px; font-size: 11px; border-bottom: 1px solid #f0f0f0; }
        .pay-table tr.even td { background: #f9fafb; }

        /* Footer */
        .footer { border-top: 2px solid #00838F; padding-top: 14px; text-align: center; }
        .footer .thanks { font-size: 13px; font-weight: 700; color: #00838F; margin-bottom: 3px; }
        .footer p { font-size: 11px; color: #6b8c94; margin-bottom: 2px; }
        .footer .small { font-size: 10px; color: #bdbdbd; margin-top: 6px; }
    </style>
</head>
<body>
<div class="page">

    {{-- ── Header ─────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-right">
            <span class="logo-circle">T</span>
            <span class="company-text">
                <h1>توتال الكلاكلة</h1>
                <p>تجارة وتوزيع أدوات كهربائية ومعدات</p>
                <p>الخرطوم، السودان</p>
            </span>
        </div>
        <div class="header-left">
            <div class="invoice-title">فاتورة مبيعات</div>
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            <div class="invoice-date">{{ $invoice->created_at->format('Y/m/d') }}</div>
            @php
                $b = ['paid'=>'paid','confirmed'=>'confirmed','partial'=>'partial','draft'=>'draft','cancelled'=>'cancelled'];
            @endphp
            <span class="badge badge-{{ $b[$invoice->status] ?? 'draft' }}">{{ $invoice->status_label }}</span>
        </div>
    </div>

    {{-- ── Info ────────────────────────────────────────────────── --}}
    <table class="info-table">
        <tr>
            <td class="info-cell">
                <div class="info-box">
                    <h3>بيانات العميل</h3>
                    @if($invoice->customer)
                        <div class="info-row">
                            <span class="info-label">الاسم</span>
                            <span class="info-value" style="font-weight:700;">{{ $invoice->customer->name }}</span>
                        </div>
                        @if($invoice->customer->phone)
                        <div class="info-row">
                            <span class="info-label">الهاتف</span>
                            <span class="info-value">{{ $invoice->customer->phone }}</span>
                        </div>
                        @endif
                        @if($invoice->customer->address)
                        <div class="info-row">
                            <span class="info-label">العنوان</span>
                            <span class="info-value">{{ $invoice->customer->address }}</span>
                        </div>
                        @endif
                        @if($invoice->customer->tax_number)
                        <div class="info-row">
                            <span class="info-label">الرقم الضريبي</span>
                            <span class="info-value">{{ $invoice->customer->tax_number }}</span>
                        </div>
                        @endif
                    @else
                        <div class="info-row"><span class="info-value">عميل نقدي</span></div>
                    @endif
                </div>
            </td>
            <td class="info-cell" style="padding-right:0; padding-left:0;">
                <div class="info-box">
                    <h3>تفاصيل الفاتورة</h3>
                    <div class="info-row">
                        <span class="info-label">رقم الفاتورة</span>
                        <span class="info-value" style="color:#00838F;">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">تاريخ الإصدار</span>
                        <span class="info-value">{{ $invoice->created_at->format('Y/m/d') }}</span>
                    </div>
                    @if($invoice->due_date)
                    <div class="info-row">
                        <span class="info-label">تاريخ الاستحقاق</span>
                        <span class="info-value" style="{{ $invoice->is_overdue ? 'color:#c62828;' : '' }}">
                            {{ $invoice->due_date->format('Y/m/d') }}
                        </span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">نوع الدفع</span>
                        <span class="info-value">{{ $invoice->type_label }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">المستخدم</span>
                        <span class="info-value">{{ $invoice->user?->name }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Products Table ──────────────────────────────────────── --}}
    @php $hasDiscount = $invoice->items->where('discount', '>', 0)->count() > 0; @endphp
    <table class="products-table">
        <thead>
            <tr>
                <th style="width:30px;" class="center">#</th>
                <th>المنتج</th>
                <th style="width:70px;" class="center">الكمية</th>
                <th style="width:100px;">سعر الوحدة</th>
                @if($hasDiscount)
                <th style="width:80px;">الخصم</th>
                @endif
                <th style="width:100px;">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
            <tr class="{{ $i % 2 === 1 ? 'even' : '' }}">
                <td class="center">{{ $i + 1 }}</td>
                <td>
                    <div style="font-weight:500;">{{ $item->product?->name_ar ?? $item->product?->name_en ?? '—' }}</div>
                    @if($item->product?->sku)
                        <div style="font-size:10px; color:#9e9e9e;">{{ $item->product->sku }}</div>
                    @endif
                </td>
                <td class="center">{{ number_format($item->quantity) }}</td>
                <td class="ltr">{{ number_format($item->price, 2) }}</td>
                @if($hasDiscount)
                <td class="ltr" style="color:#c62828;">{{ $item->discount > 0 ? number_format($item->discount, 2) : '—' }}</td>
                @endif
                <td class="ltr" style="font-weight:600;">{{ number_format($item->total, 2) }} SDG</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Totals ──────────────────────────────────────────────── --}}
    <div class="totals-wrap">
        <div class="totals-inner">
            <div class="t-row">
                <span class="t-label">المجموع الفرعي</span>
                <span class="t-value">{{ number_format($invoice->subtotal, 2) }} SDG</span>
            </div>
            @if($invoice->discount_amount > 0)
            <div class="t-row disc-row">
                <span class="t-label">الخصم @if($invoice->discount_percent > 0)({{ $invoice->discount_percent }}%)@endif</span>
                <span class="t-value">- {{ number_format($invoice->discount_amount, 2) }} SDG</span>
            </div>
            @endif
            @if($invoice->tax_amount > 0)
            <div class="t-row tax-row">
                <span class="t-label">الضريبة ({{ $invoice->tax_percent }}%)</span>
                <span class="t-value">{{ number_format($invoice->tax_amount, 2) }} SDG</span>
            </div>
            @endif
            <div class="t-row grand">
                <span class="t-label">الإجمالي النهائي</span>
                <span class="t-value">{{ number_format($invoice->total, 2) }} SDG</span>
            </div>
            @if($invoice->paid_amount > 0)
            <div class="t-row paid-row">
                <span class="t-label">المدفوع</span>
                <span class="t-value">{{ number_format($invoice->paid_amount, 2) }} SDG</span>
            </div>
            @endif
            @if($invoice->remaining_amount > 0)
            <div class="t-row rem-row">
                <span class="t-label">المتبقي</span>
                <span class="t-value">{{ number_format($invoice->remaining_amount, 2) }} SDG</span>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Payments ────────────────────────────────────────────── --}}
    @if($invoice->payments->count())
    <div class="pay-title">سجل الدفعات</div>
    <table class="pay-table">
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
            <tr class="{{ $i % 2 === 1 ? 'even' : '' }}">
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->format('Y/m/d') : $pay->created_at->format('Y/m/d') }}</td>
                <td>{{ $methods[$pay->method] ?? $pay->method }}</td>
                <td>{{ $pay->notes ?? '—' }}</td>
                <td style="font-weight:600; color:#2e7d32; direction:ltr; text-align:right;">{{ number_format($pay->amount, 2) }} SDG</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── Notes ──────────────────────────────────────────────── --}}
    @if($invoice->notes)
    <div class="notes-box">
        <h4>ملاحظات</h4>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    {{-- ── Footer ─────────────────────────────────────────────── --}}
    <div class="footer">
        <div class="thanks">شكراً لتعاملكم مع توتال الكلاكلة</div>
        <p>الخرطوم، السودان • هاتف: 0900000000</p>
        <div class="small">تم إصدار هذه الفاتورة إلكترونياً بواسطة نظام ERP توتال الكلاكلة — {{ now()->format('Y/m/d H:i') }}</div>
    </div>

</div>
</body>
</html>
