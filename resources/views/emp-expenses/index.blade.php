@extends('layouts.master')
@section('title', "Emp. Daily expenses")
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
                        Emp. Daily Expenses
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active">
                            Emp. Daily expenses
                        </li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    @if(role_can('emp-expenses') || role_can('emp-expenses.self') || user()->admin_of_super_departments)
                    <a href="{{ route('emp-expenses.create') }}" class="btn add-btn">
                        <i class="fa fa-plus"></i>
                        New Entry
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="row">
            <form class="col-md-12" method="GET">
                <div class="row">
                    @if(
                        role_can('emp-expenses') || user()->admin_of_super_departments
                    )
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Super Department</label>
                        <select name="super_department" id="" class="form-control super-department-select2">
                            @if (request()->super_department)
                            <option value="{{ request()->super_department }}" selected>
                                {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Department</label>
                        <select name="department" id="" class="form-control department-select2">
                            @if (request()->department)
                            <option value="{{ request()->department }}" selected>
                                {{ \App\Models\Department::find(request()->department)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Designation</label>
                        <select name="designation" id="" class="form-control designation-select2">
                            @if (request()->designation)
                            <option value="{{ request()->designation }}" selected>
                                {{ \App\Models\Designation::find(request()->designation)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Employee</label>
                        <select name="employee" id="" class="form-control employee-select2">
                            @if (request()->employee)
                            <option value="{{ request()->employee }}" selected>
                                {{ \App\Models\User::find(request()->employee)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    @elseif(\App\Models\ApprovalProcess::canApproveEmployees('emp_daily_expense', user()))
                    <div class="mb-2 mt-2 col-12">
                        <select name="employee" id="" class="form-control init-select2" onchange="loadBalance(this)">
                            <option value="">Select an employee</option>
                            <option value="{{ user()->id }}" @if(request()->employee == user()->id) selected @endif>{{
                                user()->name }}</option>
                            @foreach (\App\Models\ApprovalProcess::canApproveEmployees('emp_daily_expense',
                            user())['employees'] as $employee)
                            <option value="{{ $employee->id }}" @if(request()->employee == $employee->id) selected
                                @endif
                                >
                                {{ $employee->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <input type="hidden" name="employee" value="{{ user()->id }}">
                    @endif
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">From Date:</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $from->format('Y-m-d') }}">
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">To Date:</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $to->format('Y-m-d') }}">
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <div class="form-group form-focus select-focus">
                            <label class="-label">Status</label>
                            <select class="select" name="type">
                                <option @if($type==="Pending" ) selected @endif>Pending</option>
                                <option @if($type==="Approved" ) selected @endif> Approved</option>
                                <option @if($type==="Rejected" ) selected @endif>Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <button class="btn btn-primary">
                        Search
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-2">
            <div class="table-responsive">
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
                @include('emp-expenses.list_table', [
                'expenses' => $expenses
                ])
            </div>
        </div>
        <div class="mt-2">

        </div>
    </div>
    <!-- /Page Content -->
</div>
@foreach ($expenses as $item)
<!-- Modal -->
<div class="modal fade edit-appl" id="editAppl{{ $item->id }}" tabindex="-1" role="dialog"
    aria-labelledby="editAppl{{ $item->id }}Title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Expense of: {{ $item->employee?->name }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                </form>
                <div>
                    @include('emp-expenses.show', [
                    'expense' => $item
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
<!-- /Page Wrapper -->
@section('script')
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('table', {});
    $(document).ready(function() {
        let url = (new URL(window.location.href)).searchParams.get('id');
        if (url) {
            $('#editAppl' + url).modal('show');
        }
    });
</script>
@endsection
