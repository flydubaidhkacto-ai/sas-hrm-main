<table class="table table-striped custom-table">
    <thead>
        <tr>
            <th>SL. No</th>
            <th>From Account</th>
            <th>To Account</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($transfers as $transfer)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $transfer->fromAccount->name }}</td>
            <td>{{ $transfer->toAccount->name }}</td>
            <td>{{ $transfer->amount }}</td>
            <td>{{ Carbon\Carbon::parse($transfer->transfer_date)->format('d-m-Y H:i:s') }}</td>
            <td>{{ $transfer->description }}</td>
            <td>
                {{-- <a href="{{ route('transfers.edit', $transfer->id) }}" class="btn btn-warning"><i class="fa fa-edit"></i></a> --}}
                <form action="{{ route('transfers.destroy', $transfer->id) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this transfer?')"><i class="fa fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
