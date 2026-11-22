@extends('layouts.master')
@section('title', "Income Tax Report")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">
                        Tax of Payslips
                        @if ($for_one)
                        : {{ $for_one->name }}
                        @endif
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">All</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Search Filter -->
        @if(!$for_one)
        <form action="{{ route('payslip.tax') }}" method="get" class="mb-3">
            <div class="row filter-row">
                <div class="mt-2 col-sm-6 col-md-3">
                    <label for="">Super Department</label>
                    @if(
                    !role_can('payslip.show')
                    && role_can('payslip.self.show.super_department')
                    )
                    <input type="text" value="{{ user()->department?->super_department?->name }}" class="form-control"
                        readonly>
                    <input type="hidden" name="super_department" value="{{ user()->department?->super_department_id }}">
                    <script>
                        window.selected_super_department = '{{ user()->department?->super_department_id }}';
                    </script>
                    @else
                    <select name="super_department" id="" class="form-control super-department-select2">
                        @if(request()->super_department)
                        <option value="{{ request()->super_department }}" selected>
                            {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                        </option>
                        @endif
                    </select>
                    @endif
                </div>
                <div class="mt-2 col-sm-6 col-md-3">
                    <label for="">Department</label>
                    <select name="department" id="" class="form-control department-select2">
                        @if(request()->department)
                        <option value="{{ request()->department }}" selected>
                            {{ \App\Models\Department::find(request()->department)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="mt-2 col-sm-6 col-md-3">
                    <label for="">Designation</label>
                    <select name="designation" id="" class="form-control designation-select2">
                        @if(request()->designation)
                        <option value="{{ request()->designation }}" selected>
                            {{ \App\Models\Designation::find(request()->designation)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="mt-2 col-sm-6 col-md-3">
                    <label for="">Employee</label>
                    <select name="employee" id="" class="form-control employee-select2">
                        @if(request()->employee)
                        <option value="{{ request()->employee }}" selected>
                            {{ \App\Models\User::find(request()->employee)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="mt-2 col-sm-6 col-md-3">
                    <label for="">From</label>
                    <input name="start" type="text" class="form-control month-year-picker"
                        value="{{ request()->start }}" required>
                </div>
                <div class="mt-2 col-sm-6 col-md-3">
                    <label for="">To</label>
                    <input name="end" type="text" class="form-control month-year-picker" value="{{ request()->end }}"
                        required>
                </div>
                <div class=" col-sm-6 col-md-3 mt-2">
                    <label for="">&nbsp;</label>
                    <button type="sumit" class="btn btn-success btn-block"> Search </button>
                </div>
            </div>
        </form>
        @endif
        <!-- Search Filter -->
        {{-- message --}}
        {!! Toastr::message() !!}
        <div class="exports text-right">
            @include('layouts.exports', [
            'no_docx' => true,
            ])
        </div>
        <div>
            <h3>Total Tax: {{ $data['total'] }}</h3>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    @include('payslip.tax_table', [
                    'data' => $data,
                    'for_one' => $for_one
                    ])
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->
@endsection
@section('script')
<script>

</script>
@endsection