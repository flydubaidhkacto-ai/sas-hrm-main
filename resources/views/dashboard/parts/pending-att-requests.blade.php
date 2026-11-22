@php
$pending = App\Models\AttendanceRequest::query()
->where('status', 'pending');

// filter limitations according to access
$canApproveEmps = App\Models\ApprovalProcess::canApproveEmployees('attendance_request', user());
if(
    role_can('attendance-requests')
)
{
// if()
}else
if($canApproveEmps && $canApproveEmps['employees'])
{
    $pending = App\Models\AttendanceRequest::related_by(user()->id, 'attendance_request', 'pending');
    // $pending->whereIn('user_id', [
    // ...$canApproveEmps['employees']->pluck('id'), user()->id
    // ]);
}
else {
    $pending->where('user_id', user()->id);
}
$pending = App\Models\AttendanceRequest::pending_for_approval($pending->latest()->limit(1000)->get());
$pendingList = $pending->take(5);
@endphp
<div class="card" style="border-top: 3px solid #10d66b;">
    <div class="card-body">
        <h4 class="card-title">
            Pending Att. Requests
            <span class="badge bg-inverse-danger ml-2">
                {{ $pending->count() }}
            </span>
        </h4>
        @if ($pendingList->count())
        @foreach ($pendingList as $item)
        <div class="leave-info-box">
            <div class="media align-items-center">
                <a href="{{ route('employees.active.show', $item->user_id) }}" class="avatar">
                    <img @if ($item->employee?->picture_file) src="/uploaded_files/{{ $item->employee?->picture_file }}"
                    @else
                    src="https://www.seekpng.com/png/small/966-9665493_my-profile-icon-blank-profile-image-circle.png"
                    @endif />
                </a>
                <div class="media-body">
                    <button class="btn btn-primary btn-sm float-right" data-toggle="modal"
                        data-target="#editAttendance{{ $item->id }}">
                        Open
                    </button>
                    <div class="my-0 text-sm">
                        {{ $item->employee?->name }}
                    </div>
                </div>
            </div>
            <div class="row align-items-center mt-3">
                <div class="col-6">
                    <h6 class="mb-0">
                        {{ $item->title }}
                        <br>
                        ({{ carbon($item->date)->format('d M, Y') }})
                    </h6>
                </div>
                <div class="col-6 text-right">
                    <span class="badge bg-inverse-danger">Pending</span>
                </div>
            </div>
            <div>
                <small class="text-muted" style="font-size: 10px">
                    {{ carbon($item->created_at)->diffForHumans() }}
                </small>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade edit-fine" id="editAttendance{{ $item->id }}" tabindex="-1" role="dialog"
            aria-labelledby="editAttendance{{ $item->id }}Title" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Att. Request of:
                            {{ $item->employee?->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" onkeyup="calculateDuration()" onmousemove="calculateDuration()"
                        onmouseup="calculateDuration()">
                        @include('attendance_request.view', [
                        'attendance_request' => $item,
                        ])
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        @else
        <i>Nothing here!</i>
        @endif
        @if ($pending->count() > 5)
        <div class="load-more text-center">
            <a class="text-dark" href="{{ route('attendance-requests') }}">See all</a>
        </div>
        @endif
    </div>
</div>
