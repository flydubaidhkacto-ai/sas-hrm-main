

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bank Salary Payment Report</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-size: 11px;
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        th {
            font-weight: bold;
        }

        .group-title {
            background: #e9ecef;
            font-weight: bold;
            text-align: left;
            padding: 6px;
        }

        .salary { text-align: right; }
        .deduction { text-align: right; }

        .total-row {
            font-weight: bold;
            background: #f1f1f1;
        }

        .station-block {
            page-break-after: always;
        }

        .station-block:last-child {
            page-break-after: auto;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 20px;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>

<div class="container-fluid">

    @php
        $monthLabel = $month->format('F Y');

        $signatures = [
            'Prepared By',
            'Checked By',
            'Verified By',
            'Approved By (MD)'
        ];
    @endphp

        <div class="station-block">

            {{-- HEADER --}}
            <div class="text-center mb-2">
                <h5><b>Sky Aviation Services Ltd.</b></h5>
                <h6>Bank Salary Payment Report - {{ $monthLabel }}</h6>
            </div>

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
                @endphp

            <table style="margin-top: 20px;">

                {{-- STATION HEADER --}}
                <tr>
                    <td colspan="20" class="group-title">
                        <b>Station:</b> {{ $station?->name ?? 'N/A' }}

                        <span style="float:right;">
                    Ref: HR/SR-{{ $month->format('m/Y') }} <br>
                    Date: {{ now()->format('d-m-Y') }}
                </span>
                    </td>
                </tr>

                {{-- HEADER --}}
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
                            Department: {{ $department?->name ?? 'N/A' }}
                        </td>
                    </tr>


                    {{-- EMPLOYEES --}}
                    @foreach($departmentGroup['employees'] as $payslip)

                        @php
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
                            <td>{{ $emp?->employee_id }}</td>
                            <td style="text-align:left;">{{ $emp?->name }}</td>
                            <td>{{ $emp?->designation?->name }}</td>

                            {{-- JOIN DATE --}}
                            <td>
                                {{ $emp?->join_date ? \Carbon\Carbon::parse($emp->join_date)->format('d-m-Y') : '' }}
                            </td>

                            {{-- PAYSLIP DATA --}}
                            <td>{{ $payslip->ssbd_total_attendance ?? 0 }}</td>
                            <td>{{ $payslip->working_days ?? 0 }}</td>


                            <td>{{ $payslip->grade_name ?? 'N/A' }}</td>

                            {{-- EARN --}}
                            <td class="salary">{{ number_format($basic,2) }}</td>
                            <td class="salary">{{ number_format($house,2) }}</td>
                            <td class="salary">{{ number_format($allow,2) }}</td>
                            <td class="salary">{{ number_format($gross,2) }}</td>

                            {{-- DEDUCT --}}
                            <td class="deduction">{{ number_format($absent,2) }}</td>
                            <td class="deduction">{{ number_format($late,2) }}</td>
                            <td class="deduction">{{ number_format($loan,2) }}</td>
                            <td class="deduction">{{ number_format($pf,2) }}</td>
                            <td class="deduction">{{ number_format($tax,2) }}</td>
                            <td class="deduction">{{ number_format($totalDed,2) }}</td>

                            <td class="salary">{{ number_format($net,2) }}</td>
                            <td>-</td>
                        </tr>

                    @endforeach

                @endforeach


                {{-- TOTAL --}}
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

                {{-- SIGNATURE SECTION --}}
                <div class="row mt-5">

                    @php
                        $preparedUser = $payslip->getStageUser('payslip_prepare', $status);
                        $prepared = $payslip->getStageApproval('payslip_prepare', $status);
                    @endphp

                    {{-- Prepared By --}}
                    <div class="col-3 text-center">
                        <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 10px auto;"></div>

                        <strong>Prepared By</strong>

                        <div class="text-start mt-2" style="font-size: 11px;">

                            <div style="display:flex; justify-content:space-between;">
                                <span>Name & Sign:</span>
                                <span style="flex:1; margin-left:5px;">
                                    @if($preparedUser)
                                        {{ $preparedUser->name }}
                                        <br>
                                        @if($preparedUser->signature_file)
                                            <img src="{{ asset('uploaded_files/'.$preparedUser->signature_file) }}"
                                                 style="width:80px; margin-top:5px;">
                                        @endif
                                    @endif
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between;">
                                <span>Staff ID:</span>
                                <span style="flex:1; margin-left:5px;">
                                    {{ $preparedUser?->employee_id }}
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between;">
                                <span>Date:</span>
                                <span style="flex:1; margin-left:5px;">
                                    {{ $prepared?->created_at?->format('d-m-Y h:i A') }}
                                </span>
                            </div>

                        </div>
                    </div>

                    @php
                        $checkedUser = $payslip->getStageUser('payslip_accounts_check', $status);
                        $checked = $payslip->getStageApproval('payslip_accounts_check', $status);
                    @endphp

                    {{-- Checked By --}}
                    <div class="col-3 text-center">
                        <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 10px auto;"></div>

                        <strong>Checked By</strong>

                        <div class="text-start mt-2" style="font-size: 11px;">

                            <div style="display:flex; justify-content:space-between;">
                                <span>Name & Sign:</span>
                                <span style="flex:1; margin-left:5px;">
                                    @if($checkedUser)
                                        {{ $checkedUser->name }}
                                        <br>
                                        @if($checkedUser->signature_file)
                                            <img src="{{ asset('uploaded_files/'.$checkedUser->signature_file) }}"
                                                 style="width:80px; margin-top:5px;">
                                        @endif
                                    @endif
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between;">
                                <span>Staff ID:</span>
                                <span style="flex:1; margin-left:5px;">
                                    {{ $checkedUser?->employee_id }}
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between;">
                                <span>Date:</span>
                                <span style="flex:1; margin-left:5px;">
                                    {{ $checked?->created_at?->format('d-m-Y h:i A') }}
                                </span>
                            </div>

                        </div>
                    </div>

                    @php
                        $verifiedUser = $payslip->getStageUser('payslip_finance_verify', $status);
                        $verified = $payslip->getStageApproval('payslip_finance_verify', $status);
                    @endphp

                    {{-- Verified By --}}
                    <div class="col-3 text-center">
                        <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 10px auto;"></div>

                        <strong>Verified By</strong>
                        <div class="text-start mt-2" style="font-size: 11px;">

                            <div style="display:flex; justify-content:space-between;">
                                <span>Name & Sign:</span>
                                <span style="flex:1; margin-left:5px;">
                                    @if($verifiedUser)
                                        {{ $verifiedUser->name }}
                                        <br>
                                        @if($verifiedUser->signature_file)
                                            <img src="{{ asset('uploaded_files/'.$checkedUser->signature_file) }}"
                                                 style="width:80px; margin-top:5px;">
                                        @endif
                                    @endif
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between;">
                                <span>Staff ID:</span>
                                <span style="flex:1; margin-left:5px;">
                                    {{ $verifiedUser?->employee_id }}
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between;">
                                <span>Date:</span>
                                <span style="flex:1; margin-left:5px;">
                                    {{ $verified?->created_at?->format('d-m-Y h:i A') }}
                                </span>
                            </div>

                        </div>
                    </div>

                    @php
                        $mdApproval = $payslip->getStageApproval('payslip_md_approve', $status);
                        $mdUser = $mdApproval?->reviewer ?? $mdApproval?->reviewedByUser;
                    @endphp

                    {{-- Approved By --}}
                    <div class="col-3 text-center">
                        <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 10px auto;"></div>

                        <strong>Approved By (MD)</strong>

                        <div class="text-start mt-2" style="font-size: 11px;">

                            <div style="display:flex; justify-content:space-between;">
                                <span>Name & Sign:</span>
                                <span style="flex:1; margin-left:5px;">
                                    @if($mdUser)
                                        {{ $mdUser->name }}
                                        <br>
                                        @if($mdUser->signature_file)
                                            <img src="{{ asset('uploaded_files/'.$mdUser->signature_file) }}" style="width:80px; margin-top:5px;">
                                        @endif
                                    @endif
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between;">
                                <span>Staff ID:</span>
                                <span style="flex:1; margin-left:5px;">
                                    {{ $mdUser?->employee_id }}
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between;">
                                <span>Date:</span>
                                <span style="flex:1; margin-left:5px;">
                                    {{ $mdApproval ?->created_at?->format('d-m-Y h:i A') }}
                                </span>
                            </div>

                        </div>
                    </div>

                </div>

        </div>

    @endforeach

</div>

</body>
</html>