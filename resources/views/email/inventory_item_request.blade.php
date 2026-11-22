<!DOCTYPE html>
<html>
<body>
    <h2>Inventory Item Request {{ ucfirst($status) }}</h2>
    <p>Your request for inventory item '{{ $modal->inventoryItem->name }}' has been {{ $status }}.</p>
    <p>{{ ucfirst($status) }} by: {{ $actionBy }}</p>
    <p>{{ ucfirst($status) }} Date: {{ $actionDate }}</p>
</body>
</html>
