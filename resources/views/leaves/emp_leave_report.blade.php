@extends('layouts.master')
@section('title', "Leaves")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Employee Leave Report
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        {{-- <li class="breadcrumb-item"><a href="{{ route('leaves.history') }}">Leaves</a></li> --}}
                        <li class="breadcrumb-item active">Employee Leave Report</li>
                    </ul>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        <div class="mt-2">
            <form method="get" action="{{ route('leaves.emp_leave_report') }}" class="row filter-row mb-4">
                @if(
                role_can('leave')
                || role_can('leave.show')
                || role_can('leave.show.self.super_department')
                )
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
                <div class="mb-2 col-sm-6 col-md-3 mt-4">
                    <div class="form-group form-focus">
                        <div class="cal-icon">
                            <input class="form-control floating datetimepicker" type="text"
                                value="{{ $start_date?->format('d-m-Y') }}" name="start_date">
                        </div>
                        <label class="focus-label">Applied From</label>
                    </div>
                </div>
                <div class="mb-2 col-sm-6 col-md-3 mt-4">
                    <div class="form-group form-focus">
                        <div class="cal-icon">
                            <input class="form-control floating datetimepicker" type="text"
                                value="{{ $end_date?->format('d-m-Y') }}" name="end_date">
                        </div>
                        <label class="focus-label">Applied To</label>
                    </div>
                </div>
                <div class="mb-2 col-sm-6 col-md-3 mt-4">
                    <button type="submit" class="btn btn-success btn-block"> Submit </button>
                </div>
            </form>
            {!! Toastr::message() !!}
            @if($employee)
            @include('layouts.alerts')
            <div class="mt-2">
                <div class="table-responsive">
                    <div class="exports">
                        {{-- @include('layouts.exports_excel') --}}
                        <div style="text-align: end" class="mb-2">
                            {{-- @if($no_docx ?? false)
                            @else
                            <a href="{{ add_query_to_current_route(['export' => 'docx']) }}" class="btn btn-sm btn-info"
                                target="_blank">
                                <i class="fa fa-file-word-o"></i>
                                Docx
                            </a>
                            @endif --}}
                            {{-- <a href="{{ add_query_to_current_route(['export' => 'xlsx']) }}"
                                class="btn btn-sm btn-info" target="_blank">
                                <i class="fa fa-file-excel"></i>
                                Excel
                            </a> --}}
                            <a href="{{ add_query_to_current_route(['export' => 'pdf']) }}" class="btn btn-sm btn-info"
                                target="_blank">
                                <i class="fa fa-file-pdf"></i>
                                PDF
                            </a>
                            <a href="{{ add_query_to_current_route(['export' => 'print']) }}"
                                class="btn btn-sm btn-info" target="_blank">
                                <i class="fa fa-print"></i>
                                Print
                            </a>
                        </div>
                    </div>
                    @include('leaves.emp_leave_report_table', [
                    'data' => $data,
                    'employee' => $employee,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    ])
                </div>
            </div>
            <div class="mt-2">
                {{-- {{ $data->withQueryString()->links() }} --}}
            </div>
            @else
            <div class="alert alert-info">
                No employee selected
            </div>
            @endif
        </div>
    </div>
</div>
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

@endsection

@section('script')
{{-- <script>
    let table = new DataTable('table', {
        pageLength: 50
    });
</script> --}}
@endsection