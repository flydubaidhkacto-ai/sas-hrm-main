<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bank Salary Payment Report</title>

    <style>
        @page {
            size: A3 landscape;
            margin: 8mm;
        }

        body {
            font-size: 11px;
            font-family: Arial, sans-serif;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            word-wrap: break-word;
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
        }

        .group-title {
            background: #e9ecef;
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
            background: #f5f5f5;
        }

        .station-block {
            page-break-after: always;
        }

        .station-block:last-child {
            page-break-after: auto;
        }

        .signature-wrapper {
            width: 100%;
            margin-top: 35px;
            font-size: 0;
        }

        .signature-box {
            width: 24%;
            display: inline-block;
            vertical-align: top;
            text-align: center;
            font-size: 10px;
            margin-right: 1%;
        }

        .signature-box:last-child {
            margin-right: 0;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 25px;
            margin-bottom: 8px;
        }

        .signature-content {
            text-align: left;
            font-size: 10px;
        }

        .signature-content div {
            margin-bottom: 4px;
        }

        img {
            max-width: 80px;
            max-height: 50px;
            object-fit: contain;
        }
    </style>
</head>

<body>

@php
    $monthLabel = $month->format('F Y');
    $monthShort = $month->format('M');   // Apr
    $yearShort = $month->format('y');    // 26

@endphp

