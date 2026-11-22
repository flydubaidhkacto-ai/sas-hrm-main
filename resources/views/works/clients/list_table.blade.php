<table class="table table-striped custom-table">
    <thead>
        <tr>
            <th>SL. No.</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Company Name</th>
            <th>Company Email</th>
            <th>Company Phone</th>
            <th>Company Address</th>
            <th>Shipping Address</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($clients as $client)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $client->name }}</td>
            <td>{{ $client->email }}</td>
            <td>{{ $client->phone }}</td>
            <td>{{ $client->company_name }}</td>
            <td>{{ $client->company_email }}</td>
            <td>{{ $client->company_phone }}</td>
            <td>{{ $client->company_address }}</td>
            <td>{{ $client->shipping_address }}</td>
            <td>
                <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                <form action="{{ route('clients.destroy', $client->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
