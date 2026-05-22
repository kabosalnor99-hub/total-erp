{{-- المسار الكامل: resources/views/invoices/print.blade.php --}}
{{-- المسار: resources/views/invoices/print.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة — {{ $invoice->invoice_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Tajawal', sans-serif; }
        body { background: #fff; color: #1a2e35; font-size: 14px; }

        .page { max-width: 800px; margin: 0 auto; padding: 30px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 3px solid #00838F; margin-bottom: 24px; }
        .company-logo { width: 60px; height: 60px; background: #00838F; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; font-weight: 700; }
        .company-info h1 { font-size: 22px; font-weight: 700; color: #00838F; }
        .company-info p { font-size: 12px; color: #6b8c94; margin-top: 2px; }
        .invoice-meta { text-align: left; }
        .invoice-meta h2 { font-size: 20px; font-weight: 700; color: #1a2e35; }
        .invoice-meta .number { font-size: 16px; color: #00838F; font-weight: 600; margin-top: 4px; }
        .invoice-meta .date { font-size: 12px; color: #6b8c94; margin-top: 3px; }

        /* Status badge */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-top: 5px; }
        .badge-paid { background: #e8f5e9; color: #2e7d32; }
        .badge-confirmed { background: #e3f2fd; color: #1565c0; }
        .badge-partial { background: #fff8e1; color: #f57f17; }
        .badge-draft { background: #f5f5f5; color: #616161; }
        .badge-cancelled { background: #fce4ec; color: #c62828; }

        /* Info boxes */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
        .info-box { border: 1px solid #e0e0e0; border-radius: 8px; padding: 14px; }
        .info-box h3 { font-size: 11px; font-weight: 700; color: #00838F; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .info-box p { font-size: 13px; color: #1a2e35; margin-bottom: 3px; }
        .info-box .label { font-size: 11px; color: #6b8c94; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead tr { background: #00838F; color: #fff; }
        thead th { padding: 10px 12px; text-align: right; font-size: 13px; font-weight: 600; }
        tbody tr { border-bottom: 1px solid #f0f0f0; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody td { padding: 10px 12px; font-size: 13px; color: #1a2e35; }
        tbody td.center { text-align: center; }
        tbody td.ltr { direction: ltr; text-align: right; }

        /* Totals */
        .totals { display: flex; justify-content: flex-end; margin-bottom: 24px; }
        .totals-box { width: 300px; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        .totals-row { display: flex; justify-content: space-between; padding: 9px 14px; font-size: 13px; }
        .totals-row:not(:last-child) { border-bottom: 1px solid #f0f0f0; }
        .totals-row.total { background: #00838F; color: #fff; font-weight: 700; font-size: 15px; }
        .totals-row.remaining { background: #fff3e0; color: #e65100; font-weight: 600; }
        .totals-row.paid { background: #e8f5e9; color: #2e7d32; font-weight: 600; }

        /* Notes */
        .notes { border: 1px solid #e0e0e0; border-radius: 8px; padding: 14px; margin-bottom: 24px; }
        .notes h3 { font-size: 12px; font-weight: 700; color: #6b8c94; margin-bottom: 6px; }
        .notes p { font-size: 13px; color: #1a2e35; }

        /* Footer */
        .footer { border-top: 2px solid #00838F; padding-top: 16px; text-align: center; }
        .footer p { font-size: 12px; color: #6b8c94; margin-bottom: 3px; }
        .footer .thanks { font-size: 14px; font-weight: 600; color: #00838F; margin-bottom: 4px; }

        /* Payments history */
        .payments-section { margin-bottom: 24px; }
        .payments-section h3 { font-size: 13px; font-weight: 700; color: #1a2e35; margin-bottom: 10px; }

        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
            .page { padding: 15px; }
        }
    </style>
</head>
<body>

{{-- زر الطباعة --}}
<div class="no-print" style="background:#f4f7f8; padding:12px 30px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #ddd;">
    <a href="{{ route('invoices.show', $invoice) }}" style="color:#00838F; text-decoration:none; font-size:14px;">
        ← رجوع للفاتورة
    </a>
    <button onclick="window.print()"
            style="background:#00838F; color:#fff; border:none; padding:8px 24px; border-radius:6px; font-size:14px; font-family:'Tajawal',sans-serif; cursor:pointer; font-weight:600;">
        🖨️ طباعة
    </button>
</div>

<div class="page">

    {{-- Header --}}
    <div class="header">
        <div style="display:flex; align-items:center; gap:14px;">
            <div class="company-logo">T</div>
            <div class="company-info">
                <h1>توتال الكلاكلة</h1>
                <p>تجارة وتوزيع أدوات كهربائية ومعدات</p>
                <p>الخرطوم، السودان</p>
            </div>
        </div>
        <div class="invoice-meta">
            <h2>فاتورة مبيعات</h2>
            <div class="number">{{ $invoice->invoice_number }}</div>
            <div class="date">{{ $invoice->created_at->format('Y/m/d') }}</div>
            @php
                $badges = [
                    'paid'      => 'paid',
                    'confirmed' => 'confirmed',
                    'partial'   => 'partial',
                    'draft'     => 'draft',
                    'cancelled' => 'cancelled',
                ];
            @endphp
            <span class="badge badge-{{ $badges[$invoice->status] ?? 'draft' }}">
                {{ $invoice->status_label }}
            </span>
        </div>
    </div>

    {{-- معلومات العميل والفاتورة --}}
    <div class="info-grid">
        <div class="info-box">
            <h3>بيانات العميل</h3>
            @if($invoice->customer)
                <p style="font-weight:600;">{{ $invoice->customer->name }}</p>
                @if($invoice->customer->phone)
                    <p class="label">الهاتف: {{ $invoice->customer->phone }}</p>
                @endif
                @if($invoice->customer->address)
                    <p class="label">العنوان: {{ $invoice->customer->address }}</p>
                @endif
                @if($invoice->customer->tax_number)
                    <p class="label">الرقم الضريبي: {{ $invoice->customer->tax_number }}</p>
                @endif
            @else
                <p>عميل نقدي</p>
            @endif
        </div>
        <div class="info-box">
            <h3>تفاصيل الفاتورة</h3>
            <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                <span class="label">رقم الفاتورة</span>
                <span style="font-weight:600; color:#00838F;">{{ $invoice->invoice_number }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                <span class="label">التاريخ</span>
                <span>{{ $invoice->created_at->format('Y/m/d') }}</span>
            </div>
            @if($invoice->due_date)
            <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                <span class="label">تاريخ الاستحقاق</span>
                <span style="{{ $invoice->is_overdue ? 'color:#c62828; font-weight:600;' : '' }}">
                    {{ $invoice->due_date->format('Y/m/d') }}
                </span>
            </div>
            @endif
            <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                <span class="label">نوع الدفع</span>
                <span>{{ $invoice->type_label }}</span>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span class="label">المستخدم</span>
                <span>{{ $invoice->user?->name }}</span>
            </div>
        </div>
    </div>

    {{-- جدول المنتجات --}}
    <table>
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>المنتج</th>
                <th style="width:80px; text-align:center;">الكمية</th>
                <th style="width:110px;">سعر الوحدة</th>
                @if($invoice->items->where('discount', '>', 0)->count())
                <th style="width:90px;">الخصم</th>
                @endif
                <th style="width:110px;">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>
                    <div style="font-weight:500;">{{ $item->product?->name_ar ?? $item->product?->name_en ?? '—' }}</div>
                    @if($item->product?->sku)
                        <div style="font-size:11px; color:#6b8c94;">{{ $item->product->sku }}</div>
                    @endif
                </td>
                <td class="center">{{ number_format($item->quantity) }}</td>
                <td class="ltr">{{ number_format($item->price, 2) }}</td>
                @if($invoice->items->where('discount', '>', 0)->count())
                <td class="ltr">{{ $item->discount > 0 ? number_format($item->discount, 2) : '—' }}</td>
                @endif
                <td class="ltr" style="font-weight:600;">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- الإجماليات --}}
    <div class="totals">
        <div class="totals-box">
            <div class="totals-row">
                <span>المجموع الفرعي</span>
                <span>{{ number_format($invoice->subtotal, 2) }} SDG</span>
            </div>
            @if($invoice->discount_amount > 0)
            <div class="totals-row" style="color:#c62828;">
                <span>الخصم
                    @if($invoice->discount_percent > 0)({{ $invoice->discount_percent }}%)@endif
                </span>
                <span>- {{ number_format($invoice->discount_amount, 2) }} SDG</span>
            </div>
            @endif
            @if($invoice->tax_amount > 0)
            <div class="totals-row" style="color:#1565c0;">
                <span>الضريبة ({{ $invoice->tax_percent }}%)</span>
                <span>{{ number_format($invoice->tax_amount, 2) }} SDG</span>
            </div>
            @endif
            <div class="totals-row total">
                <span>الإجمالي النهائي</span>
                <span>{{ number_format($invoice->total, 2) }} SDG</span>
            </div>
            @if($invoice->paid_amount > 0)
            <div class="totals-row paid">
                <span>المدفوع</span>
                <span>{{ number_format($invoice->paid_amount, 2) }} SDG</span>
            </div>
            @endif
            @if($invoice->remaining_amount > 0)
            <div class="totals-row remaining">
                <span>المتبقي</span>
                <span>{{ number_format($invoice->remaining_amount, 2) }} SDG</span>
            </div>
            @endif
        </div>
    </div>

    {{-- سجل الدفعات --}}
    @if($invoice->payments->count())
    <div class="payments-section">
        <h3>سجل الدفعات</h3>
        <table>
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
                @php
                    $methods = ['cash'=>'نقدي','bank'=>'بنك','other'=>'أخرى'];
                @endphp
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->format('Y/m/d') : $pay->created_at->format('Y/m/d') }}</td>
                    <td>{{ $methods[$pay->method] ?? $pay->method }}</td>
                    <td>{{ $pay->notes ?? '—' }}</td>
                    <td class="ltr" style="font-weight:600; color:#2e7d32;">{{ number_format($pay->amount, 2) }} SDG</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ملاحظات --}}
    @if($invoice->notes)
    <div class="notes">
        <h3>ملاحظات</h3>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p class="thanks">شكراً لتعاملكم مع توتال الكلاكلة</p>
        <p>الخرطوم، السودان • هاتف: 0900000000</p>
        <p style="margin-top:8px; font-size:11px; color:#9e9e9e;">
            تم إصدار هذه الفاتورة بواسطة نظام ERP توتال الكلاكلة
        </p>
    </div>

</div>

</body>
</html>
