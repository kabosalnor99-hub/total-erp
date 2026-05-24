{{-- المسار الكامل: resources/views/reports/general_ledger.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دفتر الأستاذ العام</title>
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
        .num { text-align: left; font-family: monospace; }
        .debit-val  { color: #1d4ed8; }
        .credit-val { color: #065f46; }
        .balance-val { color: #00838F; font-weight: bold; }

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
            <div class="report-title">دفتر الأستاذ العام — General Ledger</div>
        </div>
        <div class="period">
            <div>من: {{ \Carbon\Carbon::parse($data['date_from'])->format('Y/m/d') }}</div>
            <div>إلى: {{ \Carbon\Carbon::parse($data['date_to'])->format('Y/m/d') }}</div>
            <div style="margin-top:4px; opacity:.7;">طُبع: {{ now()->format('Y/m/d H:i') }}</div>
        </div>
    </div>

    {{-- بيانات التقرير --}}
    <div class="meta-row">
        <div><span>عدد الحركات:</span> <strong>{{ count($data['rows']) }} حركة</strong></div>
        @if($data['account_id'])
        @php
        $account = $data['accounts']->firstWhere('id', $data['account_id']);
        @endphp
        @if($account)
        <div><span>الحساب:</span> <strong>{{ $account->code }} — {{ $account->name_ar }}</strong></div>
        @endif
        @endif
    </div>

    {{-- جدول دفتر الأستاذ --}}
    <table>
        <thead>
            <tr>
                <th style="width:12%">التاريخ</th>
                <th style="width:15%">المرجع</th>
                <th style="width:33%">الوصف</th>
                <th style="width:15%">مدين</th>
                <th style="width:15%">دائن</th>
                <th style="width:10%">الرصيد</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['rows'] as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row['date'])->format('Y/m/d') }}</td>
                <td>{{ $row['reference'] }}</td>
                <td>{{ $row['description'] }}</td>
                <td class="num @if($row['debit'] > 0) debit-val @endif">
                    {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}
                </td>
                <td class="num @if($row['credit'] > 0) credit-val @endif">
                    {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}
                </td>
                <td class="num balance-val">{{ number_format($row['balance'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        @if(count($data['rows']) > 0)
        <tfoot>
            <tr>
                <td colspan="5">الرصيد النهائي</td>
                <td class="num">{{ number_format(end($data['rows'])['balance'], 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- التوقيعات --}}
    <div class="signatures">
        <div class="sig-box">المحاسب<br><br>_______________</div>
        <div class="sig-box">المدير المالي<br><br>_______________</div>
        <div class="sig-box">المدير العام<br><br>_______________</div>
    </div>

    {{-- تذييل الصفحة --}}
    <div class="footer">
        <strong>توتال الكلاكلة</strong> — نظام ERP | تقرير دفتر الأستاذ العام | {{ now()->format('Y/m/d H:i') }}
    </div>

</body>
</html>
