@extends('layouts.master')
@section('title', "Fines")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Fines
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Fines</li>
                    </ul>
                </div>
                @if(role_can('fines') || user()->admin_of_super_departments)
                <div class="col-auto float-right ml-auto">
                    <a href="{{ route('fines.create') }}" class="btn add-btn">
                        <i class="fa fa-plus"></i>
                        New Fine
                    </a>
                </div>
                @endif
            </div>
        </div>

        @include('layouts.alerts')
        <div class="mt-2">
            <form method="get" action="{{ route('fines.index') }}" class="row filter-row mb-4">
                @if(
                    role_can('fines') || user()->admin_of_super_departments
                )
                <div class="mb-2 col-sm-6 col-md-3">
                    <label for="">Super Department</label>
                    <select name="super_department" id="" class="form-control super-department-select2">
                        @if (request()->super_department)
                        <option value="{{ request()->super_department }}" selected>
                            {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
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
                @elseif(\App\Models\ApprovalProcess::canApproveEmployees('fine', user()))
                <div class="mb-2 mt-2 col-12">
                    <label for="">Select Employee:</label>
                    <select name="employee" id="" class="form-control init-select2">
                        <option value="">Select an employee</option>
                        <option value="{{ user()->id }}" @if(request()->employee == user()->id) selected @endif>{{
                            user()->name }}</option>
                        @foreach (\App\Models\ApprovalProcess::canApproveEmployees('fine', user())['employees'] as $employee)
                        <option value="{{ $employee->id }}" @if(request()->employee == $employee->id) selected @endif
                            >
                            {{ $employee->name }}
                        </option>
                        @endforeach
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
                                value="{{ $from?->format('d-m-Y') }}" name="from">
                        </div>
                        <label class="focus-label">Applied From</label>
                    </div>
                </div>
                <div class="mb-2 col-sm-6 col-md-3">
                    <div class="form-group form-focus">
                        <div class="cal-icon">
                            <input class="form-control floating datetimepicker" type="text"
                                value="{{ $to?->format('d-m-Y') }}" name="to">
                        </div>
                        <label class="focus-label">Applied To</label>
                    </div>
                </div>
                <div class="mb-2 col-sm-6 col-md-3">
                    <button type="submit" class="btn btn-success btn-block"> Search </button>
                </div>
            </form>
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <div class="mt-2">
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
                <div class="table-responsive">
                    @include('fines.all_table', [
                    'data' => $data,
                    'for_export' => false
                    ])
                </div>
            </div>
        </div>
    </div>
</div>


@foreach ($data as $item)
<div class="modal fade" id="detailsModal{{$item->id}}" tabindex="-1" role="dialog"
    aria-labelledby="detailsModal{{$item->id}}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModal{{$item->id}}">Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @include('fines.view', [
                'fine' => $item,
                ])
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- data table cdn --}}
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('table', {});
</script>
@endsection
