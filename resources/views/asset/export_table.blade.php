<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset List</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        h2 { text-align: center; margin: 0; }
        .subtitle { text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>{{ company_data()['name'] ?? 'Asset List' }}</h2>
    <p class="subtitle">Asset List</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Tag</th>
                <th>Category</th>
                <th>User</th>
                <th>Location</th>
                <th>Purchase Value</th>
                <th>Present Value</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $key => $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->tag }}</td>
                <td>{{ $item->asset_category?->name ?? 'N/A' }}</td>
                <td>{{ $item->user ?? 'N/A' }}</td>
                <td>{{ $item->super_department?->name ?? ($item->location ?? 'N/A') }}</td>
                <td>{{ $item->purchase_value ?? 'N/A' }}</td>
                <td>{{ $item->present_value ?? 'N/A' }}</td>
                <td>{{ $item->status == 1 ? 'Active' : 'Inactive' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
