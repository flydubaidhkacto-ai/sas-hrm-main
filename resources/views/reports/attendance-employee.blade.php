@extends('layouts.master')

@section('title', "Emp Attendance Report")
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
                    <h3 class="page-title">Emp. Attendance Report</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Emp. Attendance Report</li>
                    </ul>
                </div>
                <div class="col-auto">

                </div>
            </div>
        </div>
        <!-- /Page Header -->
        <!-- Search Filter -->
        <form method="get" action="{{ route('reports.attendance.employee') }}" class="row filter-row mb-4">
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
                <select name="employee" id="" class="form-control employee-select2" required>
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
                            name="start_date" required>
                    </div>
                </div>
            </div>
            <div class="mb-2 col-sm-6 col-md-3">
                <div class="form-group ">
                    <label class="focus-label">To</label>
                    <div class="cal-icon">
                        <input class="form-control datetimepicker" type="text" value="{{ $to->format('d-m-Y') }}"
                            name="end_date" required>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-success"> Search </button>
            </div>
        </form>
        <!-- /Search Filter -->
        <div class="row">
            <div class="col-md-12">
                <div style="text-align: end" class="mb-2">
                    <a href="{{ add_query_to_current_route(['export' => 'pdf']) }}" class="btn btn-sm btn-info"
                        target="_blank">
                        <i class="fa fa-file-pdf-o"></i>
                        PDF
                    </a>
                    <a href="{{ add_query_to_current_route(['export' => 'print']) }}" class="btn btn-sm btn-info"
                        target="_blank">
                        <i class="fa fa-print"></i>
                        Print
                    </a>
                </div>
                <div class="table-responsive">
                    @include('reports.attendance-employee-table', ['data' => $data, 'employee' => $_employee])
                </div>
                {{-- <div class="mt-2">
                    {{ $data->withQueryString()->links() }}
                </div> --}}
            </div>
        </div>
        <!-- /Content End -->
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->

@endsection
