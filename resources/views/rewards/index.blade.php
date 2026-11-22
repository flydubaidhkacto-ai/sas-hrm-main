@extends('layouts.master')
@section('title', "Rewards")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-md-8">
                    <h3 class="page-title">
                        Emp. Rewards
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Emp. Rewards</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('rewards.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add
                        Reward</a>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        <div class="mt-2">
            <form method="get" action="{{ route('rewards') }}" class="row filter-row mb-4">
                @if(
                    role_can('rewards')
                    || role_can('rewards.self.super_department')
                    || user()->admin_of_super_departments
                )
                @if(
                    !role_can('rewards')
                    && role_can('rewards.self.super_department')
                )
                <div class="mb-2 col-sm-6 col-md-3">
                    <label for="">Super Department</label>
                    <input type="hidden" name="super_department"
                        value="{{ user()->department?->super_department?->id }}">
                    <input type="text" class="form-control" readonly
                        value="{{ user()->department?->super_department?->name }}">
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

                @else
                <select name="employee" id="" class="form-control d-none">
                    <option value="{{ user()->id }}" selected>
                        {{ user()->name }}
                    </option>
                </select>
                @endif
                <div class="mb-2 col-sm-6 col-md-3">
                    <div class="form-group form-focus">
                        <div class="cal-icon">
                            <input class="form-control floating datetimepicker" type="text"
                                value="{{ ($from ?? null)?->format('d-m-Y') }}" name="start_date">
                        </div>
                        <label class="focus-label">From</label>
                    </div>
                </div>
                <div class="mb-2 col-sm-6 col-md-3">
                    <div class="form-group form-focus">
                        <div class="cal-icon">
                            <input class="form-control floating datetimepicker" type="text"
                                value="{{ ($to ?? null)?->format('d-m-Y') }}" name="end_date">
                        </div>
                        <label class="focus-label">To</label>
                    </div>
                </div>
                <div class="mb-2 col-sm-6 col-md-3">
                    <button type="submit" class="btn btn-success btn-block"> Search </button>
                </div>
            </form>
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <div class="exports">
                <div style="text-align: end" class="mb-2">
                    <a href="{{ add_query_to_current_route(['export' => 'xlsx']) }}" class="btn btn-sm btn-info"
                        target="_blank">
                        <i class="fa fa-file-excel"></i>
                        Excel
                    </a>
                    <a href="{{ add_query_to_current_route(['export' => 'pdf']) }}" class="btn btn-sm btn-info"
                        target="_blank">
                        <i class="fa fa-file-pdf"></i>
                        PDF
                    </a>

                    <a href="{{ add_query_to_current_route(['export' => 'print']) }}" class="btn btn-sm btn-info"
                        target="_blank">
                        <i class="fa fa-print"></i>
                        Print
                    </a>
                </div>
            </div>
            <div class="mt-2">
                <div class="table-responsive">
                    @include('rewards.list_table', [
                    'data' => $data
                    ])
                </div>
            </div>
            <div class="mt-2">
                {{ $data->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
