@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Long Absent Report
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
            <th>
                Employee
            </th>
            <th>
                Department
            </th>
            @if(config('have_multi_sup_dept'))
            <th>
                Sup. Department
            </th>
            @endif
            <th>
                Designation
            </th>
            <th>
                Phone
            </th>
            <th>
                Absent Since
            </th>
            <th>
                Shift
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach ($employees as $employee)
            <tr>
                <td>
                    {{ $loop->iteration }}
                </td>
                <td style="width: 100px">
                    {{ $employee->id }}
                </td>
                <td style="width: 100px">
                    {{ $employee->employee_id }}
                </td>
                <td>
                    @if($for_export ?? false)
                        {{ $employee->name }}
                    @else
                        <h2 class="table-avatar">
                            <a href="{{ route('employees.active.show', $employee->id) }}">
                                {{ $employee->name }}
                            </a>
                        </h2>
                    @endif
                </td>
                <td>
                    {{ $employee->department?->name }}
                </td>
                @if(config('have_multi_sup_dept'))
                <td>
                    {{ $employee->department?->super_department?->name }}
                </td>
                @endif
                <td>
                    {{ $employee->designation?->name }}
                </td>
                <td>
                    {{ $employee->phone_number }}
                </td>
                <td>
                    {{ carbon($employee->long_absent?->start_date)->format('d M, Y') }} -
                    {{ $employee->long_absent?->total_days }} days
                </td>
                <td>
                    {{ $employee->shift?->name }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
