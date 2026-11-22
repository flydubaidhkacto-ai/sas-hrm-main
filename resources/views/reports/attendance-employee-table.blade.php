@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Employee Attendance Report
    </p>

    <style>
        table {
        font-family: Arial, Helvetica, sans-serif;
        border-collapse: collapse;
            width: 100%;
        }

        table td, table th {
            border: 1px solid #ddd;
            padding: 4px;
        }

        table tr:nth-child(even){background-color: #f2f2f2;}

        table tr:hover {background-color: #ddd;}
        table tr td,
        table tr th,
        {
            font-size: 12px;
        }
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
        span {
            font-size: 12px;
        }
    </style>
@endif

@if(isset($data) && !empty($data['data']))
    <div>
        <div style="margin-top: 7px">
            <span>
                <b>
                    From:
                </b>
                {{ date('d M, Y', array_keys($data['data'])[0] ?? 0)}}
            </span>
            <span style="margin-left: 40px">
                <b>
                    To:
                </b>
                {{ date('d M, Y', @end(array_keys($data['data'])) ?? 0)}}
            </span>
        </div>
        <div style="margin-top: 7px">
            <span>
                <b>
                    Employee:
                </b>
                {{ $employee->name ?? '' }}
            </span>
            <span style="margin-left: 30px">
                <b>
                    Emp. ID:
                </b>
                {{ $employee->employee_id ?? '' }}
            </span>
            <span style="margin-left: 30px">
                <b>
                    Code:
                </b>
                {{ $employee->id ?? '' }}
            </span>
        </div>
        <div style="margin-top: 7px">
            <span>
                <b>
                    Sup. Department:
                </b>
                {{ $employee->department?->super_department?->name ?? '' }}
            </span>
            <span style="margin-left: 30px">
                <b>
                    Department:
                </b>
                {{ $employee->department?->name ?? '' }}
            </span>
            <span style="margin-left: 30px">
                <b>
                    Designation:
                </b>
                {{ $employee->designation?->name ?? '' }}
            </span>
        </div>
    </div>
    <table
        @if($for_export ?? false)
            style="width: 100%; margin-top: 10px"
        @else
            class="table table-striped custom-table mt-4"
        @endif
    >
        <thead>
            <tr>
                <th>S.L</th>
                <th>Date</th>
                <th>
                    Status
                </th>
                <th>
                    In Time
                </th>
                <th>
                    Out Time
                </th>
                <th>
                    W. Hour
                </th>
                <th>
                    O.T.
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data['data'] as $time => $item)
                <tr>
                    <td>
                        {{ $loop->iteration }}
                    </td>
                    <td>
                        {{ date('d M, Y', $time)}}
                    </td>
                    <td>
                        @php
                            $can_entry = false;
                        @endphp
                        @if($item['attendance'] ?? false)
                            @if($item['holiday'] ?? false)
                                <span style="color: #ff00ff">
                                    Present (H)
                                </span>
                            @elseif($item['weekend'] ?? false)
                                <span style="color: #ffb800">
                                    Present (W)
                                </span>
                            @else
                                Present
                            @endif
                        @elseif($item['leave'] ?? false)
                            @if($item['leave']->is_roster)
                                {{ $item['leave']->title }}
                            @elseif($item['leave']->leave_type?->bg_color)
                            <span style="color: {{ $item['leave']->leave_type->bg_color }}">
                                On Leave [{{ $item['leave']->leave_type->name }}]
                            </span>
                            @else
                                On Leave
                            @endif
                        @elseif($item['holiday'] ?? false)
                            <span class="text-success">
                                Holiday [{{ $item['holiday'] }}]
                            </span>
                        @elseif($item['absent'] ?? false)
                            <span class="text-danger">
                                Absent
                            </span>
                            @php
                                $can_entry = true;
                            @endphp
                        @elseif($item['weekend'] ?? false)
                            <span class="text-info">
                                Weekend [{{ date('l', $time) }}]
                            </span>
                        @endif
                    </td>
                    <td>
                        @if (($for_export ?? false))
                            @if($item['attendance'] ?? false)
                                @if($item['attendance']->check_in)
                                    {{ carbon($item['attendance']->check_in ?? '00:00')->format('h:i A') }}
                                @endif
                            @else
                                --:--
                            @endif
                        @else
                            @if($item['absent'] ?? false)
                                <input type="time"
                                    name="check_in"
                                    data-date="{{ date('Y-m-d', $time) }}"
                                    data-employee="{{ $employee->id }}"
                                    class="form-control timepicker check-in-{{ date('Y-m-d', $time) }}">
                            @elseif($item['attendance'] ?? false)
                                @if($item['attendance']->check_in)
                                    {{ carbon($item['attendance']->check_in ?? '00:00')->format('h:i A') }}
                                @endif
                            @else
                                --:--
                            @endif
                        @endif
                    </td>
                    <td>
                        @if ($for_export ?? false)
                            @if($item['attendance'] ?? false)
                                @if($item['attendance']->check_out)
                                    {{ carbon($item['attendance']->check_out ?? '00:00')->format('h:i A') }}
                                @endif
                            @else
                                --:--
                            @endif
                        @else
                            @if($item['absent'] ?? false)
                                <div class="d-flex align-items-center">
                                    <input type="time"
                                        name="check_out"
                                        data-date="{{ date('Y-m-d', $time) }}"
                                        data-employee="{{ $employee->id }}"
                                        class="form-control timepicker check-out-{{ date('Y-m-d', $time) }}">
                                    <button class="btn btn-sm btn-primary save-attendance ms-2"
                                            data-date="{{ date('Y-m-d', $time) }}"
                                            data-employee="{{ $employee->id }}">
                                        Save
                                    </button>
                                </div>
                            @elseif($item['attendance'] ?? false)
                                @if($item['attendance']->check_out)
                                    {{ carbon($item['attendance']->check_out ?? '00:00')->format('h:i A') }}
                                @endif
                            @else
                                --:--
                            @endif
                        @endif
                    </td>
                    <td>
                        @if($item['attendance'] ?? false)
                            @if($item['attendance']->working_time)
                                {{ carbon($item['attendance']->working_time ?? '00:00')->format('H:i') }}
                            @endif
                        @else
                            --:--
                        @endif
                    </td>
                    <td>
                        @if($item['attendance'] ?? false)
                            @if($item['attendance']->overtime_time)
                                {{ carbon($item['attendance']->overtime_time ?? '00:00')->format('H:i') }}
                            @endif
                        @else
                            --:--
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">
                        No Data
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top: 10px">
        <div style="width: 170px; display: inline-block; font-size: 12px">
            {{-- Max Worked Days: {{ $data['max_working_days'] ?? 0 }}
            <br> --}}
            Total Worked Days: {{ $data['total_worked_days'] ?? 0 }}
            <br>
            Total Worked Hours: {{ seconds_to_hh_mm($data['total_worked_seconds']) }}
            <br>
            Total Overtime Hours: {{ seconds_to_hh_mm($data['total_overtime_seconds']) }}
        </div>
        <div style="width: 150px; display: inline-block; font-size: 12px; margin-left: 20px">
            Total Late Days: {{ $data['total_late_days'] ?? 0 }}
            <br>
            Total Late Hours: {{ seconds_to_hh_mm($data['total_late_seconds']) }}
        </div>
        <div style="width: 150px; display: inline-block; font-size: 12px">
            Total Leave Days: {{ $data['total_leave_days'] ?? 0 }}
            <br>
            Total Absent Days: {{ $data['total_absent_days'] ?? 0 }}
            <br>
            Total Holidays: {{ $data['total_holidays'] ?? 0 }}
            <br>
            Total Weekends: {{ $data['total_weekends'] ?? 0 }}
        </div>
    </div>
@endif

<script>
    $(document).ready(function() {
        $('.save-attendance').on('click', function() {
            const date = $(this).data('date');
            const employeeId = $(this).data('employee');
            const checkIn = $(`.check-in-${date}`).val();
            const checkOut = $(`.check-out-${date}`).val();
            const button = $(this);
            // console.log(date, employeeId, checkIn, checkOut);

            if (!checkIn || !checkOut) {
                toastr.error('Please fill both check-in and check-out times');
                return;
            }

            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: '{{ route("attendance.manual.store") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    employee: employeeId,
                    date: date,
                    check_in: checkIn,
                    check_out: checkOut
                },
                success: function(response) {
                    // console.log(response);
                    if (response.success) {
                        toastr.success('Attendance saved successfully');
                        // Update the row with new values without page reload
                        const row = button.closest('tr');
                        row.find('td:eq(2)').html('Present');
                        row.find('td:eq(3)').html(formatTime(checkIn));
                        row.find('td:eq(4)').html(formatTime(checkOut));
                        if (response.data.working_time) {
                            const workingTime = response.data.working_time.split(':').slice(0, 2).join(':');
                            row.find('td:eq(5)').html(workingTime);
                        }
                        if (response.data.overtime_time) {
                            const overtimeTime = response.data.overtime_time.split(':').slice(0, 2).join(':');
                            row.find('td:eq(6)').html(overtimeTime);
                        }
                    } else {
                        toastr.error(response.message || 'Error saving attendance');
                    }
                },
                error: function() {
                    toastr.error('Error saving attendance');
                },
                complete: function() {
                    button.prop('disabled', false).html('<i class="fas fa-save"></i>');
                }
            });
        });

        function formatTime(time) {
            if (!time) return '--:--';
            const [hours, minutes] = time.split(':');
            const date = new Date();
            date.setHours(hours);
            date.setMinutes(minutes);
            return date.toLocaleString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true });
        }
    });
    </script>
