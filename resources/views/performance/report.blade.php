@extends('layouts.master')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-7">
                    <h3 class="page-title">
                        Performance Report
                        @if ($data)
                        : {{$data->emp->name}}
                        @endif
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Performance Report</li>
                    </ul>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        <div class="mt-2">
            @if($data)
            <div>
                <div class="mb-2">
                    <a href="{{ route('performance.report') }}" class="btn btn-sm btn-info">
                        <i class="fa fa-arrow-left"></i> Change employee
                    </a>
                </div>
                @include('performance.table', ['data' => $data, 'editable' => true])
                <div class="mt-4 mb-4">
                    @include('performance.chart', ['data' => $data])
                </div>
            </div>
            @else
            <form action="" method="get">
                <div class="">
                    @if(
                    role_can('loans')
                    || role_can('loans.apply')
                    || role_can('loans.apply.self.super_department')
                    )
                    <div class="row">
                        @if(
                        !role_can('loans')
                        && !role_can('loans.apply')
                        && role_can('loans.apply.self.super_department')
                        )
                        <div class="col-sm-6 col-md-3">
                            <label for="">Super Department</label>
                            <input type="hidden" name="super_department"
                                value="{{ user()->department?->super_department?->id }}">
                            <input type="text" readonly class="form-control"
                                value="{{ user()->department?->super_department?->name }}">
                            <script>
                                window.selected_super_department = '{{ user()->department?->super_department_id }}';
                            </script>
                        </div>
                        @else
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
                        @endif
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
                        <select name="employee" id="" class="form-control employee-select2" required>
                            @if(request()->employee)
                            <option value="{{ request()->employee }}" selected>
                                {{ \App\Models\User::find(request()->employee)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    @else
                    <select name="employee" id="" class="form-control d-none" required>
                        <option value="{{ user()->id }}" selected>
                            {{ user()->name }}
                        </option>
                    </select>
                    @endif
                </div>
                <div class="mt-2">
                    <div style="width: 500px">
                        <label for="">Select Month</label>
                        <input type="text" name="month" class="form-control month-year-picker"
                            value="{{ request()->month }}" required>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Get Employee Performance </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection