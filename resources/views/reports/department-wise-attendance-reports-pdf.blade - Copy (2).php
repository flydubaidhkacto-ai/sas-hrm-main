<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle ?? 'Department Wise Attendance' }}</title>

    <style>
        @page {
            margin: 20px 10px;
            header: html_myHeader;
            footer: html_myFooter;
        }

        body {
            font-family: sans-serif;
            font-size: 9px; /* smaller font for large calendar */
        }

        h1 {
            text-align: center;
            margin: 4px 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed; /* critical for consistent column widths */
        }

        th, td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            word-wrap: break-word;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
        }

        /* ===== Column widths ===== */
        .emp-name {
            text-align: left;
            width: 380px;          /* Increased width */
            white-space: normal;   /* Allows wrapping if needed */
            word-break: break-word; /* Break at spaces or long words */
            font-size: 9px;        /* small but readable */
        }

        th.emp-name {
            white-space: normal;   /* header also wraps */
            word-break: break-word;
        }

        .date-col {
            width: 55px;
            font-size: 8px;
        }

        .total-col {
            width: 65px;
            font-weight: bold;
        }

        /* ===== Group headers ===== */
        .super-dept {
            background: #d9d9d9;
            font-weight: bold;
            text-align: left;
            font-size: 14px;
        }

        .dept {
            background: #eeeeee;
            font-weight: bold;
            text-align: left;
            font-size: 12px;
        }

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

@php
    $data = is_array($data) ? $data : iterator_to_array($data);

    // Total Employees
    $totalEmployees = collect($data)
        ->pluck('employee.id')
        ->filter()
        ->unique()
        ->count();

    // Unique dates for columns
    $dates = collect($data)
        ->pluck('date')
        ->unique()
        ->sortBy(fn($d) => \Carbon\Carbon::parse($d)->timestamp)
        ->values();

    // Group data: Super Dept → Dept → Employee
    $reportData = collect($data)->groupBy([
        fn($row) => $row['employee']->department->super_department->name ?? 'N/A',
        fn($row) => $row['employee']->department->name ?? 'N/A',
        fn($row) => $row['employee']->id ?? 'N/A',
    ]);
@endphp

<h1>{{ $reportTitle ?? '' }}</h1>
<h1>{{ $dateRange ?? '' }}</h1>
<h1>
    Total Employees: {{ $totalEmployees }},
    Shift Type: <span style="text-transform: capitalize;">{{ $shiftType ?? 'All' }}</span>
</h1>

<br>

<table>
    <tbody>
    @foreach($reportData as $superDeptName => $departments)

        {{-- Super Department --}}
        <tr>
            <td colspan="{{ count($dates) + 3 }}" class="super-dept">
                Station: {{ $superDeptName }}
            </td>
        </tr>

        @foreach($departments as $deptName => $employees)

            {{-- Department --}}
            <tr>
                <td colspan="{{ count($dates) + 3 }}" class="dept">
                    {{ $deptName }}
                </td>
            </tr>

            {{-- Header --}}
            <tr>
                <th class="emp-name">Employee Name</th>

                @foreach($dates as $date)
                    <th class="date-col">
                        {{ \Carbon\Carbon::parse($date)->format('d M') }}<br>
                        ({{ \Carbon\Carbon::parse($date)->format('D') }})
                    </th>
                @endforeach

                <th class="total-col">Total Late</th>
                <th class="total-col">Approved Late</th>
            </tr>

            {{-- Employee Rows --}}
            @php $sl = 1; @endphp

            @foreach($employees as $empId => $rows)
                @php
                    $employee = $rows->first()['employee'] ?? null;
                    $totalLate = 0;
                    $totalApprovedLate = 0;
                @endphp

                <tr>
                    <td class="emp-name">
                        {{ $sl++ }}. {{ $employee->name ?? '-' }} ({{ $employee->id ?? '-' }})
                    </td>

                    @foreach($dates as $date)
                        @php
                            $found = $rows->firstWhere('date', $date);
                            $checkIn = $found['check_in'] ?? null;
                            $inColor = $found['in_color'] ?? '';
                            $statusColor = $found['status_color'] ?? '';
                            $statusLabel = ucfirst($found['status']['label'] ?? '-');
                            $leaveDisplay = $found['status']['leave_display'] ?? null;
                            $request_status = $found['request_status'] ?? null;

                            if (strtolower($statusLabel) === 'late') {
                                $totalLate++;
                                if (strtolower($request_status ?? '') === 'approved') {
                                    $totalApprovedLate++;
                                }
                            }
                        @endphp

                        <td class="date-col">
                            @if($checkIn)
                                @if(strtolower($statusLabel) == 'late' && strtolower($request_status ?? '') == 'approved')
                                    {{ $checkIn }} ({{ $statusLabel }}) ({{ $request_status }})
                                @elseif($statusLabel && $statusLabel !== '-')
                                    <span style="{{ $statusColor }}">
                                        {{ $checkIn }} ({{ $statusLabel }})
                                    </span>
                                @else
                                    <span style="{{ $inColor }}">
                                        {{ $checkIn }}
                                    </span>
                                @endif

                                @if($leaveDisplay)
                                    <br><small>{{ $leaveDisplay }}</small>
                                @endif
                            @else
                                <span style="{{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>

                                @if($leaveDisplay)
                                    <br><small>{{ $leaveDisplay }}</small>
                                @endif
                            @endif
                        </td>
                    @endforeach

                    <td class="total-col">{{ $totalLate }}</td>
                    <td class="total-col" style="color:green;">
                        {{ $totalApprovedLate }}
                    </td>
                </tr>
            @endforeach

        @endforeach
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