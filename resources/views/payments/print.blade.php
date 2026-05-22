{{-- المسار: resources/views/payments/print.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>سند قبض — {{ $payment->payment_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Tajawal', sans-serif; }
        body { background: #fff; color: #1a2e35; font-size: 14px; }
        .page { max-width: 700px; margin: 0 auto; padding: 30px; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #00838F; padding-bottom: 18px; margin-bottom: 24px; }
        .logo-circle { width: 52px; height: 52px; background: #00838F; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px; font-weight: 700; }
        .company-info h1 { font-size: 20px; font-weight: 700; color: #00838F; }
        .company-info p { font-size: 12px; color: #6b8c94; margin-top: 2px; }
        .voucher-meta { text-align: left; }
        .voucher-meta h2 { font-size: 18px; font-weight: 700; color: #1a2e35; }
        .voucher-meta .number { font-size: 15px; color: #00838F; font-weight: 600; margin-top: 3px; }
        .voucher-meta .date { font-size: 12px; color: #6b8c94; margin-top: 3px; }

        .amount-box { background: #00838F; color: #fff; border-radius: 10px; padding: 18px 24px; text-align: center; margin-bottom: 24px; }
        .amount-box .label { font-size: 13px; opacity: 0.85; margin-bottom: 4px; }
        .amount-box .amount { font-size: 32px; font-weight: 700; }
        .amount-box .currency { font-size: 14px; opacity: 0.85; margin-top: 3px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
        .info-box { border: 1px solid #e0e0e0; border-radius: 8px; padding: 14px; }
        .info-box h3 { font-size: 11px; font-weight: 700; color: #00838F; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px; }
        .info-row .lbl { color: #6b8c94; }
        .info-row .val { font-weight: 500; color: #1a2e35; }

        .method-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }

        .invoice-section { border: 1px solid #e0e0e0; border-radius: 8px; padding: 14px; margin-bottom: 24px; }
        .invoice-section h3 { font-size: 11px; font-weight: 700; color: #6b8c94; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
        .invoice-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 5px; }

        .notes-box { border: 1px dashed #b0bec5; border-radius: 8px; padding: 12px 14px; margin-bottom: 24px; }
        .notes-box h4 { font-size: 11px; font-weight: 700; color: #6b8c94; margin-bottom: 5px; }

        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 24px; margin-top: 40px; }
        .sig-box { text-align: center; border-top: 1px solid #b0bec5; padding-top: 8px; font-size: 12px; color: #6b8c94; }

        .footer { border-top: 2px solid #00838F; padding-top: 14px; text-align: center; }
        .footer .thanks { font-size: 13px; font-weight: 700; color: #00838F; margin-bottom: 3px; }
        .footer p { font-size: 11px; color: #6b8c94; }

        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print" style="background:#f4f7f8; padding:12px 30px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #ddd;">
    <a href="{{ route('payments.show', $payment) }}" style="color:#00838F; text-decoration:none; font-size:14px;">← رجوع</a>
    <button onclick="window.print()"
            style="background:#00838F;color:#fff;border:none;padding:8px 24px;border-radius:6px;font-size:14px;font-family:'Tajawal',sans-serif;cursor:pointer;font-weight:600;">
        🖨️ طباعة
    </button>
</div>

<div class="page">

    {{-- Header --}}
    <div class="header">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="logo-circle">T</div>
            <div class="company-info">
                <h1>توتال الكلاكلة</h1>
                <p>تجارة وتوزيع أدوات كهربائية ومعدات</p>
                <p>الخرطوم، السودان</p>
            </div>
        </div>
        <div class="voucher-meta">
            <h2>سند قبض</h2>
            <div class="number">{{ $payment->payment_number }}</div>
            <div class="date">{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('Y/m/d') : $payment->created_at->format('Y/m/d') }}</div>
        </div>
    </div>

    {{-- المبلغ --}}
    <div class="amount-box">
        <div class="label">المبلغ المستلم</div>
        <div class="amount">{{ number_format($payment->amount, 2) }}</div>
        <div class="currency">جنيه سوداني (SDG)</div>
    </div>

    {{-- معلومات --}}
    <div class="info-grid">
        <div class="info-box">
            <h3>بيانات الدافع</h3>
            @if($payment->customer)
            <div class="info-row">
                <span class="lbl">الاسم</span>
                <span class="val" style="font-weight:700;">{{ $payment->customer->name }}</span>
            </div>
            @if($payment->customer->phone)
            <div class="info-row">
                <span class="lbl">الهاتف</span>
                <span class="val">{{ $payment->customer->phone }}</span>
            </div>
            @endif
            @if($payment->customer->address)
            <div class="info-row">
                <span class="lbl">العنوان</span>
                <span class="val">{{ $payment->customer->address }}</span>
            </div>
            @endif
            @else
            <div class="info-row"><span class="val">عميل نقدي</span></div>
            @endif
        </div>
        <div class="info-box">
            <h3>تفاصيل السند</h3>
            <div class="info-row">
                <span class="lbl">رقم السند</span>
                <span class="val" style="color:#00838F;">{{ $payment->payment_number }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">تاريخ الاستلام</span>
                <span class="val">{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('Y/m/d') : $payment->created_at->format('Y/m/d') }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">طريقة الدفع</span>
                <span class="val"><span class="method-badge">{{ $payment->method_label }}</span></span>
            </div>
            @if($payment->reference)
            <div class="info-row">
                <span class="lbl">رقم المرجع</span>
                <span class="val" style="direction:ltr;text-align:right;">{{ $payment->reference }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="lbl">سجّل بواسطة</span>
                <span class="val">{{ $payment->user?->name }}</span>
            </div>
        </div>
    </div>

    {{-- الفاتورة المرتبطة --}}
    @if($payment->invoice)
    <div class="invoice-section">
        <h3>الفاتورة المرتبطة</h3>
        <div class="invoice-row">
            <span style="color:#6b8c94;">رقم الفاتورة</span>
            <span style="font-weight:600;color:#00838F;">{{ $payment->invoice->invoice_number }}</span>
        </div>
        <div class="invoice-row">
            <span style="color:#6b8c94;">إجمالي الفاتورة</span>
            <span style="font-weight:600;">{{ number_format($payment->invoice->total, 2) }} SDG</span>
        </div>
        <div class="invoice-row">
            <span style="color:#6b8c94;">إجمالي المدفوع</span>
            <span style="font-weight:600;color:#2e7d32;">{{ number_format($payment->invoice->paid_amount, 2) }} SDG</span>
        </div>
        @if($payment->invoice->remaining_amount > 0)
        <div class="invoice-row">
            <span style="color:#6b8c94;">المتبقي بعد هذه الدفعة</span>
            <span style="font-weight:600;color:#e65100;">{{ number_format($payment->invoice->remaining_amount, 2) }} SDG</span>
        </div>
        @else
        <div class="invoice-row">
            <span style="color:#6b8c94;">حالة الفاتورة</span>
            <span style="font-weight:700;color:#2e7d32;">✓ مسددة بالكامل</span>
        </div>
        @endif
    </div>
    @endif

    {{-- ملاحظات --}}
    @if($payment->notes)
    <div class="notes-box">
        <h4>ملاحظات</h4>
        <p style="font-size:13px;">{{ $payment->notes }}</p>
    </div>
    @endif

    {{-- التوقيعات --}}
    <div class="signatures">
        <div class="sig-box">توقيع المستلم</div>
        <div class="sig-box">توقيع الدافع</div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="thanks">شكراً لتعاملكم مع توتال الكلاكلة</div>
        <p>الخرطوم، السودان • هاتف: 0900000000</p>
        <p style="font-size:10px;color:#bdbdbd;margin-top:6px;">
            تم إصدار هذا السند بواسطة نظام ERP توتال الكلاكلة — {{ now()->format('Y/m/d H:i') }}
        </p>
    </div>

</div>
</body>
</html>
