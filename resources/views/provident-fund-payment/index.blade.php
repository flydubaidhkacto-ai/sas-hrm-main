@extends('layouts.master')
@section('title', "Provident Fund Payment History")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Provident Fund Payment History</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">PF Payment History</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Search Filter -->
        <form action="{{ route('pf-payment.index') }}" method="get" class="mb-3">
            <div class="row filter-row">
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
                <div class="mt-2 col-sm-6 col-md-3">
                    <label for="">Date From</label>
                    <input name="date_from" type="date" class="form-control" value="{{ request()->date_from }}">
                </div>
                <div class="mt-2 col-sm-6 col-md-3">
                    <label for="">Date To</label>
                    <input name="date_to" type="date" class="form-control" value="{{ request()->date_to }}">
                </div>
                <div class="col-sm-6 col-md-3 mt-2">
                    <label for="">&nbsp;</label>
                    <button type="submit" class="btn btn-success btn-block"> Search </button>
                </div>
                <div class="col-sm-6 col-md-3 mt-2">
                    <label for="">&nbsp;</label>
                    <a href="{{ route('pf-payment.index') }}" class="btn btn-secondary btn-block"> Reset </a>
                </div>
            </div>
        </form>
        <!-- Search Filter -->

        {{-- message --}}
        {!! Toastr::message() !!}

        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped custom-table data-table">
                        <thead>
                            <tr>
                                <th>S.L</th>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Paid Amount</th>
                                <th>Total Balance</th>
                                <th>Remaining Balance</th>
                                <th>Note</th>
                                <th>Processed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pf_payments as $index => $payment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $payment->paid_at->format('d M, Y') }}</td>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="{{ route('employees.active.show', $payment->user->id) }}">
                                                {{ $payment->user->name }}
                                            </a>
                                        </h2>
                                        <small class="text-muted">ID: {{ $payment->user->employee_id }}</small>
                                    </td>
                                    <td>
                                        {{ $payment->user->department?->super_department?->name }}
                                        @if($payment->user->department?->name)
                                            <br><small class="text-muted">{{ $payment->user->department?->name }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $payment->user->designation?->name }}</td>
                                    <td>
                                        <span class="text-success font-weight-bold">
                                            {{ number_format($payment->paid_amount, 2) }} Tk
                                        </span>
                                    </td>
                                    <td>{{ number_format($payment->total_balance, 2) }} Tk</td>
                                    <td>{{ number_format($payment->remaining_balance, 2) }} Tk</td>
                                    <td>
                                        @if($payment->note)
                                            <span class="text-muted">{{ Str::limit($payment->note, 50) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $payment->paidBy->name }}
                                        <br><small class="text-muted">{{ $payment->paid_at->format('H:i') }}</small>
                                    </td>
                                </tr>
                            @empty

                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->
@endsection

@section('script')
{{-- datatable --}}
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        let table = new DataTable('.data-table', {
            pageLength: 50,
            order: [[1, 'desc']] // Sort by date column descending
        });
    });
</script>
@endsection
