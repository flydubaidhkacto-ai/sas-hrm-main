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
            font-size: 11px;
        }

        h1 {
            text-align: center;
            margin: 4px 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: auto;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            white-space: nowrap;
            text-align: center;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
        }

        .super-dept {
            background: #d9d9d9;
            font-weight: bold;
            text-align: left;
            font-size: 16px;
        }

        .dept {
            background: #eeeeee;
            font-weight: bold;
            text-align: left;
            font-size: 14px;
        }

        .emp-name {
            text-align: left;
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

    $dates = collect($data)
        ->pluck('date')
        ->unique()
        ->sortBy(fn($d) => \Carbon\Carbon::parse($d)->timestamp)
        ->values();

    $reportData = collect($data)->groupBy([
        fn($row) => $row['employee']->department->super_department->name ?? 'N/A',
        fn($row) => $row['employee']->department->name ?? 'N/A',
        fn($row) => $row['employee']->id ?? 'N/A',
    ]);
@endphp

<h1>{{ $reportTitle ?? '' }}</h1>
<h1>{{ $dateRange ?? '' }}</h1>

<br>

<table>
    <tbody>
    @foreach($reportData as $superDeptName => $departments)
        {{-- Super Department --}}
        <tr>
            <td colspan="{{ count($dates) + 1 }}" class="super-dept">
                Station: {{ $superDeptName }}
            </td>
        </tr>

        @foreach($departments as $deptName => $employees)
            {{-- Department --}}
            <tr>
                <td colspan="{{ count($dates) + 1 }}" class="dept">
                    {{ $deptName }}
                </td>
            </tr>

            {{-- Employee header under department --}}
            <tr>
                <th class="emp-name" style="width:20%">Employee Name</th>
                @foreach($dates as $date)
                    <th>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                        ({{ \Carbon\Carbon::parse($date)->format('l') }})
                    </th>
                @endforeach
            </tr>

            {{-- Employee Rows --}}
            @foreach($employees as $empId => $rows)
                @php
                    $employee = $rows->first()['employee'];
                @endphp
                <tr>
                    <td class="emp-name">{{ $employee->name ?? '-' }}</td>

                    @foreach($dates as $date)
                        @php
                            $found = $rows->firstWhere('date', $date);
                            $checkIn = $found['check_in'] ?? null;
                            $inColor = $found['in_color'] ?? '';
                            $statusColor = $found['status_color'] ?? '';
                            $statusLabel = ucfirst($found['status']['label'] ?? '-');
                            $leaveDisplay = $found['status']['leave_display'] ?? null;
                            $request_status = $found['request_status'] ?? null;
                        @endphp

                        <td>
                            @if($checkIn)
                                @if($statusLabel == 'Late' && strtolower($request_status ?? '') == 'approved')
                                    {{-- Late with approved request --}}
                                    {{ $checkIn }} ({{ $statusLabel }}) ({{ $request_status }})
                                @elseif($statusLabel && $statusLabel !== '-')
                                    <span style="{{ $statusColor ?: '' }}">{{ $checkIn }} ({{ $statusLabel }})</span>
                                @else
                                    <span style="{{ $inColor ?: '' }}">{{ $checkIn }}</span>
                                @endif

                                @if($leaveDisplay)
                                    <br><small>{{ $leaveDisplay }}</small>
                                @endif
                            @else
                                <span style="{{ $statusColor ?: '' }}">{{ $statusLabel }}</span>
                                @if($leaveDisplay)
                                    <br><small>{{ $leaveDisplay }}</small>
                                @endif
                            @endif
                        </td>
                    @endforeach
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