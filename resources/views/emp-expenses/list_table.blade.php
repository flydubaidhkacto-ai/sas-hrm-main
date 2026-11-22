@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Employee Daily Expense List
    </p>

    <style>
        table {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
            background: #fff !important;
        }

        table td,
        table th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #ddd;
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
                width: 100%;
                /* Make the table width 100% for print */
                font-size: 7pt;
                /* Adjust font size for better readability in print */
                /* Add any other print-specific styles here */
            }
        }
    </style>
@endif


<table @if ($for_export ?? false) style="width: 100%"
@else
class="table table-striped custom-table" @endif>
    <thead>
        <tr>
            <th>Ref.</th>
            <th>Emp. ID</th>
            <th>Employee</th>
            <th>Super Department</th>
            <th>Department</th>
            <th>Designation</th>
            <th>Expense Category</th>
            <th>Name</th>
            <th>Amount</th>
            <th>Date</th>
            @if ($for_export ?? false)
                <th>Status</th>
            @else
                <th>Action</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($expenses as $key => $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->employee?->employee_id }}</td>
                <td>{{ $item->employee?->name }}</td>
                <td>{{ $item->employee?->department?->super_department?->name }}</td>
                <td>{{ $item->employee?->department?->name }}</td>
                <td>{{ $item->employee?->designation?->name }}</td>
                <td>{{ $item->rel_to_expensed_category?->name }}</td>
                <td>
                    {{ \Str::limit($item->name, 50) }}
                </td>
                <td>{{ $item->amount }}</td>
                <td>{{ $item->date }}</td>
                <td>
                    <div style="width: 100px">
                        @if ($item->status == 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif ($item->status == 'approved')
                            <span class="badge badge-success">Approved</span>
                        @elseif ($item->status == 'rejected')
                            <span class="badge badge-danger">Rejected</span>
                        @endif
                        <br>
                        <a data-toggle="modal" data-target="#editAppl{{ $item->id }}" class="btn btn-sm btn-primary">
                            Open
                        </a>
                        @if(role_can('emp-expenses') || is_admin_of_super_department(
                            $item->employee?->department?->super_department_id
                        ))
                        <form action="{{ route('emp-expenses.destroy', $item->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this expense?')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
