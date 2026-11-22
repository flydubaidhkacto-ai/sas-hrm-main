@php
    $info = company_data();
    // Hide leave types that don't affect leave balance (e.g., Work From Home) from balance table
    $types = $types->filter(fn($t) => ($t->affects_leave_balance ?? true));
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Leave Balance List
    </p>

    <style>
        table {
        font-family: Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        width: 100%;
        background: #fff !important;
        }

        table td, table th {
        border: 1px solid #ddd;
        padding: 3px;
        font-size: 10pt;
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

<table @if($for_export ?? false)
style="width: 100%"
@else
class="table table-striped custom-table"
@endif>
    <thead>
        <tr>
            <th>
                Code
            </th>
            <th>
                Emp. ID
            </th>
            <th>Name</th>
            @if($for_export ?? false)
                @foreach ($types as $type)
                    <th>
                        {{ $type->name }}
                    </th>
                @endforeach
            @else
                @foreach ($types as $type)
                    <th style="width: 200px">
                        {{ $type->name }}
                    </th>
                @endforeach
            @endif
            <th>Department</th>
            <th>Sup. Department</th>
            <th>Designation</th>
            <th>Mobile</th>
            <th>Shift</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($employees as $items)
            <tr>
                <td>
                    {{ $items->id }}
                </td>
                <td>
                    {{ $items->employee_id }}
                </td>
                @if($for_export ?? false)
                    <td>
                        {{ $items->name }}
                    </td>
                @else
                    <td>
                        <h2 class="table-avatar">
                            <a href="{{ route('employees.active.show', $items) }}">
                                {{ $items->name }}
                            </a>
                        </h2>
                    </td>
                @endif
                @if($for_export ?? false)
                    @foreach ($types as $type)
                        <td>
                            {{ \App\Models\LeaveBalance::employee_balance($items->id, $type->id, $year)['used'] ?? 0 }}/{{ \App\Models\LeaveBalance::employee_balance($items->id, $type->id, $year)['limit'] ?? 0 }}
                        </td>
                    @endforeach
                @else
                    @foreach ($types as $type)
                        @if ($type->is_editable)
                            <td>
                                <form method="POST" style=""
                                    action="{{ route('leaves.balance.update', $items) }}">
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    @csrf
                                    <div class="row" onchange="$(this).find('button').removeClass('d-none')"
                                        onkeydown="$(this).find('button').removeClass('d-none')">

                                            <div class="col-sm-6">
                                                <input type="number" step="0.01" name="types[{{ $type->id }}]"
                                                    id="" class="form-control" min="0"
                                                    max="365" style="min-width: 100px"
                                                    value="{{ \App\Models\LeaveBalance::employee_balance($items->id, $type->id, $year)['limit'] ?? 0 }}">
                                                    <p style="min-width: 100px">Used: {{ \App\Models\LeaveBalance::employee_balance($items->id, $type->id, $year)['used'] ?? 0 }}</p>
                                            </div>

                                        <div class="col-sm-12 mt-1">
                                            <button type="submit"
                                                class="btn btn-sm btn-success d-none">Save</button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        @else
                            <td>
                                Used:
                                {{ \App\Models\LeaveBalance::employee_balance($items->id, $type->id, $year)['used'] ?? 0 }}
                                /
                                {{ \App\Models\LeaveBalance::employee_balance($items->id, $type->id, $year)['limit'] ?? 0 }}
                            </td>
                        @endif
                    @endforeach
                @endif
                <td>{{ $items->department?->name }}</td>
                <td>{{ $items->department?->super_department?->name }}</td>
                <td>{{ $items->designation?->name }}</td>
                <td>{{ $items->phone_number }}</td>
                <td>
                    @if ($items->shift)
                        {{ $items->shift->name }} -
                        {{ carbon($items->shift->start)->format('h:i A') }} -
                        {{ carbon($items->shift->end)->format('h:i A') }}
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
