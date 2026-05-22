{{-- المسار الكامل: resources/views/vouchers/print.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $voucher->type_label }} #{{ $voucher->voucher_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            font-size: 12px;
            color: #1A2E35;
            background: #f4f7f8;
            direction: rtl;
        }

        .page {
            max-width: 720px;
            margin: 30px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,.1);
            overflow: hidden;
        }

        /* ── الترويسة ── */
        .header {
            background: #00838F;
            color: #fff;
            padding: 20px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header .company { font-size: 20px; font-weight: bold; }
        .header .subtitle { font-size: 12px; opacity: .85; margin-top: 4px; }
        .header .voucher-type {
            font-size: 18px;
            font-weight: bold;
            text-align: left;
        }
        .header .voucher-number {
            font-size: 13px;
            opacity: .85;
            margin-top: 4px;
            text-align: left;
        }

        /* ── شريط اللون حسب النوع ── */
        .type-bar {
            height: 6px;
        }
        .receipt-bar { background: linear-gradient(90deg, #10b981, #34d399); }
        .payment-bar { background: linear-gradient(90deg, #ef4444, #f87171); }

        /* ── جسم السند ── */
        .body { padding: 28px; }

        /* ── بطاقة المعلومات الرئيسية ── */
        .main-info {
            display: flex;
            justify-content: space-between;
            background: #f4f7f8;
            border: 1px solid #dde4e6;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 20px;
            gap: 20px;
        }
        .info-item { flex: 1; }
        .info-label { font-size: 10px; color: #6B8C94; margin-bottom: 3px; }
        .info-value { font-size: 13px; font-weight: bold; color: #1A2E35; }

        /* ── مبلغ السند ── */
        .amount-box {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .receipt-amount { background: #ecfdf5; border: 2px solid #6ee7b7; }
        .payment-amount { background: #fef2f2; border: 2px solid #fca5a5; }
        .amount-label { font-size: 13px; color: #6B8C94; margin-bottom: 6px; }
        .amount-value { font-size: 36px; font-weight: bold; }
        .receipt-amount .amount-value { color: #065f46; }
        .payment-amount .amount-value { color: #991b1b; }
        .amount-words { font-size: 12px; color: #6B8C94; margin-top: 4px; }

        /* ── تفاصيل السند ── */
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table tr { border-bottom: 1px solid #e5e7eb; }
        .details-table td {
            padding: 9px 12px;
            font-size: 12px;
        }
        .details-table td:first-child { color: #6B8C94; width: 35%; }
        .details-table td:last-child  { font-weight: 500; }

        /* ── البيان ── */
        .description-box {
            background: #fffbf0;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .description-box .d-label { font-size: 10px; color: #92400e; margin-bottom: 4px; }
        .description-box .d-text  { font-size: 13px; color: #1A2E35; }

        /* ── التوقيعات ── */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 16px;
            border-top: 1px dashed #dde4e6;
            gap: 16px;
        }
        .sig-box {
            flex: 1;
            text-align: center;
        }
        .sig-line {
            border-top: 1px solid #9ca3af;
            margin-bottom: 6px;
            padding-top: 0;
        }
        .sig-label { font-size: 11px; color: #6B8C94; }

        /* ── تذييل ── */
        .footer {
            background: #f4f7f8;
            border-top: 1px solid #dde4e6;
            padding: 12px 28px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #9ca3af;
        }
        .footer strong { color: #00838F; }

        /* ── أزرار الطباعة ── */
        .print-buttons {
            max-width: 720px;
            margin: 16px auto;
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        .btn-print { background: #00838F; color: #fff; }
        .btn-back  { background: #fff; color: #6B8C94; border: 1px solid #dde4e6; }
        .btn:hover { opacity: .9; }

        @media print {
            body { background: #fff; }
            .page { margin: 0; border-radius: 0; box-shadow: none; }
            .print-buttons { display: none; }
        }
    </style>
</head>
<body>

    {{-- أزرار الطباعة (تختفي عند الطباعة) --}}
    <div class="print-buttons">
        <button onclick="window.print()" class="btn btn-print">
            🖨 طباعة السند
        </button>
        <a href="{{ route('vouchers.show', $voucher) }}" class="btn btn-back">
            ← رجوع
        </a>
    </div>

    <div class="page">

        {{-- الترويسة --}}
        <div class="header">
            <div>
                <div class="company">🔧 توتال الكلاكلة</div>
                <div class="subtitle">نظام ERP — Total Tools</div>
            </div>
            <div>
                <div class="voucher-type">
                    {{ $voucher->type === 'receipt' ? '📥 سند قبض' : '📤 سند صرف' }}
                </div>
                <div class="voucher-number">#{{ $voucher->voucher_number }}</div>
            </div>
        </div>

        {{-- شريط اللون --}}
        <div class="type-bar {{ $voucher->type === 'receipt' ? 'receipt-bar' : 'payment-bar' }}"></div>

        <div class="body">

            {{-- بطاقة المعلومات الرئيسية --}}
            <div class="main-info">
                <div class="info-item">
                    <div class="info-label">رقم السند</div>
                    <div class="info-value">{{ $voucher->voucher_number }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">التاريخ</div>
                    <div class="info-value">{{ $voucher->date->format('Y/m/d') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">طريقة الدفع</div>
                    <div class="info-value">{{ $voucher->payment_method_label }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">أُعدَّ بواسطة</div>
                    <div class="info-value">{{ $voucher->user?->name ?? '—' }}</div>
                </div>
            </div>

            {{-- مبلغ السند --}}
            <div class="amount-box {{ $voucher->type === 'receipt' ? 'receipt-amount' : 'payment-amount' }}">
                <div class="amount-label">
                    {{ $voucher->type === 'receipt' ? '💰 المبلغ المستلم' : '💸 المبلغ المدفوع' }}
                </div>
                <div class="amount-value">{{ number_format($voucher->amount, 2) }}</div>
                <div class="amount-words">جنيه سوداني</div>
            </div>

            {{-- تفاصيل السند --}}
            <table class="details-table">
                <tr>
                    <td>الحساب المقابل</td>
                    <td>
                        <span style="color:#00838F; font-weight:bold;">{{ $voucher->account->code }}</span>
                        — {{ $voucher->account->name_ar }}
                    </td>
                </tr>
                <tr>
                    <td>حساب الصندوق / البنك</td>
                    <td>
                        <span style="color:#00838F; font-weight:bold;">{{ $voucher->cashAccount->code }}</span>
                        — {{ $voucher->cashAccount->name_ar }}
                    </td>
                </tr>
                @if($voucher->cheque_number)
                <tr>
                    <td>رقم الشيك</td>
                    <td>{{ $voucher->cheque_number }}</td>
                </tr>
                @endif
                @if($voucher->bank_reference)
                <tr>
                    <td>مرجع البنك</td>
                    <td>{{ $voucher->bank_reference }}</td>
                </tr>
                @endif
                @if($voucher->journalEntry)
                <tr>
                    <td>رقم القيد المحاسبي</td>
                    <td>{{ $voucher->journalEntry->entry_number }}</td>
                </tr>
                @endif
            </table>

            {{-- البيان --}}
            <div class="description-box">
                <div class="d-label">البيان / الوصف</div>
                <div class="d-text">{{ $voucher->description }}</div>
            </div>

            @if($voucher->notes)
            <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; padding:10px 14px; margin-bottom:16px;">
                <div style="font-size:10px; color:#6B8C94; margin-bottom:3px;">ملاحظات</div>
                <div style="font-size:12px;">{{ $voucher->notes }}</div>
            </div>
            @endif

            {{-- التوقيعات --}}
            <div class="signatures">
                <div class="sig-box">
                    <div style="height:40px;"></div>
                    <div class="sig-line"></div>
                    <div class="sig-label">{{ $voucher->type === 'receipt' ? 'المستلم' : 'الصارف' }}</div>
                </div>
                <div class="sig-box">
                    <div style="height:40px;"></div>
                    <div class="sig-line"></div>
                    <div class="sig-label">المحاسب</div>
                </div>
                <div class="sig-box">
                    <div style="height:40px;"></div>
                    <div class="sig-line"></div>
                    <div class="sig-label">المدير المالي</div>
                </div>
            </div>

        </div>

        {{-- تذييل السند --}}
        <div class="footer">
            <span><strong>توتال الكلاكلة</strong> — نظام ERP</span>
            <span>طُبع بتاريخ: {{ now()->format('Y/m/d H:i') }}</span>
        </div>

    </div>

</body>
</html>
