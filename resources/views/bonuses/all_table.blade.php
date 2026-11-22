@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Employee Bonus List
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
<div class="table-responsive mt-2">
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
                <th>Name</th>
                <th>Department</th>
                <th>Sup. Department</th>
                <th>Designation</th>
                <th>Month</th>
                <th>
                    Bonus
                </th>
                <th>
                    Category
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $total = 0;
            @endphp
            @foreach ($bonuses as $item )
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
                                <a href="{{ route('employees.active.show', $item->user_id) }}">
                                    {{ $item->employee?->name }}
                                </a>
                            </h2>
                        @endif
                    </td>
                    <td>{{ $item->employee?->department?->name }}</td>
                    <td>{{ $item->employee?->department?->super_department?->name }}</td>
                    <td>{{ $item->employee?->designation?->name }}</td>
                    <td>
                        {{ carbon($item->month)->format('F, Y') }}
                    </td>
                    <td>
                        {{ $item->amount }} Tk.
                        @php
                            $total += $item->amount
                        @endphp
                    </td>
                    <td>
                        {{ $item->category?->name }}
                    </td>
                </tr>
            @endforeach
            @if($for_export ?? false)
                <tr>
                    <td colspan="10" style="text-end">
                        <div style="text-align: right">
                            <b>
                                Total: {{ $total }} Tk
                            </b>
                        </div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
