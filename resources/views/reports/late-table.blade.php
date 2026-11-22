@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Late Report
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
                Code
            </th>
            <th>
                Emp. ID
            </th>
            <th>Employee</th>
            <th>Designation</th>
            <th>Department</th>
            <th>Sup. Department</th>
            <th>Late</th>
            <th>Punched In</th>
            <th>Shift started</th>
            <th>Shift</th>
            <th>Time missed</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $item)
            <tr>
                <td>
                    {{ $loop->iteration }}
                </td>
                <td>
                    {{ $item->employee?->id }}
                </td>
                <td>
                    {{ $item->employee?->employee_id }}
                </td>
                <td>
                    @if($for_export ?? false)
                        {{ $item->employee?->name }}
                    @else
                        <h2 class="table-avatar">
                            @if ($item->employee)
                                <a href="{{ route('employees.active.show', ['id' => $item->employee?->id]) }}">
                                    {{ $item->employee?->name }}
                                </a>
                            @endif
                        </h2>
                    @endif
                </td>
                <td>
                    {{ $item->employee?->designation?->name }}
                </td>
                <td>
                    {{ $item->employee?->department?->name }}
                </td>
                <td>
                    {{ $item->employee?->department?->super_department?->name }}
                </td>
                <td>
                    {{ carbon($item->date)->format('j F, Y') }}
                </td>
                <td>
                    {{ carbon($item->check_in)->format('h:i A') }}
                </td>
                <td>
                    {{ $item->shift ? carbon($item->shift?->start ?? '00:00')->format('h:i A') : '--:--' }}
                </td>
                <td>
                    @if($item->shift) {{$item->shift?->name}} @else <span class="badge badge-info">In Roster</span> @endif
                </td>
                <td>
                    {{
                        carbon($item->late_time ?? '00:00:00')->format('H:i')
                    }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
