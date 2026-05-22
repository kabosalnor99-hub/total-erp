{{-- المسار الكامل: resources/views/pdf/reports/trial_balance.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ميزان المراجعة</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1A2E35;
            background: #fff;
            direction: rtl;
        }

        /* ── الترويسة ── */
        .header {
            background: #00838F;
            color: #fff;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }
        .header .company { font-size: 18px; font-weight: bold; }
        .header .report-title { font-size: 14px; opacity: .9; margin-top: 4px; }
        .header .period { text-align: left; font-size: 11px; opacity: .85; }

        /* ── بيانات التقرير ── */
        .meta-row {
            display: flex;
            justify-content: space-between;
            background: #F4F7F8;
            border: 1px solid #dde4e6;
            border-radius: 4px;
            padding: 8px 14px;
            margin-bottom: 14px;
            font-size: 11px;
        }
        .meta-row span { color: #6B8C94; }
        .meta-row strong { color: #1A2E35; margin-right: 4px; }

        /* ── الجدول ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        thead tr {
            background: #00838F;
            color: #fff;
        }
        thead th {
            padding: 8px 10px;
            text-align: right;
            font-size: 11px;
            font-weight: bold;
        }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody tr:hover { background: #e8f5f6; }
        tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        .code-cell { color: #00838F; font-weight: bold; font-size: 10px; }
        .num { text-align: left; font-family: monospace; }
        .debit-val  { color: #1d4ed8; }
        .credit-val { color: #065f46; }

        /* ── التذييل الإجمالي ── */
        tfoot tr {
            background: #005F6B;
            color: #fff;
            font-weight: bold;
        }
        tfoot td {
            padding: 9px 10px;
            font-size: 12px;
            border-top: 2px solid #004a55;
        }

        /* ── شارة التوازن ── */
        .balance-badge {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 14px;
        }
        .balanced     { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .not-balanced { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        /* ── التوقيع ── */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            gap: 20px;
        }
        .sig-box {
            flex: 1;
            border-top: 1px solid #ccc;
            padding-top: 6px;
            text-align: center;
            font-size: 10px;
            color: #6B8C94;
        }

        /* ── تذييل الصفحة ── */
        .footer {
            margin-top: 20px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }
        .footer strong { color: #00838F; }
    </style>
</head>
<body>

    {{-- الترويسة --}}
    <div class="header">
        <div>
            <div class="company">🔧 توتال الكلاكلة</div>
            <div class="report-title">ميزان المراجعة — Trial Balance</div>
        </div>
        <div class="period">
            <div>من: {{ \Carbon\Carbon::parse($from_date)->format('Y/m/d') }}</div>
            <div>إلى: {{ \Carbon\Carbon::parse($to_date)->format('Y/m/d') }}</div>
            <div style="margin-top:4px; opacity:.7;">طُبع: {{ now()->format('Y/m/d H:i') }}</div>
        </div>
    </div>

    {{-- بيانات التقرير --}}
    <div class="meta-row">
        <div><span>إجمالي الحسابات:</span> <strong>{{ count($rows) }} حساب</strong></div>
        <div><span>إجمالي المدين:</span> <strong>{{ number_format($total_debit, 2) }} ج.س</strong></div>
        <div><span>إجمالي الدائن:</span> <strong>{{ number_format($total_credit, 2) }} ج.س</strong></div>
        <div><span>الفارق:</span> <strong>{{ number_format(abs($total_debit - $total_credit), 2) }} ج.س</strong></div>
    </div>

    {{-- شارة التوازن --}}
    @if($is_balanced)
        <div class="balance-badge balanced">✓ الميزان متوازن — المدين يساوي الدائن</div>
    @else
        <div class="balance-badge not-balanced">⚠ تحذير: الميزان غير متوازن — الفارق {{ number_format(abs($total_debit - $total_credit), 2) }} ج.س</div>
    @endif

    {{-- جدول الميزان --}}
    <table>
        <thead>
            <tr>
                <th style="width:10%">الكود</th>
                <th style="width:35%">اسم الحساب</th>
                <th style="width:15%">النوع</th>
                <th style="width:20%">المدين</th>
                <th style="width:20%">الدائن</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
            <tr>
                <td class="code-cell">{{ $row['account']->code }}</td>
                <td>{{ $row['account']->name_ar }}</td>
                <td>{{ $row['account']->type_label }}</td>
                <td class="num @if($row['debit'] > 0) debit-val @endif">
                    {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}
                </td>
                <td class="num @if($row['credit'] > 0) credit-val @endif">
                    {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">الإجمالي</td>
                <td class="num">{{ number_format($total_debit, 2) }}</td>
                <td class="num">{{ number_format($total_credit, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- التوقيعات --}}
    <div class="signatures">
        <div class="sig-box">المحاسب<br><br>_______________</div>
        <div class="sig-box">المدير المالي<br><br>_______________</div>
        <div class="sig-box">المدير العام<br><br>_______________</div>
    </div>

    {{-- تذييل الصفحة --}}
    <div class="footer">
        <strong>توتال الكلاكلة</strong> — نظام ERP | تقرير ميزان المراجعة | {{ now()->format('Y/m/d H:i') }}
    </div>

</body>
</html>
