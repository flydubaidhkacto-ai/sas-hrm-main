@extends('layouts.master')
@section('title', "Apply Leave")
@section('content')
@php

@endphp
<!-- Page Wrapper -->
<div class="page-wrapper" onclick="_calculateDuration()">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Apply Leave
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leaves.history') }}">Leaves</a></li>
                        <li class="breadcrumb-item active">Apply</li>
                    </ul>
                </div>
            </div>
        </div>
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <form action="{{ route('leaves.apply.save') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="">
                @if(
                    role_can('leave')
                    || user()->admin_of_super_departments
                )
                <div class="row">
                    <div class="col-sm-6 col-md-3">
                        <label for="">Super Department</label>
                        <select name="super_department" id="" class="form-control super-department-select2">
                            @if(request()->super_department)
                            <option value="{{ request()->super_department }}" selected>
                                {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label for="">Department</label>
                        <select name="department" id="" class="form-control department-select2">
                            @if(request()->department)
                            <option value="{{ request()->department }}" selected>
                                {{ \App\Models\Department::find(request()->department)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label for="">Designation</label>
                        <select name="designation" id="" class="form-control designation-select2">
                            @if(request()->designation)
                            <option value="{{ request()->designation }}" selected>
                                {{ \App\Models\Designation::find(request()->designation)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="mb-2 mt-2">
                    <label for="">Select Employee:</label>
                    <select name="employee" id="" class="form-control employee-select2" onchange="loadBalance(this)"
                        required>
                        @if(request()->employee)
                        <option value="{{ request()->employee }}" selected>
                            {{ \App\Models\User::find(request()->employee)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                @elseif(\App\Models\ApprovalProcess::canApproveEmployees('leave', user()))
                <div class="mb-2 mt-2">
                    <label for="">Select Employee:</label>
                    <select name="employee" id="" class="form-control init-select2" onchange="loadBalance(this)"
                        required>
                        <option value="">Select an employee</option>
                        <option value="{{ user()->id }}" @if(request()->employee == user()->id) selected @endif>{{
                            user()->name }}</option>
                        @foreach (\App\Models\ApprovalProcess::canApproveEmployees('leave', user())['employees'] as  $employee)
                        <option value="{{ $employee->id }}" @if(request()->employee == $employee->id) selected @endif
                            >
                            {{ $employee->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @else
                <select name="employee" id="" class="form-control d-none" required>
                    <option value="{{ user()->id }}" selected>
                        {{ user()->name }}
                    </option>
                </select>
                @endif

                <div class="mb-2 mt-2 balance">
                    <b>Leave Balance:</b>
                    <span>
                        <i>Select an employee to load</i>
                    </span>
                </div>
                <script>
                    function loadBalance(sel)
                        {
                            const emp = sel.value;
                            let html = $('.balance span')
                            html.html("Loading...")
                            $.get("{{ route('leaves.balance.employee') }}", {
                                employee: emp, type: $('[name=leave_type]').val()
                            })
                            .done(function(data) {
                                html.html(`
                                Balance: ${data.used}/${data.limit}
                                `)
                            })
                        }
                </script>
                <div class="mb-2">
                    <label for="">Leave Type:</label>
                    @if(role_can('leave'))
                    <select name="leave_type" id="" class="form-control select" required
                        onchange="loadBalance($('[name=employee]')[0])">
                        <option value="">Select a leave type</option>
                        @foreach (\App\Models\LeaveType::orderBy('name')->get() as $leaveType)
                        <option value="{{ $leaveType->id }}" @if(old('leave_type')==$leaveType->id) selected @endif
                            >
                            {{ $leaveType->name }}
                        </option>
                        @endforeach
                    </select>
                    @else
                    <select name="leave_type" id="" class="form-control select" required
                        onchange="loadBalance($('[name=employee]')[0])">
                        <option value="">Select a leave type</option>
                        @foreach (\App\Models\LeaveType::where('is_for_all_user', 1)->orderBy('name')->get() as  $leaveType)
                        <option value="{{ $leaveType->id }}" @if(old('leave_type')==$leaveType->id) selected @endif
                            >
                            {{ $leaveType->name }}
                        </option>
                        @endforeach
                    </select>
                    @endif
                    @if(role_can('leave_type'))
                    <span>
                        <small>
                            <a href="{{ route('leaves.types') }}" class="">Go here</a> to create a new Leave Type
                        </small>
                    </span>
                    @endif
                </div>
                <div class="mb-2">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" data-target="#daily-leave" type="button"
                                role="tab" aria-controls="daily-leave" aria-selected="true" onclick="
                                        $('#daily-leave [name=start_date]').attr('required', true);
                                        $('#daily-leave [name=end_date]').attr('required', true);
                                        $('#hourly-leave [name=date]').attr('required', false);
                                        $('#hourly-leave [name=start_time]').attr('required', false);
                                        $('#hourly-leave [name=end_time]').attr('required', false);
                                    ">
                                Daily Leave
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" data-target="#hourly-leave" type="button" role="tab"
                                aria-controls="hourly-leave" aria-selected="false" onclick="
                                        $('#daily-leave [name=start_date]').attr('required', false);
                                        $('#daily-leave [name=end_date]').attr('required', false);
                                        $('#hourly-leave [name=date]').attr('required', true);
                                        $('#hourly-leave [name=start_time]').attr('required', true);
                                        $('#hourly-leave [name=end_time]').attr('required', true);
                                    ">
                                Hourly Leave
                            </a>
                        </li>
                    </ul>
                    <div class="bg-white p-3">
                        <div class="">
                            <div class="tab-content pt-0">
                                <div class="tab-pane active" id="daily-leave" role="tabpanel"
                                    aria-labelledby="daily-leave-tab">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="">Start Date:</label>
                                            <input type="text" name="start_date" class="form-control datetimepicker"
                                                required value="{{ old('start_date') }}"
                                                onchange="_calculateDuration()">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="">End Date:</label>
                                            <input type="text" name="end_date" class="form-control datetimepicker"
                                                value="{{ old('end_date') }}" onchange="_calculateDuration()">
                                            <span id="leave_duration"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="hourly-leave" role="tabpanel"
                                    aria-labelledby="hourly-leave-tab">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="">Date:</label>
                                            <input type="text" name="date" class="form-control datetimepicker">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="">Start Time:</label>
                                            <input type="time" name="start_time" class="form-control timepicker">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="">End Time:</label>
                                            <input type="time" name="end_time" class="form-control timepicker">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-2">
                    <label for="">Reason:</label>
                    <textarea name="title" class="form-control" rows="3" required>{{ old('title') }}</textarea>
                </div>
                @if(config('services.leave_has_attachment'))
                <div class="mb-2">
                    <div class="text-right">
                        <a href="javascript:void(0)" class="btn btn-info btn-sm"
                            onclick="$('.attachment').toggleClass('d-none');$('.attachment label')[0].click();this.remove()">
                            <i class="fa fa-upload"></i>
                            Add Attachment
                        </a>
                    </div>
                    <div class="attachment d-none">
                        <label for="attachment">Attachment:</label>
                        <input type="file" name="attachment" id="attachment"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.zip,.rar" class="form-control">
                    </div>
                </div>
                @endif
                @if(config('services.leave_has_station_leave'))
                <div class="mb-2 container">
                    <input type="checkbox" name="is_station_leave" id="is_station_leave" value="1"
                        class="form-check-input" style="scale: 1.5;" onchange="enableStationLeave(this.checked)">
                    <label for="is_station_leave" class="ml-2">Is Station Leave?</label>
                </div>
                {{-- 'station',
                'station_from_date',
                'station_to_date',
                'station_present_address',
                'address_during_out_of_station',
                'station_contact_number',
                'station_alternative_number',
                'reason_for_station_leave',
                'station_remarks_by_employee' --}}
                <div class="station_leave_details d-none">
                    <div class="mb-2">
                        <label for="">Station Name:</label>
                        <input type="text" name="station" class="form-control" value="{{ old('station') }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label for="">From Date:</label>
                            <input type="text" name="station_from_date" class="form-control datetimepicker"
                                value="{{ old('station_from_date') }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="">To Date:</label>
                            <input type="text" name="station_to_date" class="form-control datetimepicker"
                                value="{{ old('station_to_date') }}">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="">Present Address:</label>
                        <input type="text" name="station_present_address" class="form-control"
                            value="{{ old('station_present_address') }}">
                    </div>
                    <div class="mb-2">
                        <label for="">Address During Out of Station:</label>
                        <input type="text" name="address_during_out_of_station" class="form-control"
                            value="{{ old('address_during_out_of_station') }}">
                    </div>
                    <div class="mb-2">
                        <label for="">Contact Number:</label>
                        <input type="text" name="station_contact_number" class="form-control"
                            value="{{ old('station_contact_number') }}">
                    </div>
                    <div class="mb-2">
                        <label for="">Alternative Number:</label>
                        <input type="text" name="station_alternative_number" class="form-control"
                            value="{{ old('station_alternative_number') }}">
                    </div>
                    <div class="mb-2">
                        <label for="">Reason for Station Leave:</label>
                        <textarea name="reason_for_station_leave" class="form-control"
                            rows="3">{{ old('reason_for_station_leave') }}</textarea>
                    </div>
                    <div class="mb-2">
                        <label for="">Remarks by Employee:</label>
                        <textarea name="station_remarks_by_employee" class="form-control"
                            rows="3">{{ old('station_remarks_by_employee') }}</textarea>
                    </div>
                </div>
                @endif
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
@endsection
@section('script')
<script>
    let enableStationLeave = (checked) => {
            if(checked) {
                $('.station_leave_details').removeClass('d-none');
                $('.station_leave_details input, .station_leave_details textarea').attr('required', true);
                $('.station_leave_details [name=station_remarks_by_employee]').attr('required', false);
            } else {
                $('.station_leave_details').addClass('d-none');
                $('.station_leave_details input, .station_leave_details textarea').attr('required', false);
                $('.station_leave_details input, .station_leave_details textarea').val('');
            }
        }
        let _calculateDuration = () => {
            let start_date = $('.datetimepicker[name="start_date"]').val();
            let end_date = $('.datetimepicker[name="end_date"]').val();
            let emp = $('[name="employee"]').val();
            if(!start_date || !end_date || !emp) return
            let l = document.getElementById('leave_duration')
            l.innerText = 'Calculating...';
            axios.post('{{ route("leaves.get_duration") }}', {
                start: start_date,
                end: end_date,
                user_id: emp
            })
            .then(response => {
                l.innerText = response.data.total + ' ' + (response.data.total > 1 ? 'days' : 'day');
            })
            .catch(error => {
                l.innerText = 'Error';
            })
        }

</script>
@endSection
