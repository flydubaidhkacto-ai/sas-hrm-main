@php
    $info = company_data();
    $missed_checkout = $missed_checkout ?? false;
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        {{ $missed_checkout ? "Missed Check-Out Report" : "Attendance Report" }}
    </p>

    <style>
        table {
        font-family: Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        width: 100%;
        }

        table td, table th {
        border: 1px solid #ddd;
        padding: 8px;
        }

        table tr:nth-child(even){background-color: #f2f2f2;}

        table tr:hover {background-color: #ddd;}

        table th {
        padding-top: 10px;
        padding-bottom: 10px;
        text-align: left;
        background-color: #643a86;
        color: white;
        }
        @media print {
            table th {
                background-color: unset;
                color: unset;
            }
            table {
                width: 100%; /* Make the table width 100% for print */
                font-size: 7pt; /* Adjust font size for better readability in print */
                /* Add any other print-specific styles here */
            }
        }
    </style>
@endif
<table
    @if($for_export ?? false)
        style="width: 100%"
    @else
        class="table table-striped custom-table attendance-table"
    @endif
>
    <thead>
        <tr>
            <th style="text-align: right">
                S.L
            </th>
            <th>Code</th>
            <th>
                Emp. ID
            </th>
            <th>Employee</th>
            <th>Department</th>
            @if(config('have_multi_sup_dept'))
            <th>Super Dpt</th>
            @endif
            <th>Designation</th>
            <th>Shift</th>
            <th>Date</th>
            <th>In Time</th>
            <th>Out Time</th>
            <th>W. Hour</th>
            <th>O.T.</th>
            <th>Note</th>
            <th>
                Duty type
            </th>
            @if($for_export ?? false)
            @else
                @if(request()->editable)
                    <th>Action</th>
                @endif
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $item)
            <tr>
                <td>
                    @if($for_export ?? false)
                    @elseif(request()->editable && !$item->is_in_roster && (role_can('reports') || role_can('reports.self.super_department')))
                    <div class="form-check" >
                        <input class="form-check-input" type="checkbox"
                            id="flexCheckDefault{{ $item->id }}" style="scale: 1.5"
                            value="{{ $item->id }}"
                            onchange="if(this.checked) {
                                $('#count__').html((parseInt($('#count__').first().text())) + 1);
                            } else {
                                $('#count__').html((parseInt($('#count__').first().text())) - 1);
                            }"
                            name="attendance_ids[]">
                    </div>
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    @endif
                    {{ $loop->iteration }}
                </td>
                <td>
                    {{ $item->employee?->id }}
                </td>
                <td>
                    {{ $item->employee?->employee_id }}
                </td>
                <td>
                    @if($for_export ?? false)
                        {{ $item->employee?->name }}
                    @else
                        <h2 class="table-avatar">
                            <a href="{{ route('employees.active.show', ['id' => $item->employee_id]) }}">
                                @if ($item->employee)
                                    {{ $item->employee?->name }}
                                @endif
                            </a>
                        </h2>
                    @endif
                </td>
                <td>
                    {{ $item->employee?->department?->name }}
                </td>
                @if(config('have_multi_sup_dept'))
                <td>
                    <div style="width: 200px;">
                        {{ $item->employee?->department?->super_department?->name }}
                    </div>
                </td>
                @endif
                <td>
                    {{ $item->employee?->designation?->name }}
                </td>
                <td>
                    {{-- @dump($item->is_in_roster) --}}
                    @if($item->is_in_roster)
                        <span class="badge badge-info">
                            In roster
                        </span>
                    @else
                        <span data-toggle="tooltip" title="{{ $item->shift?->start_time }} - {{ $item->shift?->end_time }}">
                            {{ $item->shift?->name }}
                        </span>
                    @endif
                </td>
                <td>
                    {{carbon($item->date)->format('d F')}}

                    @if($item->added_manually_by)
                        @php
                            $user = \App\Models\User::find($item->added_manually_by);
                        @endphp
                        @if($user)
                        <br>
                        <i class="small">Added manually By: {{ $user->name }}</i>
                        @endif
                    @endif
                </td>

                @php
                    $all_checks = $item->used_in_out_records()
                        ->orderBy('date_time', 'asc')
                        ->get();
                    $check_in_record = $all_checks
                        [$all_checks
                        ->search(fn ($i) => $i->date_time->format('Y-m-d H:i:s') == $item->check_in)] ?? null;
                    $check_out_record = $all_checks
                        [$all_checks
                        ->search(fn ($i) => $i->date_time->format('Y-m-d H:i:s') == $item->check_out)] ?? null;
                    // dd($check_in_record);
                @endphp
                <td>
                    @if($item->check_in)
                        {{ carbon($item->check_in ?? '00:00')->format('h:i A') }}
                        @if ($check_in_record && $check_in_record->location)
                            <br>
                            <a href="https://www.google.com/maps?q={{ str_replace('-', ',', $check_in_record->location) }}" target="_blank">
                                View <i class="fa fa-location"></i>
                            </a>
                        @endif
                    @else
                        --:--
                    @endif
                </td>
                <td>
                    @if($item->check_out)
                        {{ carbon($item->check_out ?? '00:00')->format('d M, Y') }}<br/>
                        {{ carbon($item->check_out ?? '00:00')->format('h:i A') }}
                        @if ($check_out_record && $check_out_record->location)
                            <br>
                            <a href="https://www.google.com/maps?q={{ str_replace('-', ',', $check_out_record->location) }}" target="_blank">
                                View <i class="fa fa-location"></i>
                            </a>
                        @endif
                    @else
                        --:--
                    @endif
                </td>
                <td data-toggle="tooltip" title="{{ $item->working_time ?? '-:-:-' }}">
                    {{ carbon($item->working_time ?? '00:00')->format('H:i') }}
                </td>
                <td data-toggle="tooltip" title="{{ $item->overtime_time ?? '-:-:-' }}">
                    {{ carbon($item->overtime_time ?? '00:00')->format('H:i') }}
                </td>
                <td>
                    {{ $item->notes ? str_replace("\n", '<br>', $item->notes) : '--' }}
                    @if($item->notes_by)
                        @php
                            $user = \App\Models\User::find($item->notes_by);
                        @endphp
                        @if($user)
                        <br><i class="small">By: {{ $user->name }}</i>
                        @endif
                    @endif
                </td>
                <td>
                    {{ $item->duty_type ?? '--' }}
                </td>
                @if($for_export ?? false)
                @else
                    @if(request()->editable)
                        <td>
                            <button class="btn btn-info btn-sm btn-xs"
                            data-toggle="collapse" data-target="#demo{{$item->id}}" class="accordion-toggle"
                            @if ($item->shift->is_24_hour_shift) disabled @endif
                            >
                                <span @if ($item->shift->is_24_hour_shift)
                                    title="Edit not available for 24 hour shift" data-toggle="tooltip"
                                @endif>
                                    See In-Out
                                </span>
                            </button>
                            <button class="btn btn-danger btn-sm btn-xs" onclick="if(confirm('Are you sure? This will delete all in-out records of this attendance.')) $('#deleteForm{{$item->id}}').submit()">
                                <i class="fa fa-trash"></i>
                            </button>
                            <form action="{{ route('attendance.destroy', $item) }}" method="post" id="deleteForm{{$item->id}}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="attendance_id" value="{{ $item->id }}">
                            </form>
                        </td>
                    @else

                    @endif
                @endif
            </tr>
            @if($for_export ?? false)
            @else
                @if(request()->editable && !$item->shift->is_24_hour_shift)
                    <tr class="accordian-body collapse" id="demo{{$item->id}}">
                        <td colspan="12" class="hiddenRow">
                            <div>
                                <table class="table table-striped custom-table m-auto mb-0" style="max-width: 1600px">
                                    <thead>
                                        <tr>
                                            <th>S.L</th>
                                            <th>Date</th>
                                            <th>Shift</th>
                                            <th>In</th>
                                            <th>Out</th>
                                            <th>W. Hour</th>
                                            <th>O.T.</th>
                                            <th>Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            if($item->shift?->is_24_hour_shift)
                                            {
                                                $mappedInOuts = \App\Actions\EmployeePunched\Calculate24HourShift::mappedInOuts($item);
                                            }else {
                                                $mappedInOuts = \App\Actions\EmployeePunched\CalculateWorkingTimeOfAttendance::execute(
                                                    $item, true, null
                                                )['mappedInOuts'] ?? [];
                                            }
                                        @endphp
                                        @foreach ($mappedInOuts as $key => $value)
                                            @php
                                                $in = $value[0] ?? null;
                                                $out = $value[1] ?? null;
                                            @endphp
                                            <tr>
                                                <td>
                                                    {{ $key + 1 }}
                                                </td>
                                                <td>
                                                    {{ $in?->date_time?->format('d F') }}
                                                </td>
                                                <td>
                                                    {{-- @dump($item->is_in_roster) --}}
                                                    @if($item->is_in_roster)
                                                        <span class="badge badge-info">
                                                            In roster
                                                        </span>
                                                    @else
                                                        {{ $item->shift?->name }}
                                                    @endif
                                                </td>
                                                <td class="in">
                                                    {{-- <div > --}}
                                                        @if($in)
                                                            {{ ($in->date_time)?->format('h:i:s A') }}
                                                        @else
                                                            --
                                                        @endif
                                                    {{-- </div> --}}
                                                </td>
                                                <td class="out">
                                                    @if($out)
                                                        {{ ($out->date_time)?->format('d M, h:i:s A') }}
                                                    @else
                                                        --
                                                    @endif
                                                </td>
                                                <td colspan="2" class="d-none form" style="width: 500px"
                                                    onkeyup="calculateDuration__(this)"
                                                    onclick="calculateDuration__(this)"
                                                    onmousemove="calculateDuration__(this)"
                                                    onmouseup="calculateDuration__(this)"
                                                >
                                                    <form action="{{ route('attendance.update', $item) }}" class="" method="POST">
                                                        @csrf

                                                        @method('PUT')
                                                        <input type="hidden" name="out_id" value="{{ $out && $out->id != $in?->id ? $out->id : '' }}">
                                                        <input type="hidden" name="in_id" value="{{ $in ? $in->id : '' }}">
                                                        <div class="row">
                                                            <div style="width: 200px">
                                                                @if($item->is_in_roster)
                                                                    <span class="badge badge-info">
                                                                        In roster
                                                                    </span>
                                                                @else
                                                                    <label for="shift_id">Shift</label>
                                                                    <select name="shift_id" id="shift_id" class="form-control">
                                                                        @foreach ($shifts as $shift)
                                                                            <option value="{{ $shift->id }}" {{ $item->shift_id == $shift->id ? 'selected' : '' }}>{{ $shift->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                @endif
                                                            </div>
                                                            <div class="col-4">
                                                                <label for="">Check In</label>
                                                                <div class="d-flex">
                                                                    <input type="datetime-local" class="form-control"
                                                                        name="punches[{{ $key }}][in]"
                                                                        value="{{ $in ? $in->date_time->format('Y-m-d H:i') : '' }}"
                                                                        required
                                                                    >
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <label for="">Check Out</label>
                                                                <div class="d-flex">
                                                                    <input type="datetime-local" class="form-control"
                                                                        value="{{ $out ? $out->date_time->format('Y-m-d H:i') : '' }}"
                                                                        name="punches[{{ $key }}][out]"
                                                                        required
                                                                    >
                                                                </div>
                                                                <div class="duration"></div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12 pl-0 mt-1">
                                                                <label for="notes">Notes:</label>
                                                                <textarea name="notes" class="form-control" rows="2">{!! $item->notes !!}</textarea>
                                                            </div>
                                                            <div class="col-12 mt-1 pl-0">
                                                                <button class="btn btn-sm btn-info">
                                                                    Update
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </td>
                                                <td>
                                                    {{-- @dump($value[2]) --}}
                                                    {{ gmdate('H:i', $value[2]['worked'] ?? 0) }}
                                                </td>
                                                <td>
                                                    {{ gmdate('H:i', $value[2]['overtime'] ?? 0) }}
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning" onclick="openThisEdit(this)">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{-- <div class="mt-2">
                                    <button class="btn btn-sm btn-info" onclick="this.nextElementSibling.classList.toggle('d-none');this.remove()">
                                        <i class="fa fa-edit"></i> Change Shift
                                    </button>
                                    <div class="d-none">
                                        <form action="{{ route('attendance.shift.update', $item) }}" method="POST" class="d-flex gap-2">
                                            @csrf
                                            <select name="shift_id" id="" class="form-control" style="width: 200px">
                                                @foreach ($shifts as $shift)
                                                    <option value="{{ $shift->id }}" {{ $item->shift_id == $shift->id ? 'selected' : '' }}>{{ $shift->name }}</option>
                                                @endforeach
                                            </select>
                                            <div style="width: 300px">
                                                &nbsp;
                                                <button class="btn btn-sm btn-info">
                                                    <i class="fa fa-check"></i> Update Shift
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div> --}}
                            </div>
                        </td>
                        <td colspan="3">
                            @if($item->system_notes)
                                System Notes:
                                <br>
                                {!! $item->system_notes !!}
                            @endif
                        </td>
                    </tr>
                @endif
            @endif
        @endforeach
    </tbody>
</table>
@if ($for_export ?? false)
@else
    <script>
        function openThisEdit(btn)
        {
            let con = $(btn).parent().parent();
            con.find('td.in').toggleClass('d-none');
            con.find('td.out').toggleClass('d-none');
            con.find('td.form').toggleClass('d-none');
        }
        function calculateDuration__(cont)
        {
            cont = $(cont)
            let startDate = cont.find('.check_in_date').val();
            let start = cont.find('.check_in').val();
            let endDate = cont.find('.check_out_date').val();
            let end = cont.find('.check_out').val();
            if(!start || !end || !startDate || !endDate) return
            start = moment(`${startDate} ${start}`, 'DD-MM-YYYY HH:mm');
            end = moment(`${endDate} ${end}`, 'DD-MM-YYYY HH:mm');

            const duration = moment.duration(end.diff(start));
            const totalHours = duration.asHours();
            const hours = Math.floor(totalHours);
            const minutes = Math.round((totalHours - hours) * 60);

            cont.find('.duration')[0].innerText = `${hours} hours and ${minutes} minutes`;
        }
    </script>
@endif
