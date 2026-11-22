<!DOCTYPE html>
<html>
<body>
    <h2>Leave Request {{ ucfirst($status) }}</h2>
    <p>Your leave request ({{ $modal->type }}) from {{ \Carbon\Carbon::parse($modal->start_date)->format('d F, Y') }}
       to {{ \Carbon\Carbon::parse($modal->end_date)->format('d F, Y') }} has been {{ $status }}.</p>
    <p>Reason: {{ $modal->description }}</p>
    <p>{{ ucfirst($status) }} by: {{ $actionBy }}</p>
    <p>{{ ucfirst($status) }} Date: {{ $actionDate }}</p>
</body>
</html>
