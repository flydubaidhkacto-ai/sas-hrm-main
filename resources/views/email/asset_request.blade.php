<!DOCTYPE html>
<html>
<body>
    @php
        $asset = \App\Models\Asset::find($modal->asset_id);
    @endphp
    <h2>Asset Request {{ ucfirst($status) }}</h2>
    <p>Your request for asset <strong>{{ $asset->name }}</strong> has been {{ $status }}.</p>
    <p>{{ ucfirst($status) }} by: {{ $actionBy }}</p>
    <p>{{ ucfirst($status) }} Date: {{ $actionDate }}</p>
</body>
</html>
