
@extends('layouts.master')
@section('title', 'Create Shift')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">

            {{-- student tab --}}
            {{-- <div class="mb-3">
                @include('tabs.student')
            </div> --}}

            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            @if($is_24_hour_shift)
                                24 Hour Shift: Create
                            @elseif(request()->students)
                                Student Shifts: Create
                            @else
                                Employee Shifts: Create
                            @endif
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            @if(request()->students)
                            <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
                            @else
                            <li class="breadcrumb-item"><a href="{{ route('employees.active.list') }}">Employees</a></li>
                            @endif
                            <li class="breadcrumb-item"><a href="{{ route('shifts.index') }}">Shifts</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            <div>
                @include('layouts.alerts')
                <form action="{{ route('shifts.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="students" value="{{ request()->students }}">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="col-form-label">Shift Name</label>
                                <div class="input-group time timepicker">
                                    <input class="form-control" name="name" required>
                                </div>
                            </div>
                        </div>
                        @if(!$is_24_hour_shift)
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group" >
                                    <label>Start Time</label>
                                    <div class="input-group time timepicker">
                                        <input class="form-control" type="time" name="start_time" required onchange="calculateDuration()">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">
                                    <label>End Time</label>
                                    <div class="input-group time timepicker">
                                        <input type="time" name="end_time" class="form-control" required onchange="calculateDuration()">
                                    </div>
                                </div>
                                <span id="shift_duration"></span>
                            </div>
                            <div class="col-sm-5 col-md-3">
                                <div class="form-group">
                                    <label>Break Time (In Minutes) </label>
                                    <input type="number" value="0" name="break_time" class="form-control" >
                                </div>
                            </div>
                            <div class="col-sm-5 col-md-3">
                                <div class="form-group">
                                    <label>Track Break by Checkout?</label>
                                    <select name="track_break_by_checkout" class="form-control">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-7 col-md-3">
                                <div class="form-group">
                                    <label>Break Time before Overtime (In Minutes) </label>
                                    <input type="number" value="0" name="break_time_before_ot_minutes" class="form-control" >
                                </div>
                            </div>

                            <div class="col-sm-7 col-md-3">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input class="form-control floating datetimepicker" type="text" name="start_date">
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="start_time" value="00:00">
                            <input type="hidden" name="end_time" value="23:59">
                            <input type="hidden" name="break_time" value="0">
                            <input type="hidden" name="break_time_before_ot_minutes" value="0">
                            <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}">
                            <input type="hidden" name="is_24_hour_shift" value="1">
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group" >
                                    <label>Max Working Hours</label>
                                    <div class="input-group time timepicker">
                                        <input class="form-control" type="number" name="max_working" required value="8">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label>Max Overtime Hours</label>
                                    <div class="input-group time timepicker">
                                        <input type="number" name="max_overtime" class="form-control" required value="0">
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div @class([
                            'd-none' => $is_24_hour_shift,
                            'col-sm-12' => true,
                        ])>
                            <div class="form-group wday-box">
                                <label for="" class="mr-3">
                                    Working days:
                                </label>

                                <label class="checkbox-inline">
                                    <input name="working_days[]" type="checkbox" value="saturday" class="days recurring" checked=""><span class="checkmark">Sat</span>
                                </label>

                                <label class="checkbox-inline">
                                    <input name="working_days[]" type="checkbox" value="sunday" class="days recurring" checked=""><span class="checkmark">Sun</span>
                                </label>

                                <label class="checkbox-inline">
                                    <input name="working_days[]" type="checkbox" value="monday" class="days recurring" checked="">
                                    <span class="checkmark">Mon</span>
                                </label>

                                <label class="checkbox-inline">
                                    <input name="working_days[]" type="checkbox" value="tuesday" class="days recurring" checked="">
                                    <span class="checkmark">Tue</span>
                                </label>

                                <label class="checkbox-inline">
                                    <input name="working_days[]" type="checkbox" value="wednesday" class="days recurring" checked="">
                                    <span class="checkmark">Wed</span>
                                </label>

                                <label class="checkbox-inline">
                                    <input name="working_days[]" type="checkbox" value="thursday" class="days recurring" checked="">
                                    <span class="checkmark">Thu</span>
                                </label>

                                <label class="checkbox-inline">
                                    <input name="working_days[]" type="checkbox" value="friday" class="days recurring"
                                    @if($is_24_hour_shift) checked @endif
                                    >
                                    <span class="checkmark">Fri</span>
                                </label>
                            </div>
                        </div>
                        {{-- <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="can_have_overtime" id="can_have_overtime" value="1">
                                <label class="form-check-label" for="can_have_overtime">Can Have Overtime</label>
                            </div>
                        </div> --}}
                    </div>
                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function calculateDuration()
        {
            let start = $('[name=start_time]').val();
            let end = $('[name=end_time]').val();
            if(!start || !end) return
            start = moment(`2000-01-01 ${start}`, 'YYYY-MM-DD HH:mm');
            end = moment(`2000-01-01 ${end}`, 'YYYY-MM-DD HH:mm');
            if (end.isBefore(start)) {
                end.add(1, 'day');
            }
            const duration = moment.duration(end.diff(start));
            const totalHours = duration.asHours();
            const hours = Math.floor(totalHours);
            const minutes = Math.round((totalHours - hours) * 60);

            document.getElementById('shift_duration').innerText = `${hours} hours and ${minutes} minutes`;
            // document.getElementById('shift_duration').innerText = `Duration: ${hours > 9 ? hours : '0' + hours}:${minutes > 9 ? minutes : '0' + minutes}`;
        }
    </script>
@endsection
