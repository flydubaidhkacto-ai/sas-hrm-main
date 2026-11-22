<div class="card border-0">
    <div class="card-header bg-light">
        <strong>Approved Leave History — {{ $year }}</strong>
        <div class="text-muted small">
            Employee: {{ $leave->employee?->name }}
        </div>
    </div>

    <div class="card-body p-2">
        @if($approvedLeaves->isEmpty())
            <div class="alert alert-info mb-0">
                No approved leaves found for {{ $year }}.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Leave Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Days / Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvedLeaves as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->leave_type?->name ?? $item->type }}</td>
                                <td>{{ carbon($item->start_date)->format('d M Y') }}</td>
                                <td>{{ carbon($item->end_date)->format('d M Y') }}</td>
                                <td>
                                    @if($item->is_hourly)
                                        {{ $item->start_time && $item->end_time
                                            ? round(carbon($item->start_time)->diffInMinutes(carbon($item->end_time)) / 60, 2)
                                            : 0 }} hours
                                    @else
                                        {{ number_format($item->duration_count, 2) }} days
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <h6 class="mt-3"><strong>Summary by Leave Type</strong></h6>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Leave Type</th>
                            <th>Times</th>
                            <th>Total Days</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvedLeaves->where('is_hourly', false)->groupBy('leave_type_id') as $items)
                            <tr>
                                <td>{{ $items->first()->leave_type?->name ?? $items->first()->type }}</td>
                                <td>{{ $items->count() }}</td>
                                <td>{{ number_format($items->sum('duration_count'), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td>Grand Total</td>
                            <td>{{ $approvedLeaves->where('is_hourly', false)->count() }}</td>
                            <td>{{ number_format($approvedLeaves->where('is_hourly', false)->sum('duration_count'), 2) }} days</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($approvedLeaves->where('is_hourly', true)->isNotEmpty())
                <p class="text-muted small mb-0">
                    Hourly leave applications are listed above but excluded from day totals.
                </p>
            @endif
        @endif
    </div>
</div>