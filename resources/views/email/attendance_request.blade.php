<!DOCTYPE html>
<html>
<body>
    <h2>Attendance Request {{ ucfirst($status) }}</h2>
    <p>Your attendance request for {{ \Carbon\Carbon::parse($modal->date)->format('d F, Y') }} has been {{ $status }}.</p>
    <p>Punch In: {{ \Carbon\Carbon::parse($modal->punch_in)->format('d F, Y h:i A') }}, Punch Out: {{ \Carbon\Carbon::parse($modal->punch_out)->format('d F, Y h:i A') }}</p>
    <p>Reason: {{ $modal->notes }}</p>
    <p>{{ ucfirst($status) }} by: {{ $actionBy }}</p>
    <p>{{ ucfirst($status) }} Date: {{ $actionDate }}</p>
</body>
</html>
