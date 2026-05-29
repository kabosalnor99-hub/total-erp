{{-- المسار الكامل: resources/views/pdf/purchase_order.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>أمر الشراء {{ $purchaseOrder->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #1A2E35;
            direction: rtl;
            background: #fff;
        }

        .page {
            width: 100%;
            padding: 30px;
        }

        /* ─── clearfix ─── */
        .clearfix::after { content: ''; display: table; clear: both; }

        /* ─── Header: شركة يمين، معلومات المستند يسار ─── */
        .header {
            width: 100%;
            border-bottom: 3px solid #00838F;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td { vertical-align: top; }
        .header-right { width: 55%; }
        .header-left  { width: 45%; text-align: left; }

        .company-info h1 {
            font-size: 22px;
            font-weight: bold;
            color: #00838F;
            margin-bottom: 4px;
        }
        .company-info p {
            font-size: 10px;
            color: #6B8C94;
            line-height: 1.6;
        }

        .doc-title {
            font-size: 18px;
            font-weight: bold;
            color: #005F6B;
            margin-bottom: 8px;
        }
        .doc-info-table td {
            font-size: 10px;
            padding: 2px 6px;
            color: #6B8C94;
        }
        .doc-info-table td:last-child {
            font-weight: bold;
            color: #1A2E35;
        }

        /* ─── Parties: صندوقان جنباً إلى جنب ─── */
        .parties-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-bottom: 24px;
        }
        .party-box {
            width: 50%;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 14px;
            background: #F4F7F8;
            vertical-align: top;
        }
        .party-box h3 {
            font-size: 10px;
            font-weight: bold;
            color: #00838F;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            border-bottom: 1px solid #d1fae5;
            padding-bottom: 4px;
        }
        .party-box p {
            font-size: 11px;
            color: #374151;
            line-height: 1.7;
        }
        .party-box .name {
            font-size: 13px;
            font-weight: bold;
            color: #1A2E35;
            margin-bottom: 2px;
        }

        /* ─── Status Badge ─── */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-draft    { background: #f3f4f6; color: #4b5563; }
        .status-sent     { background: #dbeafe; color: #1d4ed8; }
        .status-partial  { background: #fef3c7; color: #92400e; }
        .status-received { background: #d1fae5; color: #065f46; }
        .status-cancelled{ background: #fee2e2; color: #991b1b; }

        /* ─── Items Table ─── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .items-table thead tr { background: #00838F; color: white; }
        .items-table thead th {
            padding: 10px 12px;
            text-align: right;
            font-size: 10px;
            font-weight: bold;
        }
        .items-table thead th:last-child { text-align: left; }
        .items-table tbody tr:nth-child(even) { background: #f8fafb; }
        .items-table tbody td {
            padding: 10px 12px;
            font-size: 11px;
            border-bottom: 1px solid #f0f4f5;
            vertical-align: top;
        }
        .items-table tbody td:last-child { text-align: left; }

        .product-sku {
            font-size: 9px;
            color: #9ca3af;
            font-family: monospace;
            display: block;
            margin-top: 2px;
        }

        /* ─── Totals: محاذاة يسار باستخدام جدول ─── */
        .totals-wrapper {
            width: 100%;
            margin-bottom: 24px;
        }
        .totals-outer {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-outer td.spacer { width: 60%; }
        .totals-outer td.totals-cell { width: 40%; vertical-align: top; }

        .totals-box {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            width: 100%;
        }
        .totals-box table { width: 100%; border-collapse: collapse; }
        .totals-box table tr td {
            padding: 8px 14px;
            font-size: 11px;
            border-bottom: 1px solid #f0f4f5;
        }
        .totals-box table tr td:last-child {
            text-align: left;
            font-weight: bold;
        }
        .totals-box table tr.total-row { background: #005F6B; color: white; }
        .totals-box table tr.total-row td {
            font-size: 13px;
            font-weight: bold;
            border: none;
        }

        /* ─── Notes ─── */
        .notes-box {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 14px;
            margin-bottom: 24px;
            background: #fffbeb;
        }
        .notes-box h4 {
            font-size: 10px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 6px;
        }
        .notes-box p { font-size: 11px; color: #374151; line-height: 1.6; }

        /* ─── Signatures: ثلاثة أعمدة متساوية ─── */
        .sig-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 16px 0;
            margin-top: 40px;
        }
        .sig-cell {
            width: 33%;
            text-align: center;
            border-top: 1px dashed #9ca3af;
            padding-top: 8px;
            vertical-align: top;
        }
        .sig-cell p { font-size: 10px; color: #6B8C94; }

        /* ─── Footer ─── */
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
<div class="page">

    @php $settings = \App\Models\Setting::getAll(); @endphp

    {{-- ─── Header ─── --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-right">
                    <div class="company-info">
                        <h1>توتال الكلاكلة</h1>
                        <p>
                            تجارة وتوزيع أدوات كهربائية ومعدات<br>
                            {{ $settings['company_address'] ?? 'الكلاكلة — الخرطوم — السودان' }}<br>
                            @if($settings['company_phone'] ?? null)
                                هاتف: {{ $settings['company_phone'] }}<br>
                            @endif
                            {{ $settings['company_email'] ?? '' }}
                        </p>
                    </div>
                </td>
                <td class="header-left">
                    <div class="doc-title">أمر شراء</div>
                    <table class="doc-info-table">
                        <tr>
                            <td>رقم الأمر:</td>
                            <td>{{ $purchaseOrder->order_number }}</td>
                        </tr>
                        <tr>
                            <td>التاريخ:</td>
                            <td>{{ $purchaseOrder->created_at->format('Y/m/d') }}</td>
                        </tr>
                        @if($purchaseOrder->expected_date)
                        <tr>
                            <td>تاريخ التسليم المتوقع:</td>
                            <td>{{ $purchaseOrder->expected_date->format('Y/m/d') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td>الحالة:</td>
                            <td>
                                @php
                                    $labels = [
                                        'draft'     => 'مسودة',
                                        'sent'      => 'أُرسل للمورد',
                                        'partial'   => 'مستلم جزئياً',
                                        'received'  => 'مستلم كاملاً',
                                        'cancelled' => 'ملغي',
                                    ];
                                @endphp
                                {{ $labels[$purchaseOrder->status] ?? $purchaseOrder->status }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- ─── الأطراف ─── --}}
    <table class="parties-table">
        <tr>
            <td class="party-box">
                <h3>من (المشتري)</h3>
                <p class="name">توتال الكلاكلة</p>
                <p>
                    {{ $settings['company_address'] ?? 'الكلاكلة — الخرطوم' }}<br>
                    @if($settings['company_tax_number'] ?? null)
                        الرقم الضريبي: {{ $settings['company_tax_number'] }}
                    @endif
                </p>
            </td>
            <td class="party-box">
                <h3>إلى (المورد)</h3>
                <p class="name">{{ $purchaseOrder->supplier->name }}</p>
                <p>
                    @if($purchaseOrder->supplier->company_name)
                        {{ $purchaseOrder->supplier->company_name }}<br>
                    @endif
                    @if($purchaseOrder->supplier->address)
                        {{ $purchaseOrder->supplier->address }}<br>
                    @endif
                    @if($purchaseOrder->supplier->phone)
                        هاتف: {{ $purchaseOrder->supplier->phone }}<br>
                    @endif
                    @if($purchaseOrder->supplier->tax_number)
                        ض.ق.م: {{ $purchaseOrder->supplier->tax_number }}
                    @endif
                </p>
            </td>
        </tr>
    </table>

    {{-- ─── البنود ─── --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>المنتج</th>
                <th style="width:80px">الكمية</th>
                <th style="width:90px">سعر الوحدة</th>
                <th style="width:70px">خصم %</th>
                <th style="width:100px">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $index => $item)
            @php
                $lineDiscount = $item->discount ?? 0;
                $lineTotal    = $item->quantity * $item->unit_price * (1 - $lineDiscount / 100);
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    {{ $item->product->name_ar }}
                    <span class="product-sku">{{ $item->product->sku }}</span>
                </td>
                <td>{{ number_format($item->quantity, 2) }}</td>
                <td>{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ $lineDiscount > 0 ? number_format($lineDiscount, 1).'%' : '—' }}</td>
                <td>{{ number_format($lineTotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ─── الإجماليات ─── --}}
    <table class="totals-outer">
        <tr>
            <td class="spacer"></td>
            <td class="totals-cell">
                <div class="totals-box">
                    <table>
                        <tr>
                            <td>المجموع الفرعي</td>
                            <td>{{ number_format($purchaseOrder->subtotal, 2) }}</td>
                        </tr>
                        @if($purchaseOrder->discount > 0)
                        <tr>
                            <td>الخصم</td>
                            <td>({{ number_format($purchaseOrder->discount, 2) }})</td>
                        </tr>
                        @endif
                        @if($purchaseOrder->tax > 0)
                        <tr>
                            <td>ضريبة القيمة المضافة</td>
                            <td>{{ number_format($purchaseOrder->tax, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="total-row">
                            <td>الإجمالي الكلي</td>
                            <td>{{ number_format($purchaseOrder->total, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- ─── ملاحظات ─── --}}
    @if($purchaseOrder->notes)
    <div class="notes-box">
        <h4>ملاحظات وشروط خاصة</h4>
        <p>{{ $purchaseOrder->notes }}</p>
    </div>
    @endif

    {{-- ─── التوقيعات ─── --}}
    <table class="sig-table">
        <tr>
            <td class="sig-cell">
                <p>أُعد بواسطة</p>
                <p style="font-weight:bold; margin-top:4px">{{ $purchaseOrder->user->name }}</p>
            </td>
            <td class="sig-cell">
                <p>اعتمد بواسطة</p>
                <p style="margin-top:4px; color:#d1d5db">.............................</p>
            </td>
            <td class="sig-cell">
                <p>توقيع المورد</p>
                <p style="margin-top:4px; color:#d1d5db">.............................</p>
            </td>
        </tr>
    </table>

    {{-- ─── Footer ─── --}}
    <div class="footer">
        توتال الكلاكلة — نظام ERP |
        طُبع بتاريخ: {{ now()->format('Y/m/d H:i') }}
    </div>

</div>
</body>
</html>
