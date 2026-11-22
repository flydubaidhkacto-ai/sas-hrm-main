<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>
        Summary Report
    </title>

    <style>

        @page{
            size:A3 landscape;
            margin:8mm;
        }

        body{
            font-family:Arial,sans-serif;
            font-size:11px;
            color:#000;
        }

        *{
            box-sizing:border-box;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-bottom:18px;
        }

        th,
        td{
            border:1px solid #000;
            padding:5px;
            text-align:center;
            vertical-align:middle;
            word-wrap:break-word;
        }

        th{
            background:#d9e2f2;
            font-weight:bold;
            font-size:11px;
        }

        td{
            font-size:11px;
        }

        tr:nth-child(even){
            background:#fafafa;
        }

        .company-name{
            text-align:center;
            font-size:22px;
            font-weight:bold;
            margin-bottom:2px;
        }

        .report-title{
            text-align:center;
            font-size:16px;
            font-weight:bold;
            margin-bottom:20px;
        }

        .section-title{
            background:#f4f6f9;
            border-left:5px solid #000;
            padding:8px 10px;
            margin:12px 0 5px;
            font-size:13px;
            font-weight:bold;
        }

        .section-subtitle{
            margin-bottom:10px;
            font-size:12px;
            font-weight:bold;
        }

        .salary{
            text-align:right;
            padding-right:8px;
            font-weight:600;
        }

        .total-row td{
            background:#dfe6e9;
            font-weight:bold;
        }

        .grand-total td{
            background:#cfd8dc;
            font-weight:bold;
            font-size:12px;
        }

        .left{
            text-align:left;
        }

        .signature-wrapper{
            width:100%;
            margin-top:40px;
            font-size:0;
        }

        .signature-box{
            width:24%;
            display:inline-block;
            vertical-align:top;
            text-align:center;
            margin-right:1%;
            font-size:10px;
        }

        .signature-box:last-child{
            margin-right:0;
        }

        .signature-line{
            border-bottom:1px solid #000;
            height:25px;
            margin-bottom:8px;
        }

        .signature-content{
            text-align:left;
            padding:0 5px;
        }

        .signature-content div{
            margin-bottom:4px;
        }

        img{
            max-width:80px;
            max-height:50px;
            object-fit:contain;
        }

    </style>
</head>

<body>

@php

    $monthLabel = $month->format('F Y');

    $stations = $summary['stations'];

    $totals = $summary['totals'];

    function money($amount){

        return number_format((float)$amount,2);
    }

@endphp

