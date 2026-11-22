@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Total Duty Report
    </p>

    <style>
        table {
        font-family: Arial, Helvetica, sans-serif;
        border-collapse: collapse;
            width: 100%;
        }

        table td, table th {
            border: 1px solid #ddd;
            padding: 4px;
        }

        table tr:nth-child(even){background-color: #f2f2f2;}

        table tr:hover {background-color: #ddd;}
        table tr td,
        table tr th,
        {
            font-size: 12px;
        }
        table th {
            padding-top: 10px;
            padding-bottom: 10px;
            text-align: left;
            background-color: #643a86;
            color: white;
        }
        @media print {
            table th {
                background-color: unset;
                color: unset;
            }
            table {
                width: 100%; /* Make the table width 100% for print */
                font-size: 6.5pt; /* Adjust font size for better readability in print */
                /* Add any other print-specific styles here */
            }
        }
        span {
            font-size: 12px;
        }
    </style>
@endif
<div>
    @if($data[0]['data'] ?? false)
        @php
            $first = array_keys($data[0]['data'])[0];
            $last = array_keys($data[0]['data'])[ count(array_keys($data[0]['data'])) - 1 ];
        @endphp
        <b>From:</b>
        {{ date('d M, Y', $first) }}
        &nbsp;
        &nbsp;
        &nbsp;
        &nbsp;
        <b>To:</b>
        {{ date('d M, Y', $last) }}
    @endif
</div>
<table
    @if($for_export ?? false)
        style="width: 100%; margin-top: 10px"
    @else
        class="table table-striped custom-table mt-4"
    @endif
>
    <thead>
        <tr>
            <th>S.L</th>
            <th>
                Code
            </th>
            <th>
                Emp. ID
            </th>
            <th>
                Employee
            </th>
            <th>
                Department
            </th>
            <th>
                Sup. Department
            </th>
            <th>
                Designation
            </th>
            <th>
                W. Hours
            </th>
            <th>
                W. Days
            </th>
            <th>
                O.T.
            </th>
            <th>
                Lates
            </th>
            <th>
                Late Days
            </th>
            <th>
                Leave Days
            </th>
            <th>
                Absents
            </th>
            <th>
                Holidays
            </th>
            <th>
                Weekends
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse ($data ?? [] as $time => $item)
            {{-- <tr>
                <td colspan="7">
                    @dump($item)
                </td>
            </tr> --}}
            <tr>
                <td>
                    {{ $loop->iteration }}
                </td>
                <td>
                    {{ $item['user']['id'] }}
                </td>
                <td>
                    {{ $item['user']['employee_id'] }}
                </td>
                <td>
                    @if($for_export ?? false)
                        {{ $item['user']['name'] }}
                    @else
                        <h2 class="table-avatar">
                            <a href="{{ route('employees.active.show', ['id' => $item['user']['id']]) }}">
                                {{ $item['user']['name'] }}
                            </a>
                        </h2>
                    @endif
                </td>
                <td>
                    {{ $item['user']['department']['name'] ?? '' }}
                </td>
                <td>
                    {{ $item['user']['department']['super_department']['name'] ?? '' }}
                </td>
                <td>
                    {{ $item['user']['designation']['name'] ?? '' }}
                </td>
                <td style="width: 12px">
                    {{ seconds_to_hh_mm($item['total_worked_seconds']) }}
                </td>
                <td style="width: 12px">
                    {{ $item['total_worked_days'] ?? 0 }}
                </td>
                <td style="width: 12px">
                    {{ seconds_to_hh_mm($item['total_overtime_seconds']) }}
                </td>
                <td style="width: 12px">
                    {{ seconds_to_hh_mm($item['total_late_seconds']) }}
                </td>
                <td style="width: 7px">
                    {{ $item['total_late_days'] ?? 0 }}
                </td>
                <td style="width: 7px">
                    {{ $item['total_leave_days'] ?? 0 }}
                </td>
                <td style="width: 7px">
                    {{ $item['total_absent_days'] ?? 0 }}
                </td>
                <td style="width: 7px" >
                    {{ $item['total_holidays'] ?? 0 }}
                </td>
                <td style="width: 7px" >
                    {{ $item['total_weekends'] ?? 0 }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="13" class="text-center">
                    No Data
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
