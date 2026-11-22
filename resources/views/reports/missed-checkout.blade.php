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
        <!-- /Search Filter -->
        <div class="row">
            <div class="col-md-12">
                <div style="text-align: end" class="mb-2">
                    <a href="{{ add_query_to_current_route(['export' => 'xlsx']) }}" class="btn btn-sm btn-info"
                        target="_blank">
                        <i class="fa fa-file-excel-o"></i>
                        Excel
                    </a>
                </div>
                <div class="table-responsive">
                    <table @if($for_export ?? false) style="width: 100%" @else
                        class="table table-striped custom-table attendance-table" @endif>
                        <thead>
                            <tr>
                                <th>
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
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                            <tr>
                                <td>
                                    {{ $loop->iteration }}
                                </td>
                                <td>
                                    {{ $item->employee?->id }}
                                </td>
                                <td>
                                    {{ $item->employee?->employee_id }}2
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
                                <td>{{ $item->employee?->designation?->name }}</td>
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
                                <td>
                                    {{carbon($item->date)->format('d F')}}

                                    @if($item->added_manually_by)
                                    @php
                                    $user = \App\Models\User::find($item->added_manually_by);
                                    @endphp
                                    @if($user)
                                    <br>
                                    <i class="small">Added manually By: </i>
                                    <br> <i>{{ $user->name }}</i>
                                    @endif
                                    @endif
                                </td>

                                @php
                                $used = \App\Actions\EmployeePunched\CalculateWorkingTimeOfAttendance::execute(
                                $item, true, null
                                );
                                @endphp
                                <td>
                                    <div style="width: 80px;">
                                        @if($used['checkedIn'])
                                        {{ carbon($used['checkedIn'])->format('h:i A') }}
                                        @else
                                        --:--
                                        @endif
                                    </div>
                                </td>
                                <td class="out">
                                    @if($used['checkedOut'])
                                    <div style="width: 170px;">
                                        {{ carbon($used['checkedOut'])->format('d M, Y') }}
                                        {{ carbon($used['checkedOut'])->format('h:i A') }}
                                    </div>
                                    @elseif(role_can('attendance.update.out-time'))
                                    <div style="width: 265px;">
                                        <form class="d-flex" onsubmit="saveOutTime(this);return false;"
                                            emp_id="{{ $item->employee_id }}">
                                            <input type="datetime-local" class="form-control" style="width: 215px;"
                                                required @if($used['checkedIn'])
                                                min="{{ carbon($used['checkedIn'])->format('Y-m-d H:i') }}"
                                                max="{{ carbon($used['checkedIn'])->copy()->addHours(24)->format('Y-m-d H:i') }}"
                                                @endif name="out_time">
                                            <button class="btn btn-primary btn-sm ml-2" style="width: 50px;"
                                                type="submit">
                                                Save
                                            </button>
                                        </form>
                                    </div>
                                    @else
                                    --:--
                                    @endif
                                </td>
                                <td class="working">
                                    {{ carbon($item->working_time ?? '00:00')->format('H:i') }}
                                </td>
                                <td class="overtime">
                                    {{ carbon($item->overtime_time ?? '00:00')->format('H:i') }}
                                </td>
                                <td>
                                    {{ $item->notes ? str_replace("\n", '<br>', $item->notes) : '--' }}
                                    @if($item->notes_by)
                                    @php
                                    $user = \App\Models\User::find($item->notes_by);
                                    @endphp
                                    @if($user)
                                    <br><i class="small">By: <br> {{ $user->name }}</i>
                                    @endif
                                    @endif
                                </td>
                                <td>
                                    {{ $item->duty_type ?? '--' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
        <!-- /Content End -->
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->

<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('.table', {
        pageLength: 50
    });;
    async function saveOutTime(form)
    {
        let data = {
            emp_id: form.getAttribute('emp_id'),
            out_time: form.querySelector('input[name="out_time"]').value
        };
        console.log(data);

        form.querySelector('button[type="submit"]').disabled = true;
        let url = "{{ route('attendance.update.out-time') }}";
        let response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        let result = await response.json();
        if(result.success)
        {
            toastr.success(result.message);
            let pr = $(form).parent().parent().parent();
            pr.find('.out').html(moment(result.data.check_out).format('DD MMM, YYYY hh:mm A'));
            pr.find('.working').html(result.data.working_time ? result.data.working_time.split(':').slice(0, 2).join(':') : '--');
            pr.find('.overtime').html(result.data.overtime_time ? result.data.overtime_time.split(':').slice(0, 2).join(':') : '--');
        }
        else
        {
            toastr.error(result.message);
            form.querySelector('button[type="submit"]').disabled = false;
        }
    }
</script>
@endsection
