@extends('layouts.master')
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
                    <h3 class="page-title">Attendance Report</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Attendance Report</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="#" class="btn btn-primary">PDF</a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Content Starts -->
        <div class="row justify-content-center">
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h3><b>{{ \App\Models\User::employees()->count() }}</b></h3>
                        <p>Total Employees</p>
                    </div>
                </div>
            </div>
            @php

            @endphp
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-success">
                            {{-- <b>{{ \App\Models\AttendanceMonthly::where('total_late_check_in',
                                false)->where('month_name', $month)->count() }}</b> --}}
                            <b>0</b>
                        </h3>
                        <p>Present</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-danger"><b>12</b></h3>
                        <p>Absent</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h3><b>5</b></h3>
                        <p>Days Left</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Search Filter -->
        <form method="get" action="{{ route('reports.attendance.monthly') }}" class="row filter-row mb-4">
            <div class="col-sm-6 col-md-3">
                <div class="form-group form-focus">
                    <input class="form-control floating" type="text" name="employee" value="{{ request()->employee }}">
                    <label class="focus-label">Employee</label>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group form-focus select-focus">
                    <select class="select floating" name="department">
                        <option>Select Department</option>
                        @foreach (\App\Models\Department::ordered()->get() as $item)
                        <option @if(request()->department == $item->id) selected @endif
                            value="{{ $item->id }}">{{ $item->department }}</option>
                        @endforeach
                    </select>
                    <label class="focus-label">Department</label>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group form-focus select-focus">
                    <select class="select floating" name="month">
                        <option>Select Month</option>
                        @foreach ($month_list as $key => $item)
                        <option @if($month==$key) selected @endif value="{{ $key }}">{{ $item }}</option>
                        @endforeach
                    </select>
                    <label class="focus-label">Month</label>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <button type="submit" class="btn btn-success btn-block"> Search </button>
            </div>
        </form>
        <!-- /Search Filter -->
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped custom-table mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Working days</th>
                                <th>Absent</th>
                                <th>Late</th>
                                <th>Rate</th>
                                <th>Designation</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                            <tr>
                                <td>
                                    <h2 class="table-avatar">
                                        <a href="profile.html" class="avatar"><img alt=""
                                                src="{{ URL::to('assets/img/profiles/avatar-02.jpg') }}"></a>
                                        <a href="profile.html">
                                            @if ($item->employee)
                                            {{ $item->employee->name }}
                                            @endif
                                            <span>#{{ $item->employee_id }}</span>
                                        </a>
                                    </h2>
                                </td>
                                <td>
                                    {{ $item->working_time }} Hours
                                </td>
                                <td>
                                    0
                                </td>
                                <td>
                                    0
                                </td>
                                <td>
                                    0
                                </td>
                                <td>
                                    @if ($item->employee && $item->employee->designation)
                                    {{ $item->employee->designation?->name }}
                                    @endif
                                    <br>
                                    <i>
                                        <small>
                                            @if ($item->employee && $item->employee->designation &&
                                            $item->employee->designation->department)
                                            {{ $item->employee->designation->department->department }}
                                            @endif
                                        </small>
                                    </i>
                                </td>
                                <td>
                                    @if($item->employee)
                                    <a href="{{ route('reports.attendance.monthly.month', ['month' => $month, 'employee' => $item->employee_id]) }}"
                                        class="btn btn-info">
                                        Details
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    {{ $data->withQueryString()->links() }}
                </div>
            </div>
        </div>
        <!-- /Content End -->
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->
@endsection
