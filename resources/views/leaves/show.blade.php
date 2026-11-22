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

    $prevLeaves = [];
    foreach($leave_types as $type) {
        $prevLeaves[] = \App\Models\LeaveApplication::query()
            ->where('id', '<', $leave->id)
            ->where('leave_type_id', $type->id)
            ->where('status', 'approved')
            ->where('is_hourly', false)
            ->where('employee_id', $leave->employee_id)
            ->latest()
            ->with([
                'employee',
                'leave_type',
            ])
            ->first();
    }

     $from = now()->subMonths(3)->startOfDay();
     $to   = now()->endOfDay();

    $attendanceSummaryLastThreeMonths  = app(\App\Services\Attendance\EmployeeAttendanceSummaryService::class)
        ->getAttendanceSummary($leave->employee_id, $from, $to);

@endphp

<div>
    <a href="{{ route('leaves.download', $leave->id) }}" class="btn btn-sm btn-info float-right" target="_blank">
        <i class="fa fa-download"></i>
        Download
    </a>
    <a href="{{ route('leaves.print', $leave->id) }}" class="btn btn-sm btn-info float-right ml-2 mr-2" target="_blank">
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
            action="{{ route('approvals.approve', ['module' => 'leave', 'module_id' => $leave->id]) }}"
            method="post">
        @csrf
        <div class="mb-2">
            <b>Joining Date:</b>
            {{ carbon($leave->employee?->join_date)->format('d M, Y') }}
            <br>
            
            <b>Leave Type:</b>

            @if ($can_update)
                <div>
                    <select class="form-control"
                            style="max-width: 300px"
                            name="leave_type_id">
                        @foreach ($leave_types as $item)
                            <option value="{{ $item->id }}"
                                @if ($item->id == $leave->leave_type_id) selected @endif>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>

                    @if ($can_update)
                        <button type="submit"
                                class="btn btn-sm btn-primary mt-2"
                                formaction="{{ route('leaves.update-type', $leave->id) }}"
                                formmethod="post">
                            <i class="fa fa-save"></i>
                            Save Leave Type
                        </button>
                    @endif
                </div>
            @else
                <p class="mb-1">
    <strong style="color: #212529; font-size: 15px;">
        {{ $leave->leave_type?->name ?? ($leave->type ?? 'N/A') }}
    </strong>

                    @if ($leave->leave_type)
                        <span class="{{ $leave->leave_type->is_paid ? 'text-success' : 'text-danger' }}">
                            ({{ $leave->leave_type->is_paid ? 'Paid' : 'Unpaid' }})
                        </span>
                    @endif
                </p>
            @endif

        </div>
        <div class="mb-2 leave-aapl">
            <div class="form-group">
                <b>Applied at:</b>
                {{ $leave->created_at->format('d M, Y h:i A') }}
            </div>
            @if ($leave->is_hourly)
                <span class="badge badge-info">
                    Hourly Leave
                </span>
                <div class="row">
                    <div class="col-md-4">
                        <label for="">Date:</label>
                        <input value="{{ carbon($leave->start_date)->format('d-m-Y') }}" required type="text"
                               name="date" class="form-control datetimepicker">
                    </div>
                    <div class="col-md-4">
                        <label for="">Start Time:</label>
                        <input value="{{ carbon($leave->start_time)->format('H:i') }}" required type="time"
                               name="start_time" class="form-control timepicker">
                    </div>
                    <div class="col-md-4">
                        <label for="">End Time:</label>
                        <input value="{{ carbon($leave->end_time)->format('H:i') }}" required type="time"
                               name="end_time" class="form-control timepicker">
                    </div>
                </div>
            @else
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
                        {{--                        <span class="leave_duration"></span>--}}
                    </div>
                </div>
            @endif
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
                <b>Total: </b>
                @if ($leave->is_hourly)
                    {{ round(carbon($leave->start_time)->diffInHours($leave->end_time), 2) }} hours
                @else
                    @php
                        $duration = $leave->duration_count;
                    @endphp
                    {{ round($duration, 2) }} {{ $duration > 1 ? 'days' : 'day' }}
                    <i class="fa fa-info-circle"
                       data-toggle="tooltip"
                       data-placement="top"
                       title="{{ collect($leave->applied_dates ?? [])->map(fn($date) => carbon($date)->format('d M Y'))->join(', ') }}"
                    ></i>
                @endif
            </div>
        </div>
        <div class="mb-2">
            <b for="">Reason:</b>
            <p>
                {{ $leave->title }}
            </p>
        </div>
        @if ($leave->is_station_leave)
            <div class="mb-2">
                <b for="">Station Leave:</b>
                <div style="border-left: 2px solid #d1d1d1" class="pl-3 mt-2 mb-2">
                    <div class="mb-2">
                        <b for="">Station:</b>
                        <p>
                            {{ $leave->station ?? '--' }}
                        </p>
                    </div>
                    <div class="mb-2">
                        <b for="">From:</b>
                        <p>
                            {{ $leave->station_from_date ? carbon($leave->station_from_date)->format('d M, Y') : '--' }}
                        </p>
                    </div>
                    <div class="mb-2">
                        <b for="">To:</b>
                        <p>
                            {{ $leave->station_to_date ? carbon($leave->station_to_date)->format('d M, Y') : '--' }}
                        </p>
                    </div>
                    <div class="mb-2">
                        <b for="">Present Address:</b>
                        <p>
                            {{ $leave->station_present_address ?? '--' }}
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
                            {{ $leave->station_contact_number ?? '--' }}
                        </p>
                    </div>
                    <div class="mb-2">
                        <b for="">Alternative Number:</b>
                        <p>
                            {{ $leave->station_alternative_number ?? '--' }}
                        </p>
                    </div>
                    <div class="mb-2">
                        <b for="">Reason for Station Leave:</b>
                        <p>
                            {!! str_replace("\n", "<br>", $leave->reason_for_station_leave ?? '--') !!}
                        </p>
                    </div>
                    <div class="mb-2">
                        <b for="">Remarks by Employee:</b>
                        <p>
                            {!! str_replace("\n", "<br>", $leave->station_remarks_by_employee ?? '--') !!}
                        </p>
                    </div>
                </div>
            </div>
        @endif

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
        {{-- <div class="mb-2 d-none">
            <i for="">Description:</i>
            <div>{!! str_replace("\n", '<br>', $leave->description) !!}</div>
        </div> --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light fw-bold d-flex align-items-center">
                <i class="fa fa-calendar-check me-2"></i>
                Leave Balance
            </div>

            <div class="card-body p-2">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-secondary">
                    <tr>
                        <th>Leave Type</th>
                        <th class="text-center">Taken</th>
                        <th class="text-center">Remaining</th>
                        <th class="text-center">Limit</th>
                        <th style="width:35%">Usage</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($leave_types as $leave_type)

                        <?php
                        $leave_balance = \App\Models\LeaveBalance::employee_balance(
                            $leave->employee_id,
                            $leave_type->id,
                            $leave->start_date->year
                        );

                        $used = number_format($leave_balance['used'], 1);
                        $limit = $leave_balance['limit'];
                        $remaining = number_format(max(0, $limit - $used), 1);
                        $percent = $limit > 0
                            ? min(100, max(0, round(($used / $limit) * 100)))
                            : 0;

                        $percent_with_fraction = $limit > 0
                            ? min(100, max(0, round(($used / $limit) * 100, 1)))
                            : 0;
                        ?>

                        <tr>
                            <td class="fw-semibold">
                                {{ $leave_type->name }}
                            </td>

                            <td class="text-center">
                        <span class="badge bg-danger text-white fw-bold px-2">
                            {{ $used }}
                        </span>

                            </td>

                            <td class="text-center">
                        <span class="badge bg-success text-white fw-bold px-2">
                        {{ $remaining }}
                        </span>

                            </td>
                            <td>{{$limit}}</td>

                            <td>
                                <div class="progress" style="height: 18px">
                                    <div class="progress-bar
                                {{ $percent >= 90 ? 'bg-danger' : ($percent >= 70 ? 'bg-warning' : 'bg-success') }}"
                                         role="progressbar"
                                         style="width: {{ $percent_with_fraction }}%">
                                        {{ $percent_with_fraction }}%
                                    </div>
                                </div>
                            </td>
                        </tr>

                    @endforeach

                    </tbody>
                </table>
            </div>
        </div>

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


        @if(config('app.customer') == 'sas')
            <b>Previous Approved Leave:</b>
            @foreach ($prevLeaves as $prev)
                @if($prev)
                    <div class="card card-body mb-2">
                        <table>
                            <tr>
                                <td>From:</td>
                                <td>
                                    <b>
                                        {{ carbon($prev->start_date)->format('d M, Y') }}
                                    </b>
                                </td>
                            </tr>
                            <tr>
                                <td>To:</td>
                                <td>
                                    <b>
                                        {{ carbon($prev->end_date)->format('d M, Y') }}
                                    </b>
                                </td>
                            </tr>
                            <tr>
                                <td>Type:</td>
                                <td>
                                    <b>
                                        {{ $prev->type }}
                                    </b>
                                </td>
                            </tr>
                            <tr>
                                <td>Total Days:</td>
                                <td>
                                    <b>
                                        {{ $prev->duration_count }}
                                    </b>
                                </td>
                            </tr>
                        </table>
                    </div>
                @endif
            @endforeach
        @endif

        {{-- Approved Leave History: load only when clicked --}}
        <div class="mt-3 mb-3 approved-leave-history-section">
            <button type="button"
                    class="btn btn-outline-primary btn-sm"
                    onclick="
                        var area = $(this).closest('.approved-leave-history-section').find('.approved-leave-history-results');
                        if (area.is(':visible')) {
                            area.hide();
                        } else {
                            area.show();
                            if (area.attr('data-loaded') !== '1') {
                                area.text('Loading approved leaves...');
                                area.load(this.getAttribute('data-url'), function(response, status) {
                                    if (status === 'success') {
                                        area.attr('data-loaded', '1');
                                    } else {
                                        area.text('Unable to load approved leave history.');
                                    }
                                });
                            }
                        }
                    "
                    data-url="{{ route('leaves.approved-history', $leave->id) }}">
                <i class="fa fa-history"></i>
                View Approved Leaves ({{ now()->year }})
            </button>

            <div class="approved-leave-history-results mt-3"
                 style="display:none;"></div>
        </div>
        <div class="mt-2">
            @if($leave->published_for_view ||  $leave->can_approve('leave', user()))
                @include('layouts.approval', [
                    'model' => $leave,
                    'model_type' => 'leave',
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


