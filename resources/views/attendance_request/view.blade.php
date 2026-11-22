<div>
    @if($attendance_request->punch_in)
    <b>Punch In: </b> {{ \Carbon\Carbon::parse($attendance_request->punch_in)->format('d M, Y h:i A') }} <br>
    @endif
    @if($attendance_request->punch_out)
    <b>Punch Out: </b> {{ \Carbon\Carbon::parse($attendance_request->punch_out)->format('d M, Y h:i A') }} <br>
    @endif
    @if($attendance_request->duty_type)
    <b>Duty Type: </b> {{ $attendance_request->duty_type }} <br>
    @endif
    @if($attendance_request->notes)
    <b>Note: </b> {{ $attendance_request->notes }} <br>
    @endif
    <b>Applied Date: </b> {{ $attendance_request->created_at->format('d M, Y') }} <br>
</div>

@include('layouts.approval', [
    'model' => $attendance_request,
    'model_type' => 'attendance_request',
    'employee' => $attendance_request->employee,
    'have_form' => true,
])
