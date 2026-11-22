@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Station Leave Application List
    </p>

    <style>
        table {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
            background: #fff !important;
            font-size: 9px;
        }

        table td, table th {
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
        @endif >
    <thead>
    <tr>
        <th>
            S.L
        </th>
        @if($for_export ?? false)
        @else
            <th>
                Action
            </th>
        @endif
        <th>
            Emp. Code
        </th>
        <th>
            Emp. ID
        </th>
        <th>
            Emp. Name
        </th>
        <th>
            Department
        </th>
        <th>
            Designation
        </th>
        <th>
            Reason
        </th>
        <th>
            Start Date
        </th>
        <th>
            End
        </th>
        <th>
            Duration
        </th>
        <th>
            Type
        </th>
        <th>
            Approved At
        </th>
        @if($for_export ?? false)
            <th>
                Status
            </th>
        @else
            <th>
                Action
            </th>
        @endif
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $item)
        <tr>
            <td>
                {{ $loop->iteration }}
            </td>
            @if($for_export ?? false)
            @else
                <td>
                    @if($item->status === 'approved')
                        <span class="badge badge-info">Approved</span>
                    @elseif($item->status === 'rejected')
                        <span class="badge badge-danger">Rejected</span>
                    @else
                        <span class="badge badge-warning">Pending</span>
                        <br>
                    @endif
                    <br>
                    {{-- @if(!$item->is_final_decided() || !$item->is_decided()) --}}
                    <button href="{{ route('station.leave.show', $item) }}" class="btn btn-primary btn-sm"
                            data-toggle="modal" data-target="#editAppl{{ $item->id }}"
                    >
                        Open
                    </button>
                    {{-- @endif --}}
                </td>
            @endif
            <td>
                {{ $item->employee?->id }}
            </td>
            <td>
                {{ $item->employee?->employee_id }}
            </td>
            <td>
                {{ $item->employee?->name }}
            </td>
            <td>
                {{ $item->employee?->department?->super_department?->name }}:
                {{ $item->employee?->department?->name }}
            </td>
            <td>
                {{ $item->employee?->designation?->name }}
            </td>
            <td>
                <div style="width: 200px">{{ $item->reason }}</div>
            </td>
            <td>

                {{ carbon($item->start_date)->format('d F, Y') }}

            </td>
            <td>

                {{ carbon($item->end_date)->format('d F, Y') }}

            </td>
            <td>

                @php
                    $duration = $item->duration_count;
                @endphp
                {{ round($duration, 2) }} {{ $duration > 1 ? 'days' : 'day' }}

            </td>
            <td>

                <span class="badge" style="background-color: #0000FF; font-size: 15px; color:white;">Station Leave</span>
            </td>
            <td>
                @if ($item->status === 'approved')
                    {{ $item->updated_at->format('d M, Y h:i A') }}
                @else -
                @endif
            </td>
            @if($for_export ?? false)
                <td>
                    {{ $item->status }}
                </td>
            @else
                <td>
                    @if(role_can('leave'))
                        <button class="btn btn-danger btn-sm btn-xs"
                                onclick="if(confirm('Are you sure? This will delete this station leave application.')) $('#deleteForm{{$item->id}}').submit()">
                            <i class="fa fa-trash"></i>
                        </button>
                        <form action="{{ route('station.leave.destroy', $item) }}" method="post"
                              id="deleteForm{{$item->id}}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="leave_id" value="{{ $item->id }}">
                        </form>
                    @else
                        -
                    @endif
                </td>
            @endif
        </tr>
    @endforeach
    </tbody>
</table>

