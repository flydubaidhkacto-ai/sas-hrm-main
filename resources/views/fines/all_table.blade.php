@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Fine Report
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
            <th>S.L</th>
            <th>Ref.</th>
            <th>Employee</th>
            <th>Fine Amount</th>
            <th>Due Amount</th>
            <th>Paid Amount</th>
            <th>Reason</th>
            <th>Applied Date</th>
            <th>
                Pay Term
            </th>
            <th class="">Status</th>
            @if($for_export ?? false) @else
                <th>
                    Action
                </th>
            @endif
        </tr>
    </thead>
    <tbody>
        @forelse ($data as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    {{ $item->id }}
                </td>
                <td>
                    @if($for_export ?? false)
                        {{ $item->employee?->name }}
                    @else
                        <h2 class="table-avatar">
                            <a href="{{ route('employees.active.show', ['id' => $item->employee?->id]) }}">
                                @if ($item->employee)
                                    {{ $item->employee?->name }}
                                @endif
                            </a>
                        </h2>
                    @endif
                </td>
                <td>
                    {{ $item->amount }} Tk
                </td>
                <td>
                    {{ ($item->amount) - ($item->paid_amount_till_now()->sum('amount')) }} Tk
                </td>
                <td>
                    {{ ($item->paid_amount_till_now()->sum('amount')) }} Tk.
                </td>
                <td>
                    {{ $item->reason }}
                </td>
                <td>
                    {{ carbon($item->created_at)->format('d F, Y') }}
                </td>
                <td>
                    @if ($item->cut_off_as === 'once')
                        Once
                    @else
                        {{ $item->cut_off_amount }} Tk
                        <br>
                        Every month
                    @endif
                </td>
                <td class="">
                    @if($item->status === 'approved')
                        <span class="badge badge-info">Approved</span>
                    @elseif($item->status === 'rejected')
                        <span class="badge badge-danger">Rejected</span>
                    @else
                        <span class="badge badge-warning">Pending</span>
                        <br>
                    @endif

                    <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailsModal{{$item->id}}">
                        Details
                    </button>
                </td>
                @if($for_export ?? false) @else
                    <td>
                        <button class="btn btn-danger btn-sm btn-xs" onclick="if(confirm('Are you sure? This will delete this fine.')) $('#deleteForm{{$item->id}}').submit()">
                            <i class="fa fa-trash"></i>
                        </button>
                        <form action="{{ route('fines.destroy', $item) }}" method="post" id="deleteForm{{$item->id}}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="fine_id" value="{{ $item->id }}">
                        </form>
                    </td>
                @endif
            </tr>
        @empty
            {{-- <tr>
                <th colspan="6" class="text-center">
                    No Data Found
                </th>
            </tr> --}}
        @endforelse
    </tbody>
</table>


