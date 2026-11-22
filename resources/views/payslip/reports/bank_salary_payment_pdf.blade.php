<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bank Salary Payment Report</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-size: 10px;
            font-family: DejaVu Sans, sans-serif;
            color: #000;
        }

        *{
            box-sizing:border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            font-weight: bold;
            background: #efefef;
        }

        .group-title {
            background: #dcdcdc;
            font-weight: bold;
            text-align: left;
            padding: 6px;
        }

        .salary {
            text-align: right;
        }

        .deduction {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            background: #f1f1f1;
        }

        .station-block {
            page-break-after: always;
            width: 100%;
        }

        .station-block:last-child {
            page-break-after: auto;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .mt-5 {
            margin-top: 40px;
        }

        .mb-2 {
            margin-bottom: 10px;
        }

        .signature-wrapper {
            width: 100%;
            margin-top: 40px;
        }

        .signature-box {
            width: 24%;
            display: inline-block;
            vertical-align: top;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            width: 80%;
            margin: 0 auto 10px auto;
            height: 20px;
        }

        .signature-content {
            text-align: left;
            font-size: 10px;
            margin-top: 8px;
            padding: 0 10px;
        }

        .signature-content div {
            margin-bottom: 4px;
        }

        .signature-img {
            width: 80px;
            height: auto;
            margin-top: 5px;
        }
    </style>
</head>

<body>

@php
    $monthLabel = $month->format('F Y');
@endphp

@foreach($reportData as $stationGroup)

    @php
        $station = $stationGroup['station'];

        $stationTotal = [
            'basic' => 0,
            'house_rent' => 0,
            'allowance' => 0,
            'gross' => 0,
            'absent' => 0,
            'late' => 0,
            'loan' => 0,
            'pf' => 0,
            'tax' => 0,
            'deduction' => 0,
            'net' => 0,
        ];

        $sl = 1;

        $lastPayslip = null;
    @endphp

    <div class="station-block">

        {{-- HEADER --}}
        <div class="text-center mb-2">
            <h3 style="margin:0;">Sky Aviation Services Ltd.</h3>
            <h4 style="margin:5px 0;">
                Bank Salary Payment Report - {{ $monthLabel }}
            </h4>
        </div>

        <table>

            {{-- STATION HEADER --}}
            <tr>
                <td colspan="20" class="group-title" style="padding:0;">

                    <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="border-collapse:collapse; border:none;"
                    >
                        <tr>

                            {{-- LEFT --}}
                            <td
                                    style="
                        border:none;
                        text-align:left;
                        padding:6px;
                        font-weight:bold;
                        width:70%;
                    "
                            >
                                Station:
                                {{ $station?->name ?? 'N/A' }}
                            </td>

                            {{-- RIGHT --}}
                            <td
                                    style="
                        border:none;
                        text-align:right;
                        padding:6px;
                        width:30%;
                        font-size:10px;
                        line-height:16px;
                    "
                            >
                                Ref: HR/SR-{{ $month->format('m/Y') }}

                                <br>

                                Date:
                                {{ \Carbon\Carbon::now()->format('d-m-Y') }}
                            </td>

                        </tr>
                    </table>

                </td>
            </tr>

            {{-- TABLE HEADER --}}
            <tr>
                <th rowspan="2">SL</th>
                <th rowspan="2">Staff ID</th>
                <th rowspan="2">Name</th>
                <th rowspan="2">Designation</th>
                <th rowspan="2">Join Date</th>

                <th rowspan="2">Total Working Days</th>
                <th rowspan="2">Present Days</th>
                <th rowspan="2">Grade</th>

                <th colspan="4">Earnings</th>
                <th colspan="6">Deductions</th>

                <th rowspan="2">Net Salary</th>
                <th rowspan="2">Remarks</th>
            </tr>

            <tr>
                <th>Basic</th>
                <th>House Rent</th>
                <th>Allowance</th>
                <th>Gross</th>

                <th>Absent</th>
                <th>Late</th>
                <th>Loan</th>
                <th>PF</th>
                <th>Tax</th>
                <th>Total</th>
            </tr>

            {{-- DEPARTMENTS --}}
            @foreach($stationGroup['departments'] as $departmentGroup)

                @php
                    $department = $departmentGroup['department'];
                @endphp

                <tr>
                    <td colspan="20" class="group-title">
                        Department:
                        {{ $department?->name ?? 'N/A' }}
                    </td>
                </tr>

                {{-- EMPLOYEES --}}
                @foreach($departmentGroup['employees'] as $payslip)

                    @php
                        $lastPayslip = $payslip;

                        $emp = $payslip->employee;

                        $earn = collect($payslip->earn_heads ?? [])->keyBy('name');
                        $deduct = collect($payslip->deductions ?? [])->keyBy('name');

                        $basic = $earn['Basic']['total_amount'] ?? 0;
                        $house = $earn['House Rent']['total_amount'] ?? 0;
                        $allow = $earn['Allowance']['total_amount'] ?? 0;

                        $gross = $payslip->total_earn_heads ?? 0;

                        $absent = $deduct['Absent']['total_amount'] ?? 0;
                        $late = $deduct['Late']['total_amount'] ?? 0;
                        $loan = $deduct['Personal Loan']['total_amount'] ?? 0;
                        $pf = $deduct['PF']['total_amount'] ?? 0;
                        $tax = $deduct['Income Tax']['total_amount'] ?? 0;

                        $totalDed = $payslip->total_deductions ?? 0;
                        $net = $payslip->total_salary ?? 0;

                        $stationTotal['basic'] += $basic;
                        $stationTotal['house_rent'] += $house;
                        $stationTotal['allowance'] += $allow;
                        $stationTotal['gross'] += $gross;

                        $stationTotal['absent'] += $absent;
                        $stationTotal['late'] += $late;
                        $stationTotal['loan'] += $loan;
                        $stationTotal['pf'] += $pf;
                        $stationTotal['tax'] += $tax;
                        $stationTotal['deduction'] += $totalDed;
                        $stationTotal['net'] += $net;
                    @endphp

                    <tr>
                        <td>{{ $sl++ }}</td>

                        <td>
                            {{ $emp?->employee_id }}
                        </td>

                        <td class="text-left">
                            {{ $emp?->name }}
                        </td>

                        <td>
                            {{ $emp?->designation?->name }}
                        </td>

                        <td>
                            {{ $emp?->join_date
                                ? \Carbon\Carbon::parse($emp->join_date)->format('d-m-Y')
                                : '' }}
                        </td>

                        <td>
                            {{ $payslip->ssbd_total_attendance ?? 0 }}
                        </td>

                        <td>
                            {{ $payslip->working_days ?? 0 }}
                        </td>

                        <td>
                            {{ $payslip->grade_name ?? 'N/A' }}
                        </td>

                        {{-- EARNINGS --}}
                        <td class="salary">
                            {{ number_format($basic, 2) }}
                        </td>

                        <td class="salary">
                            {{ number_format($house, 2) }}
                        </td>

                        <td class="salary">
                            {{ number_format($allow, 2) }}
                        </td>

                        <td class="salary">
                            {{ number_format($gross, 2) }}
                        </td>

                        {{-- DEDUCTIONS --}}
                        <td class="deduction">
                            {{ number_format($absent, 2) }}
                        </td>

                        <td class="deduction">
                            {{ number_format($late, 2) }}
                        </td>

                        <td class="deduction">
                            {{ number_format($loan, 2) }}
                        </td>

                        <td class="deduction">
                            {{ number_format($pf, 2) }}
                        </td>

                        <td class="deduction">
                            {{ number_format($tax, 2) }}
                        </td>

                        <td class="deduction">
                            {{ number_format($totalDed, 2) }}
                        </td>

                        <td class="salary">
                            {{ number_format($net, 2) }}
                        </td>

                        <td>-</td>
                    </tr>

                @endforeach

            @endforeach

            {{-- TOTAL --}}
            <tr class="total-row">

                <td colspan="8">
                    TOTAL
                </td>

                <td class="salary">
                    {{ number_format($stationTotal['basic'],2) }}
                </td>

                <td class="salary">
                    {{ number_format($stationTotal['house_rent'],2) }}
                </td>

                <td class="salary">
                    {{ number_format($stationTotal['allowance'],2) }}
                </td>

                <td class="salary">
                    {{ number_format($stationTotal['gross'],2) }}
                </td>

                <td class="deduction">
                    {{ number_format($stationTotal['absent'],2) }}
                </td>

                <td class="deduction">
                    {{ number_format($stationTotal['late'],2) }}
                </td>

                <td class="deduction">
                    {{ number_format($stationTotal['loan'],2) }}
                </td>

                <td class="deduction">
                    {{ number_format($stationTotal['pf'],2) }}
                </td>

                <td class="deduction">
                    {{ number_format($stationTotal['tax'],2) }}
                </td>

                <td class="deduction">
                    {{ number_format($stationTotal['deduction'],2) }}
                </td>

                <td class="salary">
                    {{ number_format($stationTotal['net'],2) }}
                </td>

                <td>-</td>

            </tr>

        </table>

        {{-- SIGNATURE SECTION --}}
        @if($lastPayslip)

            @php
                $preparedUser = $lastPayslip->getStageUser('payslip_prepare', $status);
                $prepared = $lastPayslip->getStageApproval('payslip_prepare', $status);

                $checkedUser = $lastPayslip->getStageUser('payslip_accounts_check', $status);
                $checked = $lastPayslip->getStageApproval('payslip_accounts_check', $status);

                $verifiedUser = $lastPayslip->getStageUser('payslip_finance_verify', $status);
                $verified = $lastPayslip->getStageApproval('payslip_finance_verify', $status);

                $mdApproval = $lastPayslip->getStageApproval('payslip_md_approve', $status);
                $mdUser = $mdApproval?->reviewer ?? $mdApproval?->reviewedByUser;
            @endphp

            <div class="signature-wrapper">

                {{-- Prepared --}}
                <div class="signature-box">

                    <div class="signature-line"></div>

                    <strong>Prepared By</strong>

                    <div class="signature-content">

                        @if(!empty($preparedUser?->signature_file))
                            <img
                                    class="signature-img"
                                    src="{{ public_path('uploaded_files/'.$preparedUser->signature_file) }}"
                            >
                        @endif

                        <div>
                            <strong>Name:</strong>
                            {{ $preparedUser?->name }}
                        </div>

                        <div>
                            <strong>Staff ID:</strong>
                            {{ $preparedUser?->employee_id }}
                        </div>

                        <div>
                            <strong>Date:</strong>
                            {{ $prepared?->created_at?->format('d-m-Y h:i A') }}
                        </div>

                    </div>
                </div>

                {{-- Checked --}}
                <div class="signature-box">

                    <div class="signature-line"></div>

                    <strong>Checked By</strong>

                    <div class="signature-content">

                        @if(!empty($checkedUser?->signature_file))
                            <img
                                    class="signature-img"
                                    src="{{ public_path('uploaded_files/'.$checkedUser->signature_file) }}"
                            >
                        @endif

                        <div>
                            <strong>Name:</strong>
                            {{ $checkedUser?->name }}
                        </div>

                        <div>
                            <strong>Staff ID:</strong>
                            {{ $checkedUser?->employee_id }}
                        </div>

                        <div>
                            <strong>Date:</strong>
                            {{ $checked?->created_at?->format('d-m-Y h:i A') }}
                        </div>

                    </div>
                </div>

                {{-- Verified --}}
                <div class="signature-box">

                    <div class="signature-line"></div>

                    <strong>Verified By</strong>

                    <div class="signature-content">

                        @if(!empty($verifiedUser?->signature_file))
                            <img
                                    class="signature-img"
                                    src="{{ public_path('uploaded_files/'.$verifiedUser->signature_file) }}"
                            >
                        @endif

                        <div>
                            <strong>Name:</strong>
                            {{ $verifiedUser?->name }}
                        </div>

                        <div>
                            <strong>Staff ID:</strong>
                            {{ $verifiedUser?->employee_id }}
                        </div>

                        <div>
                            <strong>Date:</strong>
                            {{ $verified?->created_at?->format('d-m-Y h:i A') }}
                        </div>



                    </div>
                </div>

                {{-- Approved --}}
                <div class="signature-box">

                    <div class="signature-line"></div>

                    <strong>Approved By (MD)</strong>

                    <div class="signature-content">

                        @if(!empty($mdUser?->signature_file))
                            <img
                                    class="signature-img"
                                    src="{{ public_path('uploaded_files/'.$mdUser->signature_file) }}"
                            >
                        @endif

                        <div>
                            <strong>Name:</strong>
                            {{ $mdUser?->name }}
                        </div>

                        <div>
                            <strong>Staff ID:</strong>
                            {{ $mdUser?->employee_id }}
                        </div>

                        <div>
                            <strong>Date:</strong>
                            {{ $mdApproval?->created_at?->format('d-m-Y h:i A') }}
                        </div>

                    </div>
                </div>

            </div>

        @endif

    </div>

@endforeach

</body>
</html>