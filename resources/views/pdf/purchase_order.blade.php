{{-- resources/views/pdf/purchase_order.blade.php --}}
{{-- محرك: mPDF — يدعم العربية بشكل كامل --}}
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Arial', 'sans-serif';
        font-size: 11px;
        color: #1A2E35;
        direction: rtl;
        text-align: right;
    }

    /* ─── Header ─── */
    .header {
        border-bottom: 3px solid #00838F;
        padding-bottom: 16px;
        margin-bottom: 20px;
    }
    .header table { width: 100%; }
    .header td { vertical-align: top; }
    .td-company { width: 55%; text-align: right; }
    .td-docinfo  { width: 45%; text-align: left; }

    .company-name {
        font-size: 20px;
        font-weight: bold;
        color: #00838F;
        margin-bottom: 4px;
    }
    .company-sub { font-size: 9px; color: #6B8C94; line-height: 1.7; }

    .doc-title { font-size: 17px; font-weight: bold; color: #005F6B; margin-bottom: 8px; }
    .doc-meta { font-size: 9px; color: #6B8C94; line-height: 1.9; }
    .doc-meta span { font-weight: bold; color: #1A2E35; }

    /* ─── Parties ─── */
    .parties { margin-bottom: 18px; }
    .parties table { width: 100%; border-spacing: 8px 0; border-collapse: separate; }
    .party-box {
        width: 50%;
        border: 1px solid #e5e7eb;
        border-radius: 5px;
        padding: 12px;
        background: #F4F7F8;
        vertical-align: top;
    }
    .party-label {
        font-size: 9px; font-weight: bold;
        color: #00838F; margin-bottom: 6px;
        border-bottom: 1px solid #d1fae5;
        padding-bottom: 3px;
    }
    .party-name { font-size: 12px; font-weight: bold; color: #1A2E35; margin-bottom: 2px; }
    .party-sub  { font-size: 10px; color: #374151; line-height: 1.6; }

    /* ─── Items ─── */
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .items-table thead tr { background: #00838F; color: #ffffff; }
    .items-table thead th {
        padding: 9px 10px;
        font-size: 10px;
        font-weight: bold;
        text-align: right;
    }
    .items-table thead th.center { text-align: center; }
    .items-table thead th.ltr    { text-align: left; direction: ltr; }
    .items-table tbody tr:nth-child(even) { background: #f8fafb; }
    .items-table tbody td {
        padding: 9px 10px;
        font-size: 10px;
        border-bottom: 1px solid #f0f4f5;
        text-align: right;
        vertical-align: middle;
    }
    .items-table tbody td.center { text-align: center; }
    .items-table tbody td.ltr    { text-align: left; direction: ltr; font-weight: bold; color: #005F6B; }
    .product-sku {
        font-size: 8px; color: #9ca3af;
        display: block; margin-top: 1px;
        direction: ltr; text-align: left;
    }

    /* ─── Totals ─── */
    .totals-wrap { margin-bottom: 20px; }
    .totals-wrap table { width: 100%; }
    .totals-wrap td.spacer { width: 55%; }
    .totals-wrap td.totals { width: 45%; vertical-align: top; }

    .totals-box {
        border: 1px solid #e5e7eb;
        border-radius: 5px;
        overflow: hidden;
    }
    .totals-box table { width: 100%; border-collapse: collapse; }
    .totals-box tr td {
        padding: 8px 12px;
        font-size: 10px;
        border-bottom: 1px solid #f0f4f5;
    }
    .totals-box tr td.lbl { text-align: right; color: #374151; }
    .totals-box tr td.amt { text-align: left; direction: ltr; font-weight: bold; color: #1A2E35; }
    .totals-box tr.grand { background: #005F6B; }
    .totals-box tr.grand td { font-size: 12px; font-weight: bold; color: #ffffff; border: none; }

    /* ─── Notes ─── */
    .notes-box {
        border: 1px solid #fcd34d;
        border-radius: 5px;
        padding: 12px;
        margin-bottom: 20px;
        background: #fffbeb;
    }
    .notes-label { font-size: 9px; font-weight: bold; color: #92400e; margin-bottom: 5px; }
    .notes-text  { font-size: 10px; color: #374151; line-height: 1.7; }

    /* ─── Signatures ─── */
    .sig-table { width: 100%; margin-top: 36px; border-collapse: collapse; }
    .sig-cell  {
        width: 33%; text-align: center;
        border-top: 1px dashed #9ca3af;
        padding-top: 8px; vertical-align: top;
        font-size: 9px; color: #6B8C94;
    }
    .sig-name { font-weight: bold; font-size: 10px; color: #1A2E35; margin-top: 3px; }

    /* ─── Footer ─── */
    .footer {
        text-align: center;
        margin-top: 24px;
        padding-top: 10px;
        border-top: 1px solid #e5e7eb;
        font-size: 8px;
        color: #9ca3af;
    }
</style>
</head>
<body>

@php $settings = \App\Models\Setting::getAll(); @endphp

{{-- ─── Header ─── --}}
<div class="header">
    <table>
        <tr>
            <td class="td-company">
                <div class="company-name">توتال الكلاكلة</div>
                <div class="company-sub">
                    تجارة وتوزيع أدوات كهربائية ومعدات<br>
                    {{ $settings['company_address'] ?? 'الكلاكلة — الخرطوم — السودان' }}<br>
                    @if($settings['company_phone'] ?? null)هاتف: {{ $settings['company_phone'] }}<br>@endif
                    {{ $settings['company_email'] ?? 'info@total-kalaklah.sd' }}
                </div>
            </td>
            <td class="td-docinfo">
                <div class="doc-title">أمر شراء</div>
                <div class="doc-meta">
                    رقم الأمر: <span>{{ $purchaseOrder->order_number }}</span><br>
                    التاريخ: <span>{{ $purchaseOrder->created_at->format('Y/m/d') }}</span><br>
                    @if($purchaseOrder->expected_date)
                    تاريخ التسليم: <span>{{ $purchaseOrder->expected_date->format('Y/m/d') }}</span><br>
                    @endif
                    الحالة: <span>
                        @php $labels=['draft'=>'مسودة','sent'=>'أُرسل للمورد','partial'=>'مستلم جزئياً','received'=>'مستلم كاملاً','cancelled'=>'ملغي']; @endphp
                        {{ $labels[$purchaseOrder->status] ?? $purchaseOrder->status }}
                    </span>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- ─── الأطراف ─── --}}
<div class="parties">
    <table>
        <tr>
            <td class="party-box">
                <div class="party-label">من (المشتري)</div>
                <div class="party-name">توتال الكلاكلة</div>
                <div class="party-sub">
                    {{ $settings['company_address'] ?? 'الكلاكلة — الخرطوم' }}<br>
                    @if($settings['company_tax_number'] ?? null)الرقم الضريبي: {{ $settings['company_tax_number'] }}@endif
                </div>
            </td>
            <td class="party-box">
                <div class="party-label">إلى (المورد)</div>
                <div class="party-name">{{ $purchaseOrder->supplier->name }}</div>
                <div class="party-sub">
                    @if($purchaseOrder->supplier->company_name){{ $purchaseOrder->supplier->company_name }}<br>@endif
                    @if($purchaseOrder->supplier->address){{ $purchaseOrder->supplier->address }}<br>@endif
                    @if($purchaseOrder->supplier->phone)هاتف: {{ $purchaseOrder->supplier->phone }}<br>@endif
                    @if($purchaseOrder->supplier->tax_number)ض.ق.م: {{ $purchaseOrder->supplier->tax_number }}@endif
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- ─── البنود ─── --}}
<table class="items-table">
    <thead>
        <tr>
            <th class="center" style="width:28px">#</th>
            <th>المنتج</th>
            <th class="center" style="width:65px">الكمية</th>
            <th class="ltr" style="width:85px">سعر الوحدة</th>
            <th class="center" style="width:60px">خصم %</th>
            <th class="ltr" style="width:90px">الإجمالي</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchaseOrder->items as $index => $item)
        @php
            $disc      = $item->discount ?? 0;
            $lineTotal = $item->quantity * $item->unit_price * (1 - $disc / 100);
        @endphp
        <tr>
            <td class="center">{{ $index + 1 }}</td>
            <td>
                {{ $item->product->name_ar }}
                <span class="product-sku">{{ $item->product->sku }}</span>
            </td>
            <td class="center">{{ number_format($item->quantity, 2) }}</td>
            <td class="ltr">{{ number_format($item->unit_price, 2) }}</td>
            <td class="center">{{ $disc > 0 ? number_format($disc, 1).'%' : '—' }}</td>
            <td class="ltr">{{ number_format($lineTotal, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ─── الإجماليات ─── --}}
<div class="totals-wrap">
    <table>
        <tr>
            <td class="spacer"></td>
            <td class="totals">
                <div class="totals-box">
                    <table>
                        <tr>
                            <td class="lbl">المجموع الفرعي</td>
                            <td class="amt">{{ number_format($purchaseOrder->subtotal, 2) }}</td>
                        </tr>
                        @if($purchaseOrder->discount > 0)
                        <tr>
                            <td class="lbl">الخصم</td>
                            <td class="amt">({{ number_format($purchaseOrder->discount, 2) }})</td>
                        </tr>
                        @endif
                        @if($purchaseOrder->tax > 0)
                        <tr>
                            <td class="lbl">ضريبة القيمة المضافة</td>
                            <td class="amt">{{ number_format($purchaseOrder->tax, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="grand">
                            <td class="lbl">الإجمالي الكلي</td>
                            <td class="amt">{{ number_format($purchaseOrder->total, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- ─── ملاحظات ─── --}}
@if($purchaseOrder->notes)
<div class="notes-box">
    <div class="notes-label">ملاحظات وشروط خاصة</div>
    <div class="notes-text">{{ $purchaseOrder->notes }}</div>
</div>
@endif

{{-- ─── التوقيعات ─── --}}
<table class="sig-table">
    <tr>
        <td class="sig-cell">
            أُعد بواسطة
            <div class="sig-name">{{ $purchaseOrder->user->name }}</div>
        </td>
        <td class="sig-cell">
            اعتمد بواسطة
            <div style="margin-top:4px; color:#d1d5db">.............................</div>
        </td>
        <td class="sig-cell">
            توقيع المورد
            <div style="margin-top:4px; color:#d1d5db">.............................</div>
        </td>
    </tr>
</table>

{{-- ─── Footer ─── --}}
<div class="footer">
    توتال الكلاكلة — نظام ERP | طُبع بتاريخ: {{ now()->format('Y/m/d H:i') }}
</div>

</body>
</html>
