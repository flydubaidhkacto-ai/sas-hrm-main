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

        h3 {
            text-align: center;
            margin: 4px 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: auto;
        }

        thead {
            display: table-header-group;
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
            text-align: center;
        }

        .dept {
            background: #eeeeee;
            font-weight: bold;
            text-align: center;
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

{{-- ================== DATA PREP ================== --}}
@php
    // Generator-safe array
    $data = is_array($data) ? $data : iterator_to_array($data);

    // Unique date list (chronological)
    $dates = collect($data)
        ->pluck('date')
        ->unique()
        ->sortBy(fn($d) => \Carbon\Carbon::parse($d)->timestamp)
        ->values();

    // Group: Super Dept → Dept → Employee
    $reportData = collect($data)->groupBy([
        fn($row) => $row['employee']->department->super_department->name ?? 'N/A',
        fn($row) => $row['employee']->department->name ?? 'N/A',
        fn($row) => $row['employee']->id ?? 'N/A',
    ]);
@endphp

{{-- HEADER --}}
<h3>{{ $reportTitle ?? '' }}</h3>
<h3>{{ $dateRange ?? '' }}</h3>

<br>

<table>
    <thead>
    <tr>
        <th style="width:20%">Employee Name</th>
        @foreach($dates as $date)
            <th>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</th>
        @endforeach
    </tr>
    </thead>

    <tbody>

    {{-- ================== SUPER DEPARTMENT LOOP ================== --}}
    @foreach($reportData as $superDeptName => $departments)
        <tr>
            <td colspan="{{ count($dates) + 1 }}" class="super-dept">
                Super Department: {{ $superDeptName }}
            </td>
        </tr>

        {{-- ================== DEPARTMENT LOOP ================== --}}
        @foreach($departments as $deptName => $employees)
            <tr>
                <td colspan="{{ count($dates) + 1 }}" class="dept">
                    Department: {{ $deptName }}
                </td>
            </tr>

            {{-- ================== EMPLOYEE LOOP ================== --}}
            @foreach($employees as $empId => $rows)
                @php
                    $employee = $rows->first()['employee'];
                @endphp

                <tr>
                    <td class="emp-name">{{ $employee->name ?? '-' }}</td>

                    {{-- DATE WISE CHECK_IN --}}
                    @foreach($dates as $date)
                        @php
                            $found = $rows->firstWhere('date', $date);
                        @endphp
                        <td>{{ $found['check_in'] ?? '-' }}</td>
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