<table class="table table-striped custom-table">
    <thead>
        <tr>
            <th>SL. No</th>
            <th>From Account</th>
            <th>To Account</th>
            <th>Type</th>
            <th>Category</th>
            <th>Method</th>
            <th>Amount</th>
            <th>Description</th>
            <th>Transaction Date</th>
            {{-- <th>Status</th> --}}
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $transaction)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $transaction->fromAccount->name }}</td>
            <td>{{ $transaction->toAccount->name }}</td>
            <td>{{ ucfirst($transaction->transaction_type) }}</td>
            <td>{{ ucfirst($transaction->transaction_category) }}</td>
            <td>{{ ucfirst($transaction->transaction_method) }}</td>
            <td>{{ $transaction->amount }}</td>
            <td>{{ $transaction->description }}</td>
            <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y h:i A') }}</td>
            {{-- <td>
                <span class="badge badge-{{ $transaction->transaction_status == 'pending' ? 'warning' : ($transaction->transaction_status == 'approved' ? 'success' : 'danger') }}">
                    {{ ucfirst($transaction->transaction_status) }}
                </span>
            </td> --}}
            <td>
                <a href="{{ route('transactions.edit', $transaction->id) }}" class="btn btn-warning"><i class="fa fa-edit"></i></a>
                <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
