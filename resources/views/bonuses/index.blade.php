@extends('layouts.master')
@section('title', "Bonuses")
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Bonuses</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Bonuses</li>
                        </ul>
                    </div>
                    @php
                        $can = role_can('bonuses') || role_can('payslip') || user()->admin_of_super_departments;
                    @endphp
                    @if(($create_hidden ?? true) && $can)
                        <div class="col-auto float-right ml-auto">
                            <a href="{{ route('bonuses.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Generate Bonus</a>
                        </div>
                    @endif
                </div>
            </div>
            <form action="{{ route('bonuses.index') }}" method="get" class="mb-3">
                <div class="row filter-row">
                    @if(
                        role_can('bonuses') || user()->admin_of_super_departments || role_can('payslip')
                    )
                        <div class="mt-2 col-sm-6 col-md-3">
                            <label for="">Super Department</label>
                            <select name="super_department" id="" class="form-control super-department-select2">
                                @if(request()->super_department)
                                    <option value="{{ request()->super_department }}" selected>
                                        {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                                    </option>
                                @endif
                            </select>
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
                    @endif
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Month</label>
                        <input name="month" type="text" class="form-control month-year-picker"
                            value="{{ request()->month ?: now()->startOfMonth()->format('m-Y') }}">
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3 mt-2">
                        <label for="">&nbsp;</label>
                        <button type="sumit" class="btn btn-success btn-block"> Search </button>
                    </div>
                </div>
            </form>
            {!! Toastr::message() !!}

            <div class="row">
                <div class="col-md-12">
                    <div class="exports text-right">
                        @include('layouts.exports', [
                            'no_docx' => true,
                        ])
                    </div>
                    @include('bonuses.all_table', [
                        'bonuses' => $bonuses,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        let table = new DataTable('table', {
            pageLength: 50
        });
    </script>
@endsection
