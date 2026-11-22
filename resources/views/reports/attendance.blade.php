@extends('layouts.master')
@section('title', $missed_checkout ? "Missed Check-Out Report" : "Attendance Report")
@section('content')
{{-- message --}}
{!! Toastr::message() !!}
<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">{{ $missed_checkout ? "Missed Check-Out Report" : "Attendance Report" }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">{{ $missed_checkout ? "Missed Check-Out Report" : "Attendance
                            Report" }} </li>
                    </ul>
                </div>
                @if(role_can('superadmin') && !$missed_checkout)
                <div class="col-auto">
                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#regenerateModal"
                        style="margin-top: 10px; float: right;">
                        <i class="fa fa-refresh"></i>
                        Regenerate
                    </button>
                </div>
                @endif
            </div>
        </div>
        <!-- /Page Header -->
        <!-- Search Filter -->
        <form method="get" @if(request()->editable)
            action="{{ route('attendance.editall') }}"
            @else
            @if($missed_checkout)
            action="{{ route('reports.missed-attendance') }}"
            @else
            action="{{ route('reports.attendance') }}"
            @endif
            @endif
            class="row filter-row">
            @if(request()->editable)
            <input type="hidden" name="editable" value="1">
            @endif
            @if(
                role_can('reports')
                || user()->admin_of_super_departments
                || role_can('reports.self.super_department')
            )
            @if(
                !role_can('reports')
                && !user()->admin_of_super_departments
                && role_can('reports.self.super_department')
            )
            <div class="mb-2 col-sm-6 col-md-3">
                <label for="">Super Department</label>
                <input type="text" value="{{ user()->department?->super_department?->name }}" class="form-control"
                    readonly>
                <input type="hidden" name="super_department" value="{{ user()->department?->super_department_id }}"
                    class="form-control">
                <script>
                    window.selected_super_department = '{{ user()->department?->super_department_id }}';
                </script>
            </div>
            @else
            <div class="mb-2 col-sm-6 col-md-3">
                <label for="">Super Department</label>
                <select name="super_department" id="" class="form-control super-department-select2">
                    @if(request()->super_department)
                    <option value="{{ request()->super_department }}" selected>
                        {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                    </option>
                    @endif
                </select>
            </div>
            @endif
            <div class="mb-2 col-sm-6 col-md-3">
                <label for="">Department</label>
                <select name="department" id="" class="form-control department-select2">
                    @if(request()->department)
                    <option value="{{ request()->department }}" selected>
                        {{ \App\Models\Department::find(request()->department)?->name }}
                    </option>
                    @endif
                </select>
            </div>
            <div class="mb-2 col-sm-6 col-md-3">
                <label for="">Designation</label>
                <select name="designation" id="" class="form-control designation-select2">
                    @if(request()->designation)
                    <option value="{{ request()->designation }}" selected>
                        {{ \App\Models\Designation::find(request()->designation)?->name }}
                    </option>
                    @endif
                </select>
            </div>
            <div class="mb-2 col-sm-6 col-md-3">
                <label for="">Employee</label>
                <select name="employee" id="" class="form-control employee-select2">
                    @if(request()->employee)
                    <option value="{{ request()->employee }}" selected>
                        {{ \App\Models\User::find(request()->employee)?->name }}
                    </option>
                    @endif
                </select>
            </div>
            @elseif(user()->managed_employees->count() > 0)
            <div class="mb-2 col-12">
                <label for="">Employee</label>
                <select name="employee" id="" class="form-control init-select2">
                    <option value="{{ user()->id }}">{{ user()->name }}</option>
                    @foreach(user()->managed_employees as $employee)
                    <option value="{{ $employee->id }}" @if(request()->employee == $employee->id) selected @endif>{{
                        $employee->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="mb-2 col-sm-6 col-md-3">
                <div class="form-group ">
                    <label class="focus-label">From</label>
                    <div class="cal-icon">
                        <input class="form-control datetimepicker" type="text" value="{{ $from->format('d-m-Y') }}"
                            name="start_date">
                    </div>
                </div>
            </div>
            <div class="mb-2 col-sm-6 col-md-3">
                <div class="form-group">
                    <label class="focus-label">To</label>
                    <div class="cal-icon">
                        <input class="form-control datetimepicker" type="text" value="{{ $to->format('d-m-Y') }}"
                            name="end_date">
                    </div>
                </div>
            </div>
            @if(config('app.customer') === 'vector')
            <div class="mb-2 col-sm-6 col-md-3">
                <div class="form-group">
                    <label class="focus-label">
                        Time Start and End
                    </label>
                    <div class="d-flex w-100">
                        <div class="">
                            <input type="time" class="form-control" name="from_time" value="{{ request()->from_time }}">
                        </div>
                        <div class="">
                            <input type="time" class="form-control" name="to_time" value="{{ request()->to_time }}">
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div class="mb-2 col-sm-6 col-md-3">
                <div class="form-group">
                    <label class="focus-label">
                        Duty Type
                    </label>
                    <div class="">
                        <select name="duty_type" class="form-control init-select2" id="">
                            <option value="">All</option>
                            @foreach ([
                            'Inside of Office', 'Outside of Office',
                            'Work from Home', 'Outside Duty Punch',
                            ] as $item)
                            <option value="{{ $item }}" @if (request()->duty_type === $item)
                                selected
                                @endif
                                >{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <button style="margin-top: 25px;" type="submit" class="btn btn-success"> Search </button>
            </div>
        </form>

        <div class="row">
            <div class="col-md-12">
                @include('layouts.exports')
                @if(request()->editable && (role_can('reports') || role_can('reports.self.super_department')))
                <form action="" class="update-shifts d-flex" id="update-shifts">
                    @csrf
                    <input type="hidden" name="attendance_ids" value="">
                    <select name="shift_id" class="form-control" style="width: 200px" required>
                        <option value="">Select Shift</option>
                        @foreach ($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endforeach
                    </select>
                    {{-- <div> --}}
                        <button type="button" onclick="updateShifts(this)" class="btn btn-sm btn-primary float-start">
                            <i class="fa fa-check"></i>
                            Update shifts of <span id="count__">0</span> Att.
                        </button>
                        {{--
                    </div> --}}
                </form>
                @endif
                <div class="table-responsive">
                    {{-- @dump($data) --}}
                    @include('reports.attendance-table', [
                        'data' => $data,
                        'missed_checkout' => $missed_checkout,
                    ])
                </div>
                <div class="mt-2">
                    {{-- {{ $data->withQueryString()->links() }} --}}
                </div>
            </div>
        </div>
        <!-- /Content End -->
    </div>
    <!-- /Page Content -->
</div>

@if(!request()->editable)
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('table', {
        pageLength: 50
    });
</script>
@endif
<script>
    function updateShifts(event) {
        let attendance_ids = [];
        let btn = $(event);
        $('input[name="attendance_ids[]"]:checked').each(function() {
            attendance_ids.push($(this).val());
        });
        if (attendance_ids.length == 0) {
            alert('Please select at least one attendance');
            return;
        }
        if ($('#update-shifts select[name="shift_id"]').val() == '') {
            alert('Please select a shift');
            return;
        }
        $('#update-shifts input[name="attendance_ids"]').val(attendance_ids.join(','));
        $('#update-shifts').submit();
    }
</script>
@endsection
