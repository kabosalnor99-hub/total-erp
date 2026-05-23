{{-- المسار الكامل: resources/views/pdf/reports/income_statement.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>قائمة الدخل</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1A2E35;
            background: #fff;
            direction: rtl;
        }
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

        /* ── بطاقة النتيجة ── */
        .result-card {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 18px;
            text-align: center;
        }
        .profit-card { background: #d1fae5; border: 2px solid #6ee7b7; }
        .loss-card   { background: #fee2e2; border: 2px solid #fca5a5; }
        .result-label { font-size: 13px; font-weight: bold; margin-bottom: 4px; }
        .result-amount { font-size: 24px; font-weight: bold; }
        .profit-card .result-label  { color: #065f46; }
        .profit-card .result-amount { color: #065f46; }
        .loss-card .result-label    { color: #991b1b; }
        .loss-card .result-amount   { color: #991b1b; }

        /* ── قسم الإيرادات والمصروفات ── */
        .section { margin-bottom: 18px; }
        .section-header {
            padding: 8px 12px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 4px 4px 0 0;
            color: #fff;
        }
        .revenue-header { background: #065f46; }
        .expense-header { background: #991b1b; }

        table { width: 100%; border-collapse: collapse; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody td {
            padding: 7px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        .code-cell { color: #00838F; font-size: 10px; font-weight: bold; }
        .num { text-align: left; font-family: monospace; }

        tfoot tr { font-weight: bold; }
        .revenue-total td { background: #ecfdf5; color: #065f46; border-top: 2px solid #6ee7b7; padding: 9px 12px; }
        .expense-total td { background: #fef2f2; color: #991b1b; border-top: 2px solid #fca5a5; padding: 9px 12px; }

        /* ── ملخص ── */
        .summary-table {
            width: 60%;
            margin: 0 auto 20px;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 9px 14px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }
        .summary-table .total-row td {
            background: #00838F;
            color: #fff;
            font-weight: bold;
            font-size: 13px;
        }

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
            <div class="report-title">قائمة الدخل — Income Statement</div>
        </div>
        <div class="period">
            <div>من: {{ \Carbon\Carbon::parse($fromDate)->format('Y/m/d') }}</div>
            <div>إلى: {{ \Carbon\Carbon::parse($toDate)->format('Y/m/d') }}</div>
            <div style="margin-top:4px; opacity:.7;">طُبع: {{ now()->format('Y/m/d H:i') }}</div>
        </div>
    </div>

    {{-- بطاقة النتيجة --}}
    <div class="result-card {{ $is_profit ? 'profit-card' : 'loss-card' }}">
        <div class="result-label">
            {{ $is_profit ? '✓ صافي الربح' : '✗ صافي الخسارة' }}
        </div>
        <div class="result-amount">
            {{ number_format(abs($net_profit), 2) }} ج.س
        </div>
    </div>

    {{-- قسم الإيرادات --}}
    <div class="section">
        <div class="section-header revenue-header">📈 الإيرادات — Revenues</div>
        <table>
            <tbody>
                @forelse($revenues as $row)
                <tr>
                    <td class="code-cell">{{ $row['account']->code }}</td>
                    <td>{{ $row['account']->name_ar }}</td>
                    <td class="num" style="color:#065f46;">{{ number_format($row['balance'], 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center; color:#9ca3af; padding:12px;">لا توجد إيرادات في هذه الفترة</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="revenue-total">
                    <td colspan="2">إجمالي الإيرادات</td>
                    <td class="num">{{ number_format($total_revenue, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- قسم المصروفات --}}
    <div class="section">
        <div class="section-header expense-header">📉 المصروفات — Expenses</div>
        <table>
            <tbody>
                @forelse($expenses as $row)
                <tr>
                    <td class="code-cell">{{ $row['account']->code }}</td>
                    <td>{{ $row['account']->name_ar }}</td>
                    <td class="num" style="color:#991b1b;">{{ number_format($row['balance'], 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center; color:#9ca3af; padding:12px;">لا توجد مصروفات في هذه الفترة</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="expense-total">
                    <td colspan="2">إجمالي المصروفات</td>
                    <td class="num">{{ number_format($total_expense, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- ملخص النتيجة --}}
    <table class="summary-table">
        <tbody>
            <tr>
                <td>إجمالي الإيرادات</td>
                <td class="num" style="color:#065f46;">{{ number_format($total_revenue, 2) }}</td>
            </tr>
            <tr>
                <td>إجمالي المصروفات</td>
                <td class="num" style="color:#991b1b;">({{ number_format($total_expense, 2) }})</td>
            </tr>
            <tr class="total-row">
                <td>{{ $is_profit ? 'صافي الربح' : 'صافي الخسارة' }}</td>
                <td class="num">{{ number_format(abs($net_profit), 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- التوقيعات --}}
    <div class="signatures">
        <div class="sig-box">المحاسب<br><br>_______________</div>
        <div class="sig-box">المدير المالي<br><br>_______________</div>
        <div class="sig-box">المدير العام<br><br>_______________</div>
    </div>

    <div class="footer">
        <strong>توتال الكلاكلة</strong> — نظام ERP | قائمة الدخل | {{ now()->format('Y/m/d H:i') }}
    </div>

</body>
</html>
