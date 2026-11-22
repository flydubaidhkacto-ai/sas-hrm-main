@extends('layouts.master')
@section('title', 'Settings')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            {!! Toastr::message() !!}
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Settings: Attendance
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">
                                Attendance Setting
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div>
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h4>
                            Auto checkout for forgotten punch
                            <span class="float-right">
                                <x-toggle-setting name="attendance_auto_checkout" :enabled="$attendance_auto_checkout" />
                            </span>
                        </h4>
                    </div>


                    <div class="card-body">
                        <form method="post">
                            @csrf
                            Everyday at
                            <input required type="time" style="width: 120px" name="attendance_auto_checkout_run_at"
                                value="{{ $attendance_auto_checkout_run_at ?? (old('attendance_auto_checkout_run_at') ?? '00:00') }}">
                            , auto checkout employees with a puch of
                            <input required type="time" style="width: 120px" name="attendance_auto_checkout_time"
                                value="{{ $attendance_auto_checkout_time ?? (old('attendance_auto_checkout_time') ?? '00:00') }}">
                            time on
                            <select required name="attendance_auto_checkout_day" style="width: 130px">
                                <option @if ($attendance_auto_checkout_day == 'previous') selected @endif value="previous">Previous day
                                </option>
                                <option @if ($attendance_auto_checkout_day == 'today') selected @endif value="today">Same day</option>
                            </select>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-info">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>
                            Overtime Policy Configuration
                            <span class="float-right">
                                <x-toggle-setting name="overtime_policy_configuration" :enabled="$overtime_policy_configuration" />
                            </span>
                        </h4>
                    </div>


                    <div class="card-body">
                        <form method="post">
                            @csrf
                            <label for="auto_over_time_policy">Auto Over Time Policy</label>
                            <select required name="auto_over_time_policy" id="auto_over_time_policy" style="width: 130px">
                                <option value="1_hour" @if ($auto_over_time_policy == '1_hour') selected @endif>1 Hour</option>
                                <option value="30_minutes" @if ($auto_over_time_policy == '30_minutes') selected @endif>30 Minutes
                                </option>
                                <option value="15_minutes" @if ($auto_over_time_policy == '15_minutes') selected @endif>15 Minutes
                                </option>
                            </select>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-info">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4>
                            Duplicate Punch Prevention
                            <span class="float-right">
                                <x-toggle-setting name="duplicate_punch_configuration" :enabled="$duplicate_punch_configuration" />
                            </span>
                        </h4>
                    </div>

                    <div class="card-body">
                        <form method="post">
                            @csrf
                            <label for="duplic">Punch only after </label>
                            <input required type="number" min="0" style="width: 50px"
                                name="duplicate_punch_minutes"
                                value="{{ old('duplicate_punch_minutes', $duplicate_punch_minutes ?? '') }}">
                             minutes
                            <div class="mt-2">
                                <button type="submit" class="btn btn-info">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4>
                            Delete Attendance Upon Late Punch
                            <span class="float-right">
                                <x-toggle-setting name="delete_attendnace_upon_late" :enabled="$delete_attendnace_upon_late" />
                            </span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            @csrf
                            Check In must be before/after
                            <input required type="number" min="0" style="width: 50px"
                                name="delete_attendnace_upon_late_minutes"
                                value="{{ $delete_attendnace_upon_late_minutes }}">
                                minutes of Shift
                            <div class="mt-2">
                                <button type="submit" class="btn btn-info">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>
                            Check In Before Shift Time
                            <small class="text-muted">
                                @if (!$check_in_before_shift_time_config)
                                    (Default: 2 hours)
                                @endif
                            </small>

                            <span class="float-right">
                                <x-toggle-setting name="check_in_before_shift_time_config" :enabled="$check_in_before_shift_time_config" />
                            </span>
                        </h4>
                    </div>


                    <div class="card-body">
                        <form method="post">
                            @csrf
                            <label for="check_in_before_shift_time">Check-In Before Shift Time</label>
                            <input required type="number" min="0" style="width: 250px"
                                name="check_in_before_shift_time"
                                value="{{ old('check_in_before_shift_time', $check_in_before_shift_time ?? '') }}">
                            <div class="mt-2">
                                <button type="submit" class="btn btn-info">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

                <div class="card mt-2">
                    <div class="card-header">
                        <h4>
                            Disable Auto In/Out State: Follow Check-In and Check-Out states from devices
                            <span class="float-right">
                                <x-toggle-setting name="block_auto_inout_state" :enabled="$block_auto_inout_state" />
                            </span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <p>
                            Upon enabling this option, the system will disable automatically assigning In/Out states to the machine logs data.
                            <br>
                            <span class="text-warning">
                                Employees have to manage the check-in and check-out state in the machine itself.
                            </span>
                        </p>
                    </div>
                </div>
                <div class="card mt-2">
                    <div class="card-header">
                        <h4>
                            Ignore All In-Between Check-In and Check-Out
                            <span class="float-right">
                                <x-toggle-setting name="attendance_ignore_in_between_punches" :enabled="$attendance_ignore_in_between_punches" />

                            </span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <p>
                            Upon enabling this option, the system will only use first check-in and last check-out of
                            employees to count working hours or overtime hours.
                            <br>
                            <b>Note: Enable it only if your employees take permission to check-in and check-out</b>
                        </p>
                    </div>
                </div>
                <div class="card mt-2">
                    <div class="card-header">
                        <h4>
                            Send SMS Notification to Company for In, Out, Late Punch
                            <span class="float-right">
                                <x-toggle-setting name="can_company_in_out_sms_notification" :enabled="$can_company_in_out_sms_notification" />

                            </span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <p>
                            Upon enabling this option, the system will SMS notification to Company phone Number
                            about the in, out, late punches of employees.
                            <br>
                            Set the Phone Number in <a href="{{ route('company.info') }}">Company Settings</a>
                        </p>
                    </div>
                </div>
                <div class="card mt-2">
                    <div class="card-header">
                        <h4>
                            Send SMS Notification to Company for Duty Summary
                            <span class="float-right">
                                <x-toggle-setting name="can_company_summary_sms_notification" :enabled="$can_company_summary_sms_notification" />
                            </span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <p>
                            Upon enabling this option, the system will send SMS notification to Company phone number
                            <i>(Set in Company Settings)</i>
                            about the duty summary of employees.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
