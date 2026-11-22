<table class="table table-striped custom-table">
    <thead>
        <tr>
            <th>SL. No</th>
            <th>Bill Number</th>
            <th>Vendor</th>
            <th>Due Date</th>
            <th>Amount Due</th>
            <th>Amount Paid</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($bills as $bill)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $bill->bill_number }}</td>
            <td>{{ $bill->vendor->name }}</td>
            <td>{{ Carbon\Carbon::parse($bill->due_date)->format('d-m-Y') }}</td>
            <td>{{ $bill->amount_due }}</td>
            <td>{{ $bill->amount_paid }}</td>
            <td>
                @if($bill->status == 'pending')
                    <span class="badge bg-warning">Pending</span>
                @elseif($bill->status == 'partially paid')
                    <span class="badge bg-info">Partially Paid</span>
                @else
                    <span class="badge bg-success">Paid</span>
                @endif</td>
            <td>
                <a href="{{ route('bills.edit', $bill->id) }}" class="btn btn-warning"><i class="fa-solid fa-pen-to-square"></i></a>
                <form action="{{ route('bills.destroy', $bill->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this bill?')"><i class="fa-solid fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