<div class="station-block">

    {{-- HEADER --}}
    <div style="text-align:center; margin-bottom:10px;">
        <h3 style="margin:0;">Sky Aviation Services Ltd.</h3>
        <h4 style="margin:5px 0;">
            Bank Salary Payment For The Month of {{ $monthLabel }}
        </h4>
    </div>

    @foreach($reportData as $stationGroup)

        @php
            $station = $stationGroup['station'];

            $stationTotal = [
                'basic'=>0,'house_rent'=>0,'allowance'=>0,'gross'=>0,
                'absent'=>0,'late'=>0,'loan'=>0,'pf'=>0,'tax'=>0,
                'deduction'=>0,'net'=>0,
            ];

            $sl = 1;
            $lastPayslip = null;
        @endphp

        <table style="margin-top: 20px;">

            <tr>
                <td colspan="12" class="group-title">
                    <b>Station:</b>
                    {{ $station?->name ?? 'N/A' }}
                </td>

                <td colspan="8"
                    class="group-title"
                    style="text-align:right;">

                    <div>
                        Ref: HR/SR-{{ $month->format('m/Y') }}
                    </div>

                    <div>
                        Date: {{ now()->format('d/m/Y') }}
                    </div>

                </td>
            </tr>

            <tr>
                <th rowspan="2">SL</th>
                <th rowspan="2">Staff ID</th>
                <th rowspan="2">Name of Staff</th>
                <th rowspan="2">Designation</th>
                <th rowspan="2">Date of Joining</th>

                <th rowspan="2">Total Working Days</th>
                <th rowspan="2">Present Days</th>
                <th rowspan="2">Grade</th>

                <th colspan="4">Salary Payable(BDT)</th>
                <th colspan="6">Salary Deduction(BDT)</th>

                <th rowspan="2">Net payable salary for {{ $monthShort }}'{{ $yearShort }} (BDT)</th>
                <th rowspan="2">Remarks</th>
            </tr>

            <tr>
                <th>Basic Salary</th>
                <th>House Rent</th>
                <th>Allowance</th>
                <th>Gross Salary</th>

                <th>Absent/Others</th>
                <th>Late</th>
                <th>Personal Loan</th>
                <th>PF(7%)</th>
                <th>Income Tax</th>
                <th>Total Deduction</th>
            </tr>

            @foreach($stationGroup['departments'] as $departmentGroup)

                <tr>
                    <td colspan="20" class="group-title">
                        Department: {{ $departmentGroup['department']?->name ?? 'N/A' }}
                    </td>
                </tr>

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
                        $fine = $deduct['Fine']['total_amount'] ?? 0;
                        $other = $deduct['Other']['total_amount'] ?? 0;

                        $absent=$absent+$fine+$other;

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
                        <td>{{ $emp?->employee_id }}</td>
                        <td style="text-align:left;">{{ $emp?->name }}</td>
                        <td>{{ $emp?->designation?->name }}</td>
                        <td>
                            {{ $emp?->join_date
                                ? \Carbon\Carbon::parse($emp->join_date)->format('d-m-Y')
                                : '' }}
                        </td>
                        <td>{{ $payslip->total_working_days ?? 0 }}</td>
                        <td>{{ $payslip->working_days ?? 0 }}</td>
                        <td>{{ $payslip->grade_name ?? 'N/A' }}</td>

                        <td class="salary">{{ number_format($basic,2) }}</td>
                        <td class="salary">{{ number_format($house,2) }}</td>
                        <td class="salary">{{ number_format($allow,2) }}</td>
                        <td class="salary">{{ number_format($gross,2) }}</td>

                        <td class="deduction">{{ number_format($absent,2) }}</td>
                        <td class="deduction">{{ number_format($late,2) }}</td>
                        <td class="deduction">{{ number_format($loan,2) }}</td>
                        <td class="deduction">{{ number_format($pf,2) }}</td>
                        <td class="deduction">{{ number_format($tax,2) }}</td>
                        <td class="deduction">{{ number_format($totalDed,2) }}</td>

                        <td class="salary">{{ number_format($net,2) }}</td>
                        <td>{{$payslip->remarks}}</td>
                    </tr>

                @endforeach

            @endforeach

            <tr class="total-row">
                <td colspan="8">TOTAL</td>
                <td class="salary">{{ number_format($stationTotal['basic'],2) }}</td>
                <td class="salary">{{ number_format($stationTotal['house_rent'],2) }}</td>
                <td class="salary">{{ number_format($stationTotal['allowance'],2) }}</td>
                <td class="salary">{{ number_format($stationTotal['gross'],2) }}</td>

                <td class="deduction">{{ number_format($stationTotal['absent'],2) }}</td>
                <td class="deduction">{{ number_format($stationTotal['late'],2) }}</td>
                <td class="deduction">{{ number_format($stationTotal['loan'],2) }}</td>
                <td class="deduction">{{ number_format($stationTotal['pf'],2) }}</td>
                <td class="deduction">{{ number_format($stationTotal['tax'],2) }}</td>
                <td class="deduction">{{ number_format($stationTotal['deduction'],2) }}</td>

                <td class="salary">{{ number_format($stationTotal['net'],2) }}</td>
                <td>-</td>
            </tr>

        </table>

        {{-- SIGNATURE (FINAL FIX - BASE64 SAFE) --}}
        {{-- SIGNATURE SECTION --}}
        @if($lastPayslip)

            @php
                $preparedApproval = $lastPayslip->getStageApproval('payslip_prepare', $status);
                $checkedApproval = $lastPayslip->getStageApproval('payslip_accounts_check', $status);
                $verifiedApproval = $lastPayslip->getStageApproval('payslip_finance_verify', $status);
                $mdApproval = $lastPayslip->getStageApproval('payslip_md_approve', $status);

                $signatureData = [
                    [
                        'title' => 'Prepared By',
                        'user' => $preparedApproval?->reviewer,
                        'approval' => $preparedApproval,
                    ],
                    [
                        'title' => 'Checked By',
                        'user' => $checkedApproval?->reviewer,
                        'approval' => $checkedApproval,
                    ],
                    [
                        'title' => 'Verified By',
                        'user' => $verifiedApproval?->reviewer,
                        'approval' => $verifiedApproval,
                    ],
                    [
                        'title' => 'Approved By',
                        'user' => $mdApproval?->reviewer ?? $mdApproval?->reviewedByUser,
                        'approval' => $mdApproval,
                    ],
                ];
            @endphp

            <div class="signature-wrapper">

                @foreach($signatureData as $item)

                    @php
                        $user = $item['user'];
                        $approval = $item['approval'];

                        $imageSrc = null;

                        if (!empty($user?->signature_file)) {

                            $path = public_path('uploaded_files/' . $user->signature_file);

                            if (file_exists($path)) {

                                $type = pathinfo($path, PATHINFO_EXTENSION);

                                $imageData = file_get_contents($path);

                                $imageSrc = 'data:image/' . $type . ';base64,' . base64_encode($imageData);
                            }
                        }
                    @endphp

                    <div class="signature-box">

                        <div class="signature-line"></div>

                        <strong>{{ $item['title'] }}</strong>

                        <div class="signature-content">

                            @if($imageSrc)
                                <div style="margin:5px 0;">
                                    <img
                                            src="{{ $imageSrc }}"
                                            style="width:80px;height:auto;"
                                    >
                                </div>
                            @endif

                            <div>
                                <strong>Name:</strong>
                                {{ $user?->name ?? '-' }}
                            </div>

                            <div>
                                <strong>ID:</strong>
                                {{ $user?->employee_id ?? '-' }}
                            </div>

                            <div>
                                <strong>Date:</strong>
                                {{ $approval?->created_at?->format('d-m-Y h:i A') ?? '-' }}
                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

</div>

@endforeach

</body>
</html>