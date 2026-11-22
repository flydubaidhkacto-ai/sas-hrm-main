@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h1 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h1>
    <p style="text-align: center">
        {{ $info['address'] ?? '' }}
    </p>
    <h3 style="text-align: center">
        Employee Leave Report
    </h3>
    <span>From: {{ $start_date?->format('d F, Y') }}</span><span style="margin-left: 20px;">To: {{ $end_date?->format('d F, Y') }}</span>
    <table style="margin-top: 20px;">
        <tr>
            <td style="vertical-align: top; width: 70%;">
                <div style="padding-right: 20px;">
                    <div>
                        Code: {{ $employee->id }}
                    </div>
                    @if($employee->employee_id)
                    <div>
                        Emp. ID: {{ $employee->employee_id }}
                    </div>
                    @endif
                    <div>
                        Name: {{ $employee->name }}
                    </div>
                    <div>
                        Designation: {{ $employee->designation?->name }}
                    </div>
                    <div>Department: {{ $employee->department?->name }}</div>
                    <div>Super Department: {{ $employee->department?->super_department?->name }}</div>
                </div>
            </td>
            <td style="width: 30%;">
                <table style="width: 100%;">
                    <tr>
                        <td colspan="2">
                            <b>Leave Types</b>
                        </td>
                    </tr>
                    @foreach (\App\Models\LeaveType::all() as $item)
                        @php
                        $bal = \App\Models\LeaveBalance::employee_balance($employee->id, $item->id);
                        @endphp
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $bal['used'] ?? 0 }}/{{ $bal['limit'] ?? 0 }} </td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>
    <style>
        table {
            width: 100%;
            font-family: Arial, Helvetica, sans-serif;
        }
        table.table {
            border-collapse: collapse;
            width: 100%;
            background: #fff !important;
        }

        table.table td,
        table.table th {
            border: 1px solid #ddd;
            padding: 3px;
            font-size: 10pt;
        }

        table.table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table.table tr:hover {
            background-color: #ddd;
        }

        table.table th {
            padding-top: 10px;
            padding-bottom: 10px;
            text-align: left;
            background-color: #643a86;
            color: white;
        }

        @media print {
            table.table th {
                background-color: unset;
                color: unset;
            }

            table.table {
                width: 100%;
                /* Make the table width 100% for print */
                font-size: 7pt;
                /* Adjust font size for better readability in print */
                /* Add any other print-specific styles here */
            }
        }
    </style>
@endif

<table
@if ($for_export ?? false)
    style="width: 100%; margin-top: 20px;" class="table"
@else
class="table table-striped custom-table" @endif>
    <thead>
        <tr>
            <th>SL</th>
            <th>Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Duration</th>
            <th>Reason</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $key => $leave)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $leave->leave_type?->name }}</td>
                <td>{{ carbon($leave->start_date)->format('d F, Y') }}</td>
                <td>{{ carbon($leave->end_date)->format('d F, Y') }}</td>
                <td>{{ $leave->start_date->diffInDays($leave->end_date) + 1 }} days</td>
                <td>
                    <div style="width: 200px">{{ $leave->title }}</div>
                </td>
                <td>{{ $leave->status }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No leave records found</td>
            </tr>
        @endforelse
        <tr>
            <td colspan="4" style="text-align: right"><strong>Total:</strong></td>
            <td><strong>{{ $data->sum(function ($leave) {
                return $leave->start_date->diffInDays($leave->end_date) + 1;
            }) }}
                    days</strong></td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>
