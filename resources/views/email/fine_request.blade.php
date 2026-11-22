<!DOCTYPE html>
<html>
<body>
    <h2>Fine Request {{ ucfirst($status) }}</h2>
    <p>Your fine request of amount {{ $modal->amount }} has been {{ $status }}.</p>
    <p>Reason: {{ $modal->reason }}</p>
    <p>{{ ucfirst($status) }} by: {{ $actionBy }}</p>
    <p>{{ ucfirst($status) }} Date: {{ $actionDate }}</p>
</body>
</html>
