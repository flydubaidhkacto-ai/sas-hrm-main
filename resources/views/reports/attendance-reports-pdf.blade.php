<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title style="text-transform: capitalize;">{{ $reportTitle }}</title>

    <style>
        @page {
            margin: 20px 10px;
            header: html_myHeader;
            footer: html_myFooter;
        }

        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        h3 {
            text-align: center;
            margin: 5px 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
            page-break-inside: auto;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        th, td {
            padding: 3px;
            border: 1px solid #000;
            text-align: left;
            vertical-align: middle;
            word-break: break-word;
        }

        th {
            background-color: #f0f0f0;
            font-size: 12px;
        }

        /* ===== WIDTH BALANCED (≈100%) ===== */

        th.sl, td.sl {
            width: 2%;
        }

        th.emp-id, td.emp-id {
            width: 4%;
        }

        th.emp-name, td.emp-name {
            width: 8%;
        }

        th.join-date, td.join-date {
            width: 6%;
        }

        th.super-dept, td.super-dept {
            width: 8%;
        }

        th.dept, td.dept {
            width: 7%;
        }

        th.designation, td.designation {
            width: 8%;
        }

        th.emp-type, td.emp-type {
            width: 5%;
        }

        th.date, td.date {
            width: 6%;
        }

        th.shift-type, td.shift-type {
            width: 5%;
        }

        th.shift, td.shift {
            width: 5%;
        }

        th.shift-start, td.shift-start {
            width: 4%;
        }

        th.shift-end, td.shift-end {
            width: 4%;
        }

        th.check-in, td.check-in {
            width: 4%;
        }

        th.check-out, td.check-out {
            width: 4%;
        }

        th.status, td.status {
            width: 9%;
        }

        th.time-missed, td.time-missed {
            width: 5%;
        }

        th.req-type, td.req-type {
            width: 4%;
        }

        th.req-status, td.req-status {
            width: 4%;
        }
        
        /* status colors */

        .status-red {
            color: red;
            font-weight: bold;
        }

        .status-green {
            color: green;
            font-weight: bold;
        }

        /* footer */

        .page-footer {
            width: 100%;
            font-size: 9px;
            text-align: right;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
    </style>
</head>

<body>

<h3 style="text-transform: capitalize;">{{ $reportTitle }}</h3>
<h3>{{ $dateRange }}</h3>
<h3>
    Employee: {{ $employee }},
    Shift Type: {{ $shiftType }},
    Attendance Type: {{ $attendanceType }}
</h3>

<br><br>

<table>
    <thead>
    <tr>
        <th class="sl">SL</th>
        <th class="emp-id">Emp ID</th>
        <th class="emp-name">Employee Name</th>
        <th class="join-date">Join Date</th>
        <th class="super-dept">Super Department</th>
        <th class="dept">Department</th>
        <th class="designation">Designation</th>
        <th class="emp-type">Employee Type</th>
        <th class="date">Date</th>
        <th class="shift-type">Shift Type</th>
        <th class="shift">Shift</th>
        <th class="shift-start">Shift Start</th>
        <th class="shift-end">Shift End</th>
        <th class="check-in">Punched In</th>
        <th class="check-out">Punched Out</th>
        <th class="status">Attendance Status</th>
        <th class="time-missed">Time Missed</th>
        <th class="req-type">Request Type</th>
        <th class="req-status">Request Status</th>
    </tr>
    </thead>

    <tbody>
    @php $sl = 1; @endphp

    @foreach($data as $row)

        @php
            $employee      = $row['employee'] ?? null;
            $shiftType     = $row['shift_type'] ?? '-';
            $shiftName     = $row['shift_name'] ?? '-';
            $shiftStart    = $row['shift_start'] ?? '-';
            $shiftEnd      = $row['shift_end'] ?? '-';
            $checkIn       = $row['check_in'] ?? '-';
            $checkOut      = $row['check_out'] ?? '-';
            $statusColor   = $row['status_color'] ?? '';
            $inColor       = $row['in_color'] ?? '';
            $outColor      = $row['out_color'] ?? '';
            $timeMissed    = $row['time_missed_readable'] ?? '-';
        @endphp

        <tr>
            <td class="sl">{{ $sl++ }}</td>
            <td class="emp-id">{{ $employee->id ?? '-' }}</td>
            <td class="emp-name">{{ $employee->name ?? '-' }}</td>
            <td class="join-date">{{ $employee->join_date ?? '-' }}</td>
            <td class="super-dept">{{ $employee->department->super_department->name ?? '-' }}</td>
            <td class="dept">{{ $employee->department->name ?? '-' }}</td>
            <td class="designation">{{ $employee->designation->name ?? '-' }}</td>
            <td class="emp-type">{{ $employee->employee_type ?? '-' }}</td>
            <td class="date">{{ $row['date'] ?? '-' }}</td>
            <td class="shift-type">{{ $shiftType }}</td>
            <td class="shift">{{ $shiftName }}</td>
            <td class="shift-start nowrap">{{ $shiftStart }}</td>
            <td class="shift-end nowrap">{{ $shiftEnd }}</td>
            <td class="check-in nowrap" style="{{ $inColor }}">{{ $checkIn }}</td>
            <td class="check-out nowrap" style="{{ $outColor }}">{{ $checkOut }}</td>

            <td class="status" style="{{ $statusColor }}">
                {{ ucfirst($row['status']['label'] ?? '-') }}
                @if(!empty($row['status']['leave_display']))
                    <br><small>{{ $row['status']['leave_display'] }}</small>
                @endif
            </td>

            <td class="time-missed nowrap">{{ $timeMissed }}</td>
            <td class="req-type">{{ ucfirst($row['request_type'] ?? '-') }}</td>
            <td class="req-status">{{ ucfirst($row['request_status'] ?? '-') }}</td>
        </tr>

    @endforeach
    </tbody>
</table>

<htmlpagefooter name="myFooter">
    <div class="page-footer">
        Page {PAGENO} of {nbpg}
    </div>
</htmlpagefooter>

<sethtmlpagefooter name="myFooter" value="on"/>

</body>
</html>