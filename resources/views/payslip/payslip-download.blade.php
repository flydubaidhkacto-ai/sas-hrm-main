<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip</title>

    <style>

        @page {
            margin: 10mm;
            size: A4;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #2b2b2b;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }

        /* ================= HEADER ================= */
        .top-header {
            width: 100%;
            margin-bottom: 10px;
        }

        .company {
            font-size: 20px;
            font-weight: bold;
            color: #714B67;
        }

        .sub-title {
            color: #666;
            font-size: 10px;
            line-height: 1.4;
        }

        .payslip-title {
            font-size: 16px;
            font-weight: bold;
            text-align: right;
        }

        /* ================= CARD ================= */
        .card {
            border: 1px solid #e5e5e5;
            border-radius: 5px;
            padding: 8px;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 6px;
            color: #714B67;
            border-bottom: 1px solid #eee;
            padding-bottom: 4px;
        }

        /* ================= TABLE ================= */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            padding: 4px;
        }

        .info-table td {
            border-bottom: 1px solid #f2f2f2;
        }

        .salary-table th {
            background: #714B67;
            color: #fff;
            text-align: left;
            padding: 5px;
        }

        .salary-table td {
            border-bottom: 1px solid #eee;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        /* ================= ATTENDANCE BOX ================= */
        .attendance-wrapper {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .summary-box {
            display: table-cell;
            width: 25%;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
        }

        .summary-title {
            font-size: 10px;
            color: #777;
        }

        .summary-value {
            font-size: 13px;
            font-weight: bold;
            margin-top: 3px;
        }

        /* ================= SALARY ================= */
        .earn {
            background: #f7fff8;
        }

        .deduct {
            background: #fff7f7;
        }

        .net-salary {
            background: #714B67;
            color: #fff;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
            margin-top: 10px;
        }

        .net-salary h2 {
            margin: 0;
            font-size: 22px;
        }

        /* ================= REMARKS ================= */
        .remarks {
            margin-top: 8px;
            padding: 8px;
            background: #fafafa;
            border-left: 3px solid #714B67;
        }

        /* ================= SIGNATURE ================= */
        .signature {
            margin-top: 20px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }

        .signature div {
            width: 40%;
        }

        /* ================= FIX PAGE BREAK ISSUE ================= */
        tr, td, .card {
            page-break-inside: avoid !important;
        }

    </style>
</head>

<body>

<!-- ================= HEADER ================= -->
<table class="top-header">
    <tr>
        <td>
            <div class="company">
                Sky Aviation Services Ltd.
            </div>

            <div class="sub-title">
                Bashati Avenue, Plot#10, Road#53,
                Gulshan Avenue, Gulshan-2, Dhaka-1212<br>
                Phone: +88 02 58817811-20 | Email: RES.DAC@flydubai.com
            </div>
        </td>

        <td class="right">
            <div class="payslip-title">PAYSLIP</div>

            <div class="sub-title">
                {{ $month->format('F Y') }}<br>
                Generated: {{ \Carbon\Carbon::now()->format('d M Y, h:i A') }}<br>
                By: {{ auth()->user()->name ?? 'System' }}
            </div>
        </td>
    </tr>
</table>

<!-- ================= EMPLOYEE INFO ================= -->
<div class="card">
    <div class="section-title">Employee Information</div>

    <table class="info-table">
        <tr>
            <td><b>Name</b></td>
            <td>{{ $employee->name }}</td>

            <td><b>ID</b></td>
            <td>{{ $employee->id }}</td>

            <td><b>Type</b></td>
            <td>{{ $employee->employee_type }}</td>
        </tr>

        <tr>
            <td><b>Department</b></td>
            <td>{{ $employee->department->name ?? '-' }}</td>

            <td><b>Designation</b></td>
            <td>{{ $employee->designation->name ?? '-' }}</td>

            <td><b>Pay Type</b></td>
            <td>{{ ucfirst($payslip->pay_type) }}</td>
        </tr>
    </table>
</div>

<!-- ================= ATTENDANCE ================= -->
<div class="card">
    <div class="section-title">Attendance Summary</div>

    <div class="attendance-wrapper">

        <div class="summary-box">
            <div class="summary-title">Total Working Days</div>
            <div class="summary-value">{{ $summary['total_working_days'] }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-title">Present</div>
            <div class="summary-value">{{ $summary['present_days'] }}</div>
        </div>

        <div class="summary-box">
            <div class="summary-title">Late</div>
            <div class="summary-value">{{ $summary['total_late_days'] }}</div>
        </div>

        <div class="summary-box">
            <div class="summary-title">Absent</div>
            <div class="summary-value">{{ $summary['total_absent_days'] }}</div>
        </div>

        <div class="summary-box">
            <div class="summary-title">Hours</div>
            <div class="summary-value">{{ $summary['worked_hours'] }}</div>
        </div>

    </div>
</div>

<!-- ================= SALARY ================= -->
<div class="card">
    <div class="section-title">Salary Breakdown</div>

    <table class="salary-table">

        <tr class="earn">
            <td colspan="2" class="bold">Earnings</td>
        </tr>

        @foreach($earn_heads as $head)
            <tr>
                <td>{{ $head['name'] }}</td>
                <td class="right">{{ number_format($head['total_amount'] ?? 0, 2) }}</td>
            </tr>
        @endforeach

        <tr>
            <td class="bold">Gross Salary</td>
            <td class="right bold">{{ number_format($summary['gross_salary'], 2) }}</td>
        </tr>

        <tr class="deduct">
            <td colspan="2" class="bold">Deductions</td>
        </tr>

        @foreach($deductions as $deduction)
            <tr>
                <td>{{ $deduction['name'] }}</td>
                <td class="right">{{ number_format($deduction['total_amount'] ?? 0, 2) }}</td>
            </tr>
        @endforeach

        <tr>
            <td class="bold">Total Deductions</td>
            <td class="right bold">{{ number_format($summary['total_deductions'], 2) }}</td>
        </tr>

    </table>

    <!-- NET SALARY -->
    <div class="net-salary">
        <div>NET SALARY</div>
        <h2>{{ number_format($summary['net_salary'], 2) }} Tk</h2>
        <div>{{ number_to_words($summary['net_salary']) }} Taka Only</div>
    </div>

    @if(!empty($summary['remarks']))
        <div class="remarks">
            <b>Remarks:</b><br>
            {{ $summary['remarks'] }}
        </div>
    @endif
</div>

<!-- ================= SIGNATURE ================= -->
<div class="signature">
    <div>
        ______________________<br>
        HR Signature
    </div>
    <br>
    <div>
        ______________________<br>
        MD Signature
    </div>
</div>

</body>
</html>