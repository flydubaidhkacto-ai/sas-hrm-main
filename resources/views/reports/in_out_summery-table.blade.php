@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        In Out Summery Report
    </p>

    <style>
        table {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        table td, table th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        table tr:nth-child(even){background-color: #f2f2f2;}

        table tr:hover {background-color: #ddd;}

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
                font-size: 7pt; /* Adjust font size for better readability in print */
                /* Add any other print-specific styles here */
            }
        }
    </style>
@endif
<table
    @if($for_export ?? false)
        style="width: 100%"
    @else
        class="table table-striped custom-table"
    @endif
>
    <thead>
        <tr>
            <th>
                S.L
            </th>
            <th>
                Ref.
            </th>
            <th>
                Emp. ID
            </th>
            <th>Employee</th>
            <th>Department</th>
            <th>Super Dpt</th>
            <th>Designation</th>
            <th>Shift</th>
            <th>Date</th>
            <th>In Time</th>
            <th>Out Time</th>
            <th>W. Hour</th>
            <th>O.T.</th>
        </tr>
    </thead>
    <tbody>
        @php
            $ii = 1;
        @endphp
        @foreach ($data as $item)
            @php
                if($item->shift?->is_24_hour_shift)
                {
                    $mappedInOuts = \App\Actions\EmployeePunched\Calculate24HourShift::mappedInOuts($item);
                }else {
                    $mappedInOuts = \App\Actions\EmployeePunched\CalculateWorkingTimeOfAttendance::execute(
                        $item, true
                    )['mappedInOuts'] ?? [];
                }
            @endphp
            @foreach ($mappedInOuts as $key => $value)
                @php
                    $in = $value[0] ?? null;
                    $out = $value[1] ?? null;
                @endphp
                <tr>
                    <td>
                        {{ $ii++ }}
                    </td>
                    <td>
                        {{ $item->id }}
                    </td>
                    <td>
                        {{ $item->employee?->employee_id }}
                    </td>
                    <td>
                        @if($for_export ?? false)
                            {{ $item->employee?->name }}
                        @else
                            <h2 class="table-avatar">
                                <a href="{{ route('employees.active.show', ['id' => $item->employee_id]) }}">
                                    @if ($item->employee)
                                        {{ $item->employee?->name }}
                                    @endif
                                </a>
                            </h2>
                        @endif
                    </td>
                    <td>
                        {{ $item->employee?->department?->name }}
                    </td>
                    <td>
                        {{ $item->employee?->department?->super_department?->name }}
                    </td>
                    <td>
                        {{ $item->employee?->designation?->name }}
                    </td>
                    <td>
                        {{ $item->shift?->name }}
                    </td>
                    <td>
                        {{ carbon($item->date)->format('d M, Y') }}
                    </td>
                    <td class="text-success">
                        @if($in)
                            @php
                                $format = carbon($item->date)->format('d-m-Y') === ($in->date_time)->format('d-m-Y')
                                    ? 'h:i:s A' : 'd M, h:i:s A';
                            @endphp
                            {{ ($in->date_time)->format($format) }}
                            @if($in->location && str_contains($in->location, '-'))
                                <br>
                                <a href="https://www.google.com/maps?q={{ str_replace('-', ',', $in->location) }}" target="_blank">Location</a>
                            @endif
                        @else
                            --
                        @endif
                    </td>
                    <td class="" style="color: #f5a606 !important">
                        @if($out)
                            @php
                                $format = $in && $out && ($in->date_time)->format('d-m-Y') === ($out->date_time)->format('d-m-Y')
                                    ? 'h:i:s A' : 'd M, h:i:s A';
                            @endphp
                            {{ ($out->date_time)->format($format) }}
                            @if($out->location && str_contains($out->location, '-'))
                                <br>
                                <a href="https://www.google.com/maps?q={{ str_replace('-', ',', $out->location) }}" target="_blank">Location</a>
                            @endif
                        @else
                            --
                        @endif
                    </td>
                    <td data-toggle="tooltip" title="{{ gmdate('H:i:s', $value[2]['worked'] ?? 0) }}">
                        {{ gmdate('H:i', $value[2]['worked'] ?? 0) }}
                    </td>
                    <td data-toggle="tooltip" title="{{ gmdate('H:i:s', $value[2]['overtime'] ?? 0) }}">
                        {{ gmdate('H:i', $value[2]['overtime'] ?? 0) }}
                    </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