<div>

    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}

    <div class="company-name">
        Sky Aviation Services Ltd.
    </div>

    <div class="report-title">
        Summary Report For {{ $monthLabel }}
    </div>

    {{-- ========================================================= --}}
    {{-- SECTION 1 --}}
    {{-- ========================================================= --}}

    <div class="section-title">
        (1) SAS AND EMPLOYEE'S CONTRIBUTION
    </div>

    <div class="section-subtitle">
        Summary Of PF Statement For {{ $monthLabel }}
    </div>

    <table>

        <tr>
            <th width="5%">S/n</th>
            <th>Station</th>
            <th>Employees' Contribution</th>
            <th>SAS Contribution</th>
            <th>Total Amount</th>
        </tr>

        @foreach($stations as $row)

            <tr>

                <td>
                    {{ $loop->iteration }}
                </td>

                <td class="left">
                    {{ $row['station'] }}
                </td>

                <td class="salary">
                    {{ money($row['employee_pf']) }}
                </td>

                <td class="salary">
                    {{ money($row['sas_pf']) }}
                </td>

                <td class="salary">
                    {{ money($row['pf_total']) }}
                </td>

            </tr>

        @endforeach

        <tr class="total-row">

            <td colspan="2">
                Total
            </td>

            <td class="salary">
                {{ money($totals['employee_pf']) }}
            </td>

            <td class="salary">
                {{ money($totals['sas_pf']) }}
            </td>

            <td class="salary">
                {{ money($totals['pf_total']) }}
            </td>

        </tr>

    </table>

    {{-- ========================================================= --}}
    {{-- SECTION 2 --}}
    {{-- ========================================================= --}}

    <div class="section-title">
        (2) EMPLOYEES' PAYMENT - INCOME TAX
    </div>

    <div class="section-subtitle">
        Summary Of Staff Income Tax For {{ $monthLabel }}
    </div>

    <table>

        <tr>

            <th width="5%">S/n</th>

            <th>Station</th>

            <th>Employees' Payment</th>

            <th>Total Amount</th>

        </tr>

        @foreach($stations as $row)

            <tr>

                <td>
                    {{ $loop->iteration }}
                </td>

                <td class="left">
                    {{ $row['station'] }}
                </td>

                <td class="salary">
                    {{ money($row['tax']) }}
                </td>

                <td class="salary">
                    {{ money($row['tax']) }}
                </td>

            </tr>

        @endforeach

        <tr class="total-row">

            <td colspan="2">
                Total
            </td>

            <td class="salary">
                {{ money($totals['tax']) }}
            </td>

            <td class="salary">
                {{ money($totals['tax']) }}
            </td>

        </tr>

    </table>

    {{-- ========================================================= --}}
    {{-- SECTION 3 --}}
    {{-- ========================================================= --}}

    <div class="section-title">
        (3) SAS PAYMENT - STAFF SALARY
    </div>

    <div class="section-subtitle">
        Summary Of Salary Statement For {{ $monthLabel }}
    </div>

    <table>

        <tr>

            <th width="5%">S/n</th>

            <th>Station</th>

            <th>Bank Pay</th>

            <th>Cash Pay</th>

            <th>Total Amount</th>

        </tr>

        @foreach($stations as $row)

            <tr>

                <td>
                    {{ $loop->iteration }}
                </td>

                <td class="left">
                    {{ $row['station'] }}
                </td>

                <td class="salary">
                    {{ money($row['bank_pay']) }}
                </td>

                <td class="salary">
                    {{ money($row['cash_pay']) }}
                </td>

                <td class="salary">
                    {{ money($row['salary_total']) }}
                </td>

            </tr>

        @endforeach

        <tr class="total-row">

            <td colspan="2">
                Total
            </td>

            <td class="salary">
                {{ money($totals['bank_pay']) }}
            </td>

            <td class="salary">
                {{ money($totals['cash_pay']) }}
            </td>

            <td class="salary">
                {{ money($totals['salary_total']) }}
            </td>

        </tr>

    </table>

    {{-- ========================================================= --}}
    {{-- SECTION 4 --}}
    {{-- ========================================================= --}}

    <div class="section-title">
        (4) SAS EMPLOYEE'S PAYMENT
    </div>

    <div class="section-subtitle">
        Summary Of SAS EMPLOYEE'S PAYMENT Statement For {{ $monthLabel }}
    </div>

    <table>

        <tr>

            <th rowspan="2" width="5%">
                S/n
            </th>

            <th rowspan="2">
                Station
            </th>

            <th colspan="2">
                SAS PAYMENT
            </th>

            <th colspan="2">
                EMPLOYEE'S PAYMENT
            </th>

        </tr>

        <tr>

            <th>
                SAS payment for Staff salary
            </th>

            <th>
                SAS Contribution for PF Fund
            </th>

            <th>
                Employee Contribution for Income Tax
            </th>

            <th>
                Employee Contribution for PF Fund
            </th>

        </tr>

        @foreach($stations as $row)

            <tr>

                <td>
                    {{ $loop->iteration }}
                </td>

                <td class="left">
                    {{ $row['station'] }}
                </td>

                <td class="salary">
                    {{ money($row['sas_salary']) }}
                </td>

                <td class="salary">
                    {{ money($row['sas_pf_contribution']) }}
                </td>

                <td class="salary">
                    {{ money($row['employee_tax']) }}
                </td>

                <td class="salary">
                    {{ money($row['employee_pf']) }}
                </td>

            </tr>

        @endforeach

        <tr class="total-row">

            <td colspan="2">
                Total
            </td>

            <td class="salary">
                {{ money($totals['sas_salary']) }}
            </td>

            <td class="salary">
                {{ money($totals['sas_pf_contribution']) }}
            </td>

            <td class="salary">
                {{ money($totals['employee_tax']) }}
            </td>

            <td class="salary">
                {{ money($totals['employee_pf_total']) }}
            </td>

        </tr>

        <tr class="grand-total">

            <td colspan="2">
                In Total
            </td>

            <td class="salary">
                {{ money(
                    $totals['sas_salary']
                    +
                    $totals['sas_pf_contribution']
                ) }}
            </td>

            <td class="salary">
                {{ money($totals['sas_pf_contribution']) }}
            </td>

            <td class="salary">
                {{ money($totals['employee_tax']) }}
            </td>

            <td class="salary">
                {{ money($totals['employee_pf_total']) }}
            </td>

        </tr>

    </table>

    {{-- ========================================================= --}}
    {{-- SIGNATURE SECTION --}}
    {{-- ========================================================= --}}

    @if($lastPayslip)

        @php

            $preparedApproval =
                $lastPayslip->getStageApproval(
                    'payslip_prepare',
                    $status
                );

            $checkedApproval =
                $lastPayslip->getStageApproval(
                    'payslip_accounts_check',
                    $status
                );

            $verifiedApproval =
                $lastPayslip->getStageApproval(
                    'payslip_finance_verify',
                    $status
                );

            $mdApproval =
                $lastPayslip->getStageApproval(
                    'payslip_md_approve',
                    $status
                );

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
                    'user' => $mdApproval?->reviewer
                        ?? $mdApproval?->reviewedByUser,
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

                        $path = public_path(
                            'uploaded_files/' . $user->signature_file
                        );

                        if (file_exists($path)) {

                            $type = pathinfo(
                                $path,
                                PATHINFO_EXTENSION
                            );

                            $imageData = file_get_contents($path);

                            $imageSrc =
                                'data:image/' .
                                $type .
                                ';base64,' .
                                base64_encode($imageData);
                        }
                    }

                @endphp

                <div class="signature-box">

                    <div class="signature-line"></div>

                    <strong>
                        {{ $item['title'] }}
                    </strong>

                    <div class="signature-content">

                        @if($imageSrc)

                            <div style="margin:5px 0;">

                                <img
                                        src="{{ $imageSrc }}"
                                >

                            </div>

                        @endif

                        <div>

                            <strong>Name :</strong>

                            {{ $user?->name ?? '-' }}

                        </div>

                        <div>

                            <strong>ID :</strong>

                            {{ $user?->employee_id ?? '-' }}

                        </div>

                        <div>

                            <strong>Date :</strong>

                            {{
                                $approval?->created_at
                                    ?->format('d-m-Y h:i A')
                                    ?? '-'
                            }}

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    @endif

</div>

</body>
</html>
```
