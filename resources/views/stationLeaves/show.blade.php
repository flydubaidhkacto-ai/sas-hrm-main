@php
    $balance = \App\Models\LeaveBalance::employee_balance(
        $leave->employee_id,
        $leave->leave_type_id,
        $leave->start_date->year,
    );
    $leave_types = \Cache::remember('leave_types', 60, function () {
        return \App\Models\LeaveType::all();
    });
    $can_approve = $leave->can_approve_this();
    $can_update = user()->is_super_admin();

    $prevLeave = \App\Models\StationLeaveApplication::query()
    ->where('employee_id', $leave->employee_id)
    ->where('status', 'approved')
    ->where('id', '<', $leave->id)
    ->orderBy('approved_at', 'desc')
    ->orderBy('id', 'desc')
    ->with('employee')
    ->first();


     $from = now()->subMonths(3)->startOfDay();
     $to   = now()->endOfDay();

    $attendanceSummaryLastThreeMonths  = app(\App\Services\Attendance\EmployeeAttendanceSummaryService::class)
        ->getAttendanceSummary($leave->employee_id, $from, $to);

@endphp

<div>
    <a href="{{ route('station.leave.download', $leave->id) }}" class="btn btn-sm btn-info float-right" target="_blank">
        <i class="fa fa-download"></i>
        Download
    </a>
    <a href="{{ route('station.leave.print', $leave->id) }}" class="btn btn-sm btn-info float-right ml-2 mr-2"
       target="_blank">
        <i class="fa fa-print"></i>
        Print
    </a>

    <div class="mb-2">
        <div class="row">
            <div class="col-3">
                <img src="/uploaded_files/{{ $leave->employee?->picture_file }}" alt="" class="w-100">
            </div>
            <div class="col-9">
                <h4>{{ $leave->employee?->name }}</h4>
                <p class="mb-0">
                    {{ $leave->employee?->department?->super_department?->name }}:
                    {{ $leave->employee?->department?->name }}
                </p>
                <p class="mb-0">{{ $leave->employee?->designation?->name }}</p>
                <p class="text-muted mb-0">{{ $leave->employee?->email }}</p>
                <p class="text-muted mb-0">{{ $leave->employee?->phone }}</p>
                <p class="text-muted mb-0">{{ $leave->employee?->address }}</p>
            </div>
        </div>
    </div>
    <form
            action="{{ route('approvals.approve', ['module' => 'station_leave', 'module_id' => $leave->id]) }}"
            method="post">
        @csrf
        <div class="mb-2">
            <b>Joining Date:</b>
            {{ carbon($leave->employee?->join_date)->format('d M, Y') }}
            <br>
            <b for="">Leave Type: </b> <span>Station</span>


        </div>
        <div class="mb-2 leave-aapl">
            <div class="form-group">
                <b>Applied at:</b>
                {{ $leave->created_at->format('d M, Y h:i A') }}
            </div>

            <div class="row">
                <div class="col-md-3">
                    <label for="">Start Date:</label>
                    <input type="text" name="start_date" class="form-control datetimepicker" required
                           value="{{ carbon($leave->start_date)->format('d-m-Y') }}" onchange="calculateDuration()"
                           @if (!$can_approve && !$can_update) readonly @endif>
                </div>
                <div class="col-md-3">
                    <label for="">End Date:</label>
                    <input type="text" name="end_date" class="form-control datetimepicker"
                           value="{{ carbon($leave->end_date)->format('d-m-Y') }}" onchange="calculateDuration()"
                           @if (!$can_approve && !$can_update) readonly @endif>

                </div>
                <div class="col-md-3">
                    <label for="">Total:</label>
                    @php
                        $duration = $leave->duration_count;
                    @endphp
                    <span class="leave_duration"></span>
                    <input type="text" name="total_leave" class="form-control"
                           value="{{ round($duration, 2) }} {{ $duration > 1 ? 'days' : 'day' }}" disabled>

                </div>
            </div>

            @if($can_update && $leave->status == 'approved')
                <input type="hidden" name="update_dates" value="1">
                <div class="mb-2">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa fa-check"></i>
                        Update Dates
                    </button>
                </div>
            @endif

            <div style="font-size: 22px;">

                <i class="fa fa-info-circle"
                   data-toggle="tooltip"
                   data-placement="top"
                   title="{{ collect($leave->applied_dates ?? [])->map(fn($date) => carbon($date)->format('d M Y'))->join(', ') }}"
                ></i>

            </div>
        </div>

        <div class="mb-2">
            <b for="">Station Leave Details:</b>
            <div style="border-left: 2px solid #d1d1d1" class="pl-3 mt-2 mb-2">
                <div class="mb-2">
                    <b for="">Station:</b>
                    <p>
                        {{ $leave->station ?? '--' }}
                    </p>
                </div>

                <div class="mb-2">
                    <b for="">Present Address:</b>
                    <p>
                        {{ $leave->present_address ?? '--' }}
                    </p>
                </div>
                <div class="mb-2">
                    <b for="">Address During Out of Station:</b>
                    <p>
                        {{ $leave->address_during_out_of_station ?? '--' }}
                    </p>
                </div>
                <div class="mb-2">
                    <b for="">Contact Number:</b>
                    <p>
                        {{ $leave->contact_number ?? '--' }}
                    </p>
                </div>
                <div class="mb-2">
                    <b for="">Alternative Number:</b>
                    <p>
                        {{ $leave->alternative_number ?? '--' }}
                    </p>
                </div>
                <div class="mb-2">
                    <b for="">Reason for Station Leave:</b>
                    <p>
                        {!! str_replace("\n", "<br>", $leave->reason ?? '--') !!}
                    </p>
                </div>
                <div class="mb-2">
                    <b for="">Remarks by Employee:</b>
                    <p>
                        {!! str_replace("\n", "<br>", $leave->remarks_by_employee ?? '--') !!}
                    </p>
                </div>
            </div>
        </div>


        @if($leave->attachment)
            <div class="mb-2">
                <b for="">Attachment:</b>
                <p>
                    <a href="{{ ($leave->attachment_url) }}" download="{{ $leave->attachment_name }}" target="_blank">
                        <i class="fa fa-file"></i>
                        {{ $leave->attachment_name }}
                    </a>
                </p>
            </div>
        @endif


        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light fw-bold d-flex align-items-center">
                <i class="fa fa-calendar-check me-2"></i>
                Attendance Summary Last 3 Months ({{ $attendanceSummaryLastThreeMonths['period']['from'] }}
                – {{ $attendanceSummaryLastThreeMonths['period']['to'] }})
            </div>

            <div class="card-body p-2">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-secondary">
                    <tr>
                        <th>Type</th>
                        <th class="text-center">Days</th>
                        <th class="text-center">Percentage</th>
                    </tr>
                    </thead>

                    <tbody>
                    {{-- Present --}}
                    <tr>
                        <td class="fw-semibold">Presence</td>

                        <td class="text-center">
                    <span class="badge bg-success fw-bold px-2">
                        {{ $attendanceSummaryLastThreeMonths['days']['presence'] }}
                    </span>
                        </td>

                        <td class="text-center">
                            {{ $attendanceSummaryLastThreeMonths['percentage']['presence'] }}%
                        </td>

                    </tr>

                    {{-- Absent --}}
                    <tr>
                        <td class="fw-semibold">Absent</td>

                        <td class="text-center">
                    <span class="badge bg-danger fw-bold px-2">
                        {{ $attendanceSummaryLastThreeMonths['days']['absent'] }}
                    </span>
                        </td>

                        <td class="text-center">
                            {{ $attendanceSummaryLastThreeMonths['percentage']['absent'] }}%
                        </td>

                    </tr>

                    {{-- Late --}}
                    <tr>
                        <td class="fw-semibold">Late</td>

                        <td class="text-center">
                    <span class="badge bg-warning text-dark fw-bold px-2">
                        {{ $attendanceSummaryLastThreeMonths['days']['late'] }}
                    </span>
                        </td>

                        <td class="text-center">
                            {{ $attendanceSummaryLastThreeMonths['percentage']['late'] }}%
                        </td>

                    </tr>

                    </tbody>
                </table>
            </div>
        </div>


        @if(config('app.customer') == 'sas' && $prevLeave)
            <b>Latest approved Station leave::</b>

                @if($prevLeave)
                    <div class="card card-body mb-2">
                        <table>
                            <tr>
                                <td>From:</td>
                                <td>
                                    <b>
                                        {{ carbon($prevLeave->start_date)->format('d M, Y') }}
                                    </b>
                                </td>
                            </tr>
                            <tr>
                                <td>To:</td>
                                <td>
                                    <b>
                                        {{ carbon($prevLeave->end_date)->format('d M, Y') }}
                                    </b>
                                </td>
                            </tr>
                            <tr>
                                <td>Type:</td>
                                <td>
                                    <b>
                                        Station Leave
                                    </b>
                                </td>
                            </tr>
                            <tr>
                                <td>Total Days:</td>
                                <td>
                                    <b>
                                        {{ $prevLeave->duration_count }}
                                    </b>
                                </td>
                            </tr>
                        </table>
                    </div>
                @endif

        @endif
        <div class="mt-2">
            @if($leave->published_for_view ||  $leave->can_approve('station_leave', user()))
                @include('layouts.approval', [
                    'model' => $leave,
                    'model_type' => 'station_leave',
                    'employee' => $leave->employee,
                ])
            @else
                <b>Status: </b> <span class="badge badge-warning">{{$leave->status}}</span>
            @endif
        </div>
    </form>
</div>

<script>
    window.calculateDuration = () => {
        $('.leave-aapl').each(function () {
            let start_date = $(this).find('.datetimepicker[name="start_date"]').val();
            let end_date = $(this).find('.datetimepicker[name="end_date"]').val();
            if (!start_date || !end_date) return
            // console.log('running', start_date, end_date);
            start_date = moment(start_date, 'DD-MM-YYYY');
            end_date = moment(end_date, 'DD-MM-YYYY');
            const duration = (end_date.diff(start_date, 'days'));
            const days = duration + 1;
            console.log('running', days, $(this).find('#leave_duration'));
            if (days < 1) {
                return $(this).find('.leave_duration').text('Invalid date');
            }
            $(this).find('.leave_duration').text(days + ' ' + (days > 1 ? 'days' : 'day'));
        })
    }
    $(document).ready(function () {
        window.calculateDuration();
    });
</script>


