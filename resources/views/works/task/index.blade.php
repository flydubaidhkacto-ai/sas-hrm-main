@extends('layouts.master')
@section('title', "Tasks")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Tasks</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Tasks</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto text-right">
                    <a href="{{ route('tasks.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add New</a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        @include('layouts.alerts')
        <div class="mt-2">
            {{-- <form method="get" action="{{ route('clients.index') }}" class="row filter-row mb-4">
                @if(
                role_can('clients')
                || role_can('clients.show')
                || role_can('clients.self.show.super_department')
                || role_can('clients.self.show')
                )
                @if(
                !role_can('clients')
                && !role_can('clients.show')
                && role_can('clients.self.show.super_department')
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
                <div class="mb-2 col-sm-6 col-md-3">
                    <div class="form-group form-focus select-focus">
                        <select class="select floating" name="type">
                            <option @if($type==="created" ) selected @endif>Created</option>
                            <option @if($type==="Not Created" ) selected @endif>Not Created</option>
                            <option @if($type==="All" ) selected @endif>All</option>
                        </select>
                        <label class="focus-label">Status</label>
                    </div>
                </div>
                <div class="mb-2 col-sm-6 col-md-3">
                    <button type="submit" class="btn btn-success btn-block"> Search </button>
                </div>
                @endif
            </form> --}}

            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <div class="mt-2">
                <div class="table-responsive">
                    @include('works.task.list_table', [
                    'tasks' => $tasks
                    ])
                </div>
            </div>
            <div class="mt-2">
                {{-- {{ $tasks->withQueryString()->links() }} --}}
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('table', {
            pageLength: 50
        });
</script>
@endsection