@extends('layouts.master')
@section('title', 'Mass Assign Shift')
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
                        Assign Shift: {{ $shift->name }}
                        ({{ carbon($shift->start)->format('h:i A') }} - {{ carbon($shift->end)->format('h:i A') }})
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        @if(request()->students)
                        <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
                        @else
                        <li class="breadcrumb-item"><a href="{{ route('employees.active.list') }}">Employees</a></li>
                        @endif
                        <li class="breadcrumb-item"><a href="{{ route('shifts.index') }}">Shifts</a></li>
                        <li class="breadcrumb-item active">Assign Shift</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="row">
            <div class="col-12 mb-3">
                <h4>Select
                    @if(request()->students)
                    Students
                    @else
                    Employees
                    @endif
                </h4>
                <form action="{{ route('shifts.assign', ['shift' => $shift->id]) }}">
                    @csrf
                    <input type="hidden" name="students" value="{{ request()->students }}">
                    {{-- <input type="hidden" name="assign_by_department" value="1"> --}}
                    <div class="row">
                        @if(!request()->students)
                        <div class="mt-2 col-sm-6 col-md-2">
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
                        <div class="mt-2 col-sm-6 col-md-2">
                            <label for="">Department</label>
                            <select name="department" id="" class="form-control department-select2">
                                @if(request()->department)
                                <option value="{{ request()->department }}" selected>
                                    {{ \App\Models\Department::find(request()->department)?->name }}
                                </option>
                                @endif
                            </select>
                        </div>
                        @if(!request()->students)
                        <div class="mt-2 col-sm-6 col-md-2">
                            <label for="">Designation</label>
                            <select name="designation" id="" class="form-control designation-select2">
                                @if(request()->designation)
                                <option value="{{ request()->designation }}" selected>
                                    {{ \App\Models\Designation::find(request()->designation)?->name }}
                                </option>
                                @endif
                            </select>
                        </div>
                        @endif
                        <div class="mt-2 col-sm-4 col-md-2">
                            <label for="">Shift</label>
                            <select name="shift_id" id="" class="form-control shift-select2">
                                <option value="">Select shift</option>
                                @foreach (\App\Models\Shift::get() as $item)
                                <option value="{{ $item->id }}" {{ request()->shift_id == $item->id ? 'selected' : '' }}
                                    >
                                    {{ $item->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @if(!request()->students)
                        <div class="mt-2 col-sm-6 col-md-2">
                            <label for="">Employee</label>
                            <select name="employee" id="" class="form-control employee-select2">
                                @if(request()->employee)
                                <option value="{{ request()->employee }}" selected>
                                    {{ \App\Models\User::find(request()->employee)?->name }}
                                </option>
                                @endif
                            </select>
                        </div>
                        @endif
                        @if(request()->students)
                        <div class="mt-2 col-sm-6 col-md-2">
                            <label for="">Class</label>
                            <select name="class" id="" class="form-control class-select2">
                                @foreach ($classes as $class)
                                <option value="{{ $class }}" {{ request()->class == $class ? 'selected' : '' }}>
                                    {{ $class }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-sm-6 col-md-2 mt-4">
                            <button style="margin-top: 15px;" class="btn btn-primary">
                                @if(request()->students)
                                Get Students
                                @else
                                Get Employees
                                @endif
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <hr>
        @include('layouts.alerts')
        <div class="mt-2">
            <h4>
                List of
                @if(request()->students)
                Students
                @else
                Employees
                @endif
            </h4>
            <div class="mt-2">
                @if (count($employees) == 0)
                <i>
                    Nothing found! The First created Shift will be used for Attendance
                </i>
                @else
                <form action="{{ route('shifts.assign.save', $shift->id) }}" method="post">
                    @csrf
                    <input type="hidden" name="bulk_add" value="1">
                    <button type="submit" class="btn btn-sm btn-info">

                        Assign to selected
                        @if(request()->students)
                        students
                        @else
                        employees
                        @endif
                    </button>
                    <div class="mt-3">
                        <input type="checkbox" name="" id="select_all" onchange="checkAll(this)"
                            style="transform: scale(1.5)">
                        <label for="select_all" class="ml-2">
                            Select All
                        </label>
                    </div>
                    <div class="table-responsive">
                        <table style="background: #fff;" class="table mt-2 table table-striped custom-table">
                            <thead>
                                <tr>
                                    <th style="width: 20px">
                                        #
                                    </th>
                                    <th>
                                        Code
                                    </th>
                                    <th>
                                        @if(request()->students)
                                        St. ID
                                        @else
                                        Emp. ID
                                        @endif
                                    </th>
                                    <th>
                                        @if(request()->students)
                                        Student
                                        @else
                                        Employee
                                        @endif
                                    </th>
                                    <th>
                                        Department
                                    </th>
                                    @if(request()->students)
                                    <th>
                                        Class
                                    </th>
                                    @else
                                    <th>
                                        Sup. Department
                                    </th>
                                    <th>
                                        Designation
                                    </th>
                                    @endif
                                    <th>
                                        Shift
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employees as $employee)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="employees[]" value="{{ $employee->id }}"
                                            style="transform: scale(1.5)">
                                    </td>
                                    <td>
                                        {{ $employee->id }}
                                    </td>
                                    <td>
                                        {{ $employee->employee_id }}
                                    </td>
                                    <td>
                                        {{ $employee->name }}
                                    </td>
                                    <td>
                                        {{ $employee->department?->name }}
                                    </td>
                                    @if(request()->students)
                                    <td>
                                        {{ $employee->class }}
                                    </td>
                                    @else
                                    <td>
                                        {{ $employee->department?->super_department?->name }}
                                    </td>
                                    <td>
                                        {{ $employee->designation?->name }}
                                    </td>
                                    @endif
                                    <td>
                                        {{ $employee->shift?->name }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-sm btn-info">
                        Assign to selected
                        @if(request()->students)
                        students
                        @else
                        employees
                        @endif
                    </button>
                </form>
                <div class="mt-2">
                    {{ $employees->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
{{--
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('table', {
            pageLength: 50
        });
</script> --}}

<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
    let table = new DataTable('table', {
            pageLength: 200,
            lengthMenu: [ [200, 500, 1000], [200, 500, 1000] ]
        });
</script>
@endsection
@section('script')
<script>
    function checkAll(btn)
        {
            if(btn.checked)
            {
                $('input[name="employees[]"]').prop('checked', true)
            }
            else
            {
                $('input[name="employees[]"]').prop('checked', false)
            }
        }
</script>
@endSection