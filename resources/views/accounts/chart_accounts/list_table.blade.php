<table class="table table-striped custom-table">
    <thead>
        <tr>
            <th>SL. No</th>
            <th>Name</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $account)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $account->name }}</td>
            <td>{{ $account->type }}</td>
            <td>{{ $account->amount }}</td>
            <td>{{ $account->description }}</td>
            <td>
                <a href="{{ route('chart-of-accounts.edit', $account->id) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route('chart-of-accounts.destroy', $account->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
