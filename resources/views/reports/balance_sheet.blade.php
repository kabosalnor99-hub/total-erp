{{-- المسار الكامل: resources/views/pdf/reports/balance_sheet.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الميزانية العمومية</title>
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

        /* ── بطاقة التوازن ── */
        .balance-info {
            display: flex;
            gap: 12px;
            margin-bottom: 18px;
        }
        .info-box {
            flex: 1;
            padding: 12px 16px;
            border-radius: 6px;
            text-align: center;
        }
        .assets-box      { background: #dbeafe; border: 1px solid #93c5fd; }
        .liabilities-box { background: #fef3c7; border: 1px solid #fcd34d; }
        .equity-box      { background: #d1fae5; border: 1px solid #6ee7b7; }
        .info-box .box-label  { font-size: 11px; color: #6B8C94; margin-bottom: 4px; }
        .info-box .box-amount { font-size: 16px; font-weight: bold; }
        .assets-box .box-amount      { color: #1d4ed8; }
        .liabilities-box .box-amount { color: #92400e; }
        .equity-box .box-amount      { color: #065f46; }

        /* ── التوازن ── */
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

        /* ── الأعمدة ── */
        .two-col {
            display: flex;
            gap: 14px;
            margin-bottom: 14px;
        }
        .col { flex: 1; }

        .section-header {
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
            border-radius: 4px 4px 0 0;
        }
        .assets-header      { background: #1d4ed8; }
        .liabilities-header { background: #92400e; }
        .equity-header      { background: #065f46; }

        table { width: 100%; border-collapse: collapse; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody td {
            padding: 6px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        .code-cell { color: #00838F; font-size: 9px; font-weight: bold; }
        .num { text-align: left; font-family: monospace; }

        tfoot tr { font-weight: bold; }
        .assets-total td      { background: #dbeafe; color: #1d4ed8; border-top: 2px solid #93c5fd; padding: 8px 10px; }
        .liabilities-total td { background: #fef3c7; color: #92400e; border-top: 2px solid #fcd34d; padding: 8px 10px; }
        .equity-total td      { background: #d1fae5; color: #065f46; border-top: 2px solid #6ee7b7; padding: 8px 10px; }

        /* ── ملخص ── */
        .equation-box {
            background: #f4f7f8;
            border: 1px solid #dde4e6;
            border-radius: 6px;
            padding: 12px 16px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .equation-box .eq-label { color: #6B8C94; margin-bottom: 6px; }
        .equation-box .eq-text  { font-size: 14px; font-weight: bold; color: #005F6B; }

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
            <div class="report-title">الميزانية العمومية — Balance Sheet</div>
        </div>
        <div class="period">
            <div>بتاريخ: {{ \Carbon\Carbon::parse($to_date)->format('Y/m/d') }}</div>
            <div style="margin-top:4px; opacity:.7;">طُبع: {{ now()->format('Y/m/d H:i') }}</div>
        </div>
    </div>

    {{-- بطاقات الإجماليات --}}
    <div class="balance-info">
        <div class="info-box assets-box">
            <div class="box-label">إجمالي الأصول</div>
            <div class="box-amount">{{ number_format($total_assets, 2) }} ج.س</div>
        </div>
        <div class="info-box liabilities-box">
            <div class="box-label">إجمالي الخصوم</div>
            <div class="box-amount">{{ number_format($total_liabilities, 2) }} ج.س</div>
        </div>
        <div class="info-box equity-box">
            <div class="box-label">حقوق الملكية</div>
            <div class="box-amount">{{ number_format($total_equity, 2) }} ج.س</div>
        </div>
    </div>

    {{-- شارة التوازن --}}
    @if($is_balanced)
        <div class="balance-badge balanced">✓ الميزانية متوازنة — الأصول = الخصوم + حقوق الملكية</div>
    @else
        <div class="balance-badge not-balanced">⚠ تحذير: الميزانية غير متوازنة</div>
    @endif

    {{-- معادلة الميزانية --}}
    <div class="equation-box">
        <div class="eq-label">معادلة الميزانية العمومية</div>
        <div class="eq-text">
            الأصول ({{ number_format($total_assets, 2) }})
            =
            الخصوم ({{ number_format($total_liabilities, 2) }})
            +
            حقوق الملكية ({{ number_format($total_equity, 2) }})
        </div>
    </div>

    {{-- عمودان: الأصول | الخصوم وحقوق الملكية --}}
    <div class="two-col">

        {{-- عمود الأصول --}}
        <div class="col">
            <div class="section-header assets-header">الأصول — Assets</div>
            <table>
                <tbody>
                    @forelse($assets as $row)
                    <tr>
                        <td class="code-cell">{{ $row['account']->code }}</td>
                        <td>{{ $row['account']->name_ar }}</td>
                        <td class="num" style="color:#1d4ed8;">{{ number_format($row['balance'], 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center; color:#9ca3af; padding:10px;">لا توجد أصول</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="assets-total">
                        <td colspan="2">إجمالي الأصول</td>
                        <td class="num">{{ number_format($total_assets, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- عمود الخصوم وحقوق الملكية --}}
        <div class="col">
            <div class="section-header liabilities-header">الخصوم — Liabilities</div>
            <table>
                <tbody>
                    @forelse($liabilities as $row)
                    <tr>
                        <td class="code-cell">{{ $row['account']->code }}</td>
                        <td>{{ $row['account']->name_ar }}</td>
                        <td class="num" style="color:#92400e;">{{ number_format($row['balance'], 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center; color:#9ca3af; padding:10px;">لا توجد خصوم</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="liabilities-total">
                        <td colspan="2">إجمالي الخصوم</td>
                        <td class="num">{{ number_format($total_liabilities, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div style="margin-top:10px;">
                <div class="section-header equity-header">حقوق الملكية — Equity</div>
                <table>
                    <tbody>
                        @forelse($equity as $row)
                        <tr>
                            <td class="code-cell">{{ $row['account']->code }}</td>
                            <td>{{ $row['account']->name_ar }}</td>
                            <td class="num" style="color:#065f46;">{{ number_format($row['balance'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align:center; color:#9ca3af; padding:10px;">لا توجد حقوق ملكية</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="equity-total">
                            <td colspan="2">إجمالي حقوق الملكية</td>
                            <td class="num">{{ number_format($total_equity, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

    {{-- التوقيعات --}}
    <div class="signatures">
        <div class="sig-box">المحاسب<br><br>_______________</div>
        <div class="sig-box">المدير المالي<br><br>_______________</div>
        <div class="sig-box">المدير العام<br><br>_______________</div>
    </div>

    <div class="footer">
        <strong>توتال الكلاكلة</strong> — نظام ERP | الميزانية العمومية | {{ now()->format('Y/m/d H:i') }}
    </div>

</body>
</html>
