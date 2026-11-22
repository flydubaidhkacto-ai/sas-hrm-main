@extends('layouts.master')
@section('title', "Probation Employee list")
@section('content')
@include('employees.terminate-modal')
@include('employees.activate-modal')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">

        {{-- <div class="mb-3">
            @include('tabs.employees')
        </div> --}}

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Probation Employee</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Probation Employee</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Search Filter -->
        <form action="{{ route('employees.probation.list') }}" method="get" class="mb-3">
            <div class="row filter-row">
                <div class="col-sm-6 col-md-2">
                    <label for="">Super Department</label>
                    @if(role_can('user') || is_admin_of_super_department())
                    <select name="super_department" id="" class="form-control super-department-select2">
                        @if(request()->super_department)
                        <option value="{{ request()->super_department }}" selected>
                            {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                        </option>
                        @endif
                    </select>
                    @else
                    <input type="text" readonly class="form-control"
                        value="{{ user()->department?->super_department?->name }}">
                    <input type="hidden" name="super_department"
                        value="{{ user()->department?->super_department?->id }}">
                    <script>
                        window.selected_super_department = {{ user()->department?->super_department?->id }};
                    </script>
                    @endif
                </div>
                <div class="col-sm-6 col-md-2">
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
                <div class="col-sm-6 col-md-3">
                    <label for="">Employee</label>
                    <select name="employee" id="" class="form-control employee-select2">
                        @if(request()->employee)
                        <option value="{{ request()->employee }}" selected>
                            {{ \App\Models\User::find(request()->employee)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="col-sm-6 col-md-2">
                    <button style="margin-top: 25px;" type="submit" class="btn btn-success btn-block"> Search </button>
                </div>
            </div>
        </form>
        <!-- Search Filter -->
        {{-- message --}}
        {!! Toastr::message() !!}
        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
            {{ $error }}
            @endforeach
        </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="exports">
                    @include('layouts.exports')
                </div>
                <div class="table-responsive">
                    @include('employees.list-table', [
                    'employees' => $employees
                    ])
                    {{-- <div class="mt-2">
                        {{ $employees->withQueryString()->links() }}
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->

</div>
<!-- /Page Wrapper -->
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('table', {
            pageLength: 50
        });;
</script>
@endsection
@section('script')
<script>
    $('.init-select2-shift').select2({}).on('change', function () {
            $(this).next().next().next().css('display', 'block')
        })
</script>
@endsection
