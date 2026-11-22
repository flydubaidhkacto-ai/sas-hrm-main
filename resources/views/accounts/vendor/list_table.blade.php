<table class="table table-striped custom-table">
    <thead>
        <tr>
            <th>SL. No</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($vendors as $vendor)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $vendor->name }}</td>
            <td>{{ $vendor->email }}</td>
            <td>{{ $vendor->phone }}</td>
            <td>{{ $vendor->address }}</td>
            <td>
                <a href="{{ route('vendors.edit', $vendor->id) }}" class="btn btn-warning"><i class="fa-solid fa-pen-to-square"></i></a>
                <form action="{{ route('vendors.destroy', $vendor->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this vendor?')"><i class="fa-solid fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
