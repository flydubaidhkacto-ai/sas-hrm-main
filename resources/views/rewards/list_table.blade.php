@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Employee Reward List
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
@endif >
    <thead>
        <tr>
            <th>
                Ref.
            </th>
            <th>
                Code
            </th>
            <th>
                Emp. ID
            </th>
            <th>
                Name
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
                Reward
            </th>
            <th>
                Reason
            </th>
            <th>
                Date
            </th>
            <th>
                Added by
            </th>
            @if($for_export ?? false)
            @else
                <th>
                    Action
                </th>
            @endif
        </tr>
    </thead>
    <tbody>
        @forelse ($data as $item)
            <tr>
                <td>
                    {{ $item->id }}
                </td>
                <td>
                    {{ $item->employee?->id }}
                </td>
                <td>
                    {{ $item->employee?->employee_id }}
                </td>

                <td>
                    @if($for_export ?? false)
                        {{ $item->employee?->name . ' ' }}
                    @else
                        <h2 class="table-avatar">
                            <a href="{{ route('employees.active.show', ['id' => $item->employee?->id ?? 1]) }}">
                            {{ $item->employee?->name }}
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
                    {{ $item->amount }} Tk
                </td>
                <td>
                    <div style="max-width: 250px">
                        {{ $item->name }}
                    </div>
                </td>
                <td>
                    {{ $item->date->format('d M, Y') }}
                </td>
                <td>
                    {{ $item->added?->name ?: '--' }}
                </td>
                @if($for_export ?? false)
                @else
                    <td>
                        <div>
                            <button class="btn btn-danger btn-sm" onclick="if(confirm('Are you sure?')){event.preventDefault(); $(this).next().submit(); }">
                                <i class="fa fa-trash"></i>
                            </button>
                            <form action="{{ route('rewards.destroy', $item->id) }}" method="POST" class="d-inline-block">@csrf @method('DELETE')</form>
                        </div>
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center">
                    No data found
                </td>
            </tr>
        @endforelse
    </tbody>
</table>