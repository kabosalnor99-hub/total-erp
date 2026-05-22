{{-- المسار الكامل: resources/views/pdf/payslip.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>قسيمة راتب — {{ $payroll->employee->employee_number }} — {{ $payroll->month_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 12px;
            color: #1a2e35;
            background: #fff;
            direction: rtl;
        }
        .page { padding: 25px 30px; }

        /* Header */
        .header {
            display: table;
            width: 100%;
            border-bottom: 3px solid #00838F;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .header-right { display: table-cell; width: 60%; vertical-align: middle; }
        .header-left  { display: table-cell; width: 40%; vertical-align: middle; text-align: left; }

        .logo-circle {
            display: inline-block; width: 48px; height: 48px;
            background: #00838F; border-radius: 50%;
            text-align: center; line-height: 48px;
            color: #fff; font-size: 20px; font-weight: 700;
            vertical-align: middle;
        }
        .company-text { display: inline-block; vertical-align: middle; margin-right: 10px; }
        .company-text h1 { font-size: 17px; font-weight: 700; color: #00838F; }
        .company-text p  { font-size: 10px; color: #6b8c94; margin-top: 2px; }

        .slip-title  { font-size: 17px; font-weight: 700; color: #1a2e35; }
        .slip-period { font-size: 13px; color: #00838F; font-weight: 600; margin-top: 3px; }
        .slip-status { font-size: 10px; color: #6b8c94; margin-top: 3px; }

        /* Employee Info */
        .emp-section {
            background: #f4f7f8;
            border: 1px solid #d0e4e7;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 16px;
        }
        .emp-section h3 { font-size: 10px; font-weight: 700; color: #00838F; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.4px; }
        .emp-grid { display: table; width: 100%; }
        .emp-col  { display: table-cell; width: 33.33%; vertical-align: top; padding-left: 10px; }
        .emp-label { font-size: 9px; color: #6b8c94; margin-bottom: 2px; }
        .emp-value { font-size: 12px; font-weight: 600; color: #1a2e35; }

        /* Salary Table */
        .section-title {
            font-size: 11px; font-weight: 700; color: #fff;
            background: #00838F; padding: 6px 12px;
            border-radius: 4px 4px 0 0;
            margin-top: 14px;
        }
        .salary-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .salary-table th {
            background: #005f6b;
            color: #fff;
            padding: 7px 10px;
            font-size: 10px;
            font-weight: 600;
            text-align: right;
        }
        .salary-table td {
            padding: 7px 10px;
            font-size: 11px;
            border-bottom: 1px solid #e8eef0;
            color: #1a2e35;
        }
        .salary-table tr:nth-child(even) td { background: #f4f7f8; }
        .salary-table .amount { text-align: left; font-weight: 600; }
        .salary-table .total-row td {
            background: #e8f5f6;
            font-weight: 700;
            font-size: 12px;
            border-top: 2px solid #00838F;
        }

        /* Two columns side by side */
        .two-col { display: table; width: 100%; border-collapse: separate; border-spacing: 8px 0; margin-bottom: 0; }
        .col-half { display: table-cell; width: 50%; vertical-align: top; }

        /* Net salary box */
        .net-box {
            background: #00838F;
            color: #fff;
            border-radius: 6px;
            padding: 14px 18px;
            text-align: center;
            margin-top: 16px;
        }
        .net-box .label { font-size: 11px; opacity: 0.85; margin-bottom: 4px; }
        .net-box .amount { font-size: 22px; font-weight: 700; }
        .net-box .currency { font-size: 13px; opacity: 0.85; margin-top: 2px; }

        /* Attendance box */
        .attendance-row { display: table; width: 100%; margin-top: 14px; }
        .att-item { display: table-cell; text-align: center; vertical-align: top; }
        .att-num  { font-size: 20px; font-weight: 700; color: #00838F; }
        .att-lbl  { font-size: 9px; color: #6b8c94; margin-top: 2px; }
        .att-sep  { display: table-cell; width: 1px; background: #e0e0e0; }

        /* Footer */
        .footer {
            margin-top: 20px;
            border-top: 1px solid #e0e0e0;
            padding-top: 10px;
            display: table;
            width: 100%;
        }
        .footer-left  { display: table-cell; width: 50%; vertical-align: bottom; font-size: 9px; color: #6b8c94; }
        .footer-right { display: table-cell; width: 50%; text-align: right; }
        .sign-box { display: inline-block; border-top: 1px solid #1a2e35; padding-top: 4px; font-size: 10px; color: #1a2e35; min-width: 150px; text-align: center; }

        /* Status badges */
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }
        .badge-draft    { background: #f5f5f5; color: #616161; }
        .badge-approved { background: #e3f2fd; color: #1565c0; }
        .badge-paid     { background: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
<div class="page">

    {{-- ─── الترويسة ──────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-right">
            <span class="logo-circle">ت</span>
            <span class="company-text">
                <h1>توتال الكلاكلة</h1>
                <p>توزيع وبيع أدوات كهربائية ومعدات</p>
            </span>
        </div>
        <div class="header-left">
            <div class="slip-title">قسيمة الراتب</div>
            <div class="slip-period">{{ $payroll->month_name }}</div>
            <div class="slip-status">
                <span class="badge badge-{{ $payroll->status }}">{{ $payroll->status_label }}</span>
                &nbsp;
                تاريخ الإصدار: {{ now()->format('Y/m/d') }}
            </div>
        </div>
    </div>

    {{-- ─── بيانات الموظف ──────────────────────────────────────────── --}}
    <div class="emp-section">
        <h3>بيانات الموظف</h3>
        <div class="emp-grid">
            <div class="emp-col">
                <div class="emp-label">الاسم</div>
                <div class="emp-value">{{ $payroll->employee->name }}</div>
            </div>
            <div class="emp-col">
                <div class="emp-label">رقم الموظف</div>
                <div class="emp-value">{{ $payroll->employee->employee_number }}</div>
            </div>
            <div class="emp-col">
                <div class="emp-label">المسمى الوظيفي</div>
                <div class="emp-value">{{ $payroll->employee->job_title }}</div>
            </div>
        </div>
        <br>
        <div class="emp-grid">
            <div class="emp-col">
                <div class="emp-label">القسم</div>
                <div class="emp-value">{{ $payroll->employee->department?->name ?? '—' }}</div>
            </div>
            <div class="emp-col">
                <div class="emp-label">تاريخ التعيين</div>
                <div class="emp-value">{{ $payroll->employee->hire_date?->format('Y/m/d') ?? '—' }}</div>
            </div>
            <div class="emp-col">
                <div class="emp-label">طريقة الدفع</div>
                <div class="emp-value">{{ $payroll->payment_method ?? 'تحويل بنكي' }}</div>
            </div>
        </div>
    </div>

    {{-- ─── الحضور ─────────────────────────────────────────────────── --}}
    <div class="attendance-row">
        <div class="att-item">
            <div class="att-num">{{ $payroll->working_days }}</div>
            <div class="att-lbl">أيام العمل</div>
        </div>
        <div class="att-sep"></div>
        <div class="att-item">
            <div class="att-num" style="color:#43a047">{{ $payroll->working_days - $payroll->absent_days }}</div>
            <div class="att-lbl">أيام الحضور</div>
        </div>
        <div class="att-sep"></div>
        <div class="att-item">
            <div class="att-num" style="color:#e53935">{{ $payroll->absent_days }}</div>
            <div class="att-lbl">أيام الغياب</div>
        </div>
        <div class="att-sep"></div>
        <div class="att-item">
            <div class="att-num" style="color:#fb8c00">{{ $payroll->late_minutes }}</div>
            <div class="att-lbl">دقائق التأخير</div>
        </div>
        <div class="att-sep"></div>
        <div class="att-item">
            <div class="att-num" style="color:#00838F">{{ $payroll->overtime_hours }}</div>
            <div class="att-lbl">ساعات إضافية</div>
        </div>
    </div>

    {{-- ─── الدخل والخصومات جنباً إلى جنب ──────────────────────────── --}}
    <div class="two-col" style="margin-top:16px;">

        {{-- الدخل --}}
        <div class="col-half">
            <div class="section-title">الدخل والبدلات</div>
            <table class="salary-table">
                <thead>
                    <tr>
                        <th>البند</th>
                        <th style="text-align:left">المبلغ (جنيه)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>الراتب الأساسي</td>
                        <td class="amount">{{ number_format($payroll->basic_salary, 2) }}</td>
                    </tr>
                    @if($payroll->housing_allowance > 0)
                    <tr>
                        <td>بدل السكن</td>
                        <td class="amount">{{ number_format($payroll->housing_allowance, 2) }}</td>
                    </tr>
                    @endif
                    @if($payroll->transport_allowance > 0)
                    <tr>
                        <td>بدل المواصلات</td>
                        <td class="amount">{{ number_format($payroll->transport_allowance, 2) }}</td>
                    </tr>
                    @endif
                    @if($payroll->food_allowance > 0)
                    <tr>
                        <td>بدل الغذاء</td>
                        <td class="amount">{{ number_format($payroll->food_allowance, 2) }}</td>
                    </tr>
                    @endif
                    @if($payroll->other_allowances > 0)
                    <tr>
                        <td>بدلات أخرى</td>
                        <td class="amount">{{ number_format($payroll->other_allowances, 2) }}</td>
                    </tr>
                    @endif
                    @if($payroll->overtime_amount > 0)
                    <tr>
                        <td>أجر إضافي</td>
                        <td class="amount">{{ number_format($payroll->overtime_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($payroll->bonus > 0)
                    <tr>
                        <td>مكافأة</td>
                        <td class="amount">{{ number_format($payroll->bonus, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td>إجمالي الدخل</td>
                        <td class="amount">{{ number_format($payroll->gross_salary, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- الخصومات --}}
        <div class="col-half">
            <div class="section-title" style="background:#005f6b;">الخصومات</div>
            <table class="salary-table">
                <thead>
                    <tr>
                        <th>البند</th>
                        <th style="text-align:left">المبلغ (جنيه)</th>
                    </tr>
                </thead>
                <tbody>
                    @if($payroll->absence_deduction > 0)
                    <tr>
                        <td>خصم الغياب</td>
                        <td class="amount" style="color:#e53935">{{ number_format($payroll->absence_deduction, 2) }}</td>
                    </tr>
                    @endif
                    @if($payroll->late_deduction > 0)
                    <tr>
                        <td>خصم التأخير</td>
                        <td class="amount" style="color:#e53935">{{ number_format($payroll->late_deduction, 2) }}</td>
                    </tr>
                    @endif
                    @if($payroll->social_insurance > 0)
                    <tr>
                        <td>التأمين الاجتماعي</td>
                        <td class="amount" style="color:#e53935">{{ number_format($payroll->social_insurance, 2) }}</td>
                    </tr>
                    @endif
                    @if($payroll->tax_deduction > 0)
                    <tr>
                        <td>الضريبة</td>
                        <td class="amount" style="color:#e53935">{{ number_format($payroll->tax_deduction, 2) }}</td>
                    </tr>
                    @endif
                    @if($payroll->advance_deduction > 0)
                    <tr>
                        <td>خصم السلفة</td>
                        <td class="amount" style="color:#e53935">{{ number_format($payroll->advance_deduction, 2) }}</td>
                    </tr>
                    @endif
                    @if($payroll->other_deductions > 0)
                    <tr>
                        <td>خصومات أخرى</td>
                        <td class="amount" style="color:#e53935">{{ number_format($payroll->other_deductions, 2) }}</td>
                    </tr>
                    @endif
                    @if($payroll->total_deductions == 0)
                    <tr>
                        <td colspan="2" style="text-align:center; color:#6b8c94; font-style:italic;">لا توجد خصومات</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td>إجمالي الخصومات</td>
                        <td class="amount" style="color:#e53935">{{ number_format($payroll->total_deductions, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    {{-- ─── صافي الراتب ────────────────────────────────────────────── --}}
    <div class="net-box">
        <div class="label">صافي الراتب المستحق عن {{ $payroll->month_name }}</div>
        <div class="amount">{{ number_format($payroll->net_salary, 2) }}</div>
        <div class="currency">جنيه سوداني</div>
    </div>

    {{-- ─── ملاحظات ────────────────────────────────────────────────── --}}
    @if($payroll->notes)
    <div style="margin-top:14px; padding:10px; background:#fffde7; border:1px solid #ffe082; border-radius:4px; font-size:11px;">
        <strong>ملاحظات:</strong> {{ $payroll->notes }}
    </div>
    @endif

    {{-- ─── التوقيعات ──────────────────────────────────────────────── --}}
    <div class="footer">
        <div class="footer-left">
            <p>صدرت بواسطة: {{ $payroll->createdBy?->name ?? 'النظام' }}</p>
            @if($payroll->payment_date)
            <p>تاريخ الدفع: {{ $payroll->payment_date->format('Y/m/d') }}</p>
            @endif
            <p style="margin-top:6px; color:#9e9e9e;">هذه الوثيقة صادرة إلكترونياً من نظام توتال ERP</p>
        </div>
        <div class="footer-right">
            <div style="display:inline-block; margin-left:30px;">
                <div class="sign-box">توقيع المدير المالي</div>
            </div>
            <div style="display:inline-block;">
                <div class="sign-box">توقيع الموظف</div>
            </div>
        </div>
    </div>

</div>
</body>
</html>
