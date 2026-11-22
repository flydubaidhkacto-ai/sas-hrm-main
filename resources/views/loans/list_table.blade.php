@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Loans List
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
<table class="table">
    <thead>
        <tr>
            <th>S.L</th>
            <th>Ref.</th>
            <th>Employee</th>
            <th>Loan Amount</th>
            <th>
                Due Amount
            </th>
            <th>
                Paid Amount
            </th>
            <th>Reason</th>
            <th>Issue Date</th>
            <th>End Date</th>
            <th>
                Pay Term
            </th>
            <th>Status</th>
            @if ($for_export ?? false)
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
                    {{ $loop->iteration }}
                </td>
                <td>
                    {{ $item->id }}
                </td>
                <td>
                    {{-- <h2 class="table-avatar">
                        @if ($item->employee)
                            <a href="{{ route('employees.active.show', $item->employee->id) }}">
                                {{ $item->employee?->name }}
                            </a>
                        @endif
                    </h2> --}}

                    @if ($for_export ?? false)
                        {{ $item->employee?->name . ' ' }}
                    @else
                        <h2 class="table-avatar">
                            @if ($item->employee)
                                <a href="{{ route('employees.active.show', $item->employee->id) }}">
                                    {{ $item->employee?->name }}
                                </a>
                            @endif
                        </h2>
                    @endif
                    <br>
                    <small><strong>Created:</strong> {{ carbon($item->created_at)->format('d F, Y H:i A') }}</small>

                </td>
                <td>
                    {{ $item->amount }} Tk
                </td>
                <td>
                    {{ $item->amount - $item->paid_amount_till_now()->sum('amount') }} Tk
                </td>
                <td>
                    {{ $item->paid_amount_till_now()->sum('amount') }} Tk.
                </td>
                <td>
                    {{ $item->reason }}
                </td>
                <td>
                    {{ carbon($item->issue_date)->format('d F, Y') }}
                </td>
                <td>
                    {{ \Carbon\Carbon::parse($item->issue_date)->addMonths($item->tenure_in_months)->format('d F, Y') }}
                </td>
                <td>
                    {{-- @dump($item) --}}
                    @if ($item->cut_off_as === 'once')
                        Once
                    @else
                        {{ $item->cut_off_amount }} Tk
                        <br>
                        Every month
                    @endif
                </td>
                <td>
                    @if ($item->status === 'approved')
                        <span class="badge badge-info">Approved</span>
                    @elseif($item->status === 'rejected')
                        <span class="badge badge-danger">Rejected</span>
                    @else
                        <span class="badge badge-warning">Pending</span>
                        <br>
                    @endif

                    <button class="btn btn-sm btn-info" data-toggle="modal"
                        data-target="#detailsModal{{ $item->id }}">
                        Details
                    </button>
                </td>
                @if ($for_export ?? false)
                @else
                    <td>
                        @if (role_can('loan.delete'))
                            <button class="btn btn-danger btn-sm btn-xs"
                                onclick="if(confirm('Are you sure? This will delete this loan.')) $('#deleteForm{{ $item->id }}').submit()">
                                <i class="fa fa-trash"></i>
                            </button>
                            <form action="{{ route('loans.destroy', $item) }}" method="post"
                                id="deleteForm{{ $item->id }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="fine_id" value="{{ $item->id }}">
                            </form>
                        @else
                            --
                        @endif
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
