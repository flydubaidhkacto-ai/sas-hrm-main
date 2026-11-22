@extends('layouts.master')
@section('title', "Payroll Payments")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Payroll Payments
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">
                            Payroll Payments
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="mt-2">
            <form method="get" action="{{ route('payslip-payment.index') }}" class="row filter-row mb-4">
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
                    <label for="">Payslip Month</label>
                    <input type="month" name="month" id="" class="form-control" value="{{ request()->month }}">
                </div>
                <div class="mb-2 col-sm-6 col-md-3">
                    <label for="">&nbsp;</label>
                    <button type="submit" class="btn btn-success btn-block"> Search </button>
                </div>
            </form>
            <div class="mt-2 table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>
                                S.L
                            </th>
                            <th>
                                Emp. Code
                            </th>
                            <th>
                                Emp. ID
                            </th>
                            <th>
                                Emp. Name
                            </th>
                            <th>
                                Department
                            </th>
                            <th>
                                Designation
                            </th>
                            <th>
                                Payslip Month
                            </th>
                            <th>
                                Net Salary
                            </th>
                            <th>
                                Paid Amount
                            </th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payslip_payments as $payslip_payment)
                        <tr>
                            <td>
                                {{ $loop->iteration }}
                            </td>
                            <td>
                                {{ $payslip_payment->user->id }}
                            </td>
                            <td>
                                {{ $payslip_payment->user->employee_id }}
                            </td>
                            <td>
                                {{ $payslip_payment->user->name }}
                            </td>
                            <td>
                                {{ $payslip_payment->user->department?->super_department?->name }}:
                                {{ $payslip_payment->user->department?->name }}
                            </td>
                            <td>
                                {{ $payslip_payment->user->designation?->name }}
                            </td>
                            @php
                                $payslip = $payslip_payment->payslip();
                            @endphp
                            <td>
                                @if ($payslip)
                                {{ carbon($payslip->month_date)->format('F, Y') }}
                                @else
                                --
                                @endif
                            </td>
                            <td>
                                @if ($payslip)
                                {{ number_format($payslip->total_salary) }} Tk
                                @else
                                --
                                @endif
                            </td>

                            @php
                                $diff = $payslip->total_salary - $payslip_payment->paid_amount;
                            @endphp
                            <td
                                @if ($diff > 0)
                                    style="color: red;"
                                @elseif($diff == 0)
                                    style="color: green;"
                                @else
                                    style="color: orange;"
                                @endif
                            >
                                <div style="width: 140px" class="">
                                    {{ number_format($payslip_payment->paid_amount) }} Tk
                                    @if ($diff > 0)
                                        <span class="badge badge-warning" title="Company has to pay {{ $diff }} Tk" data-toggle="tooltip">{{ number_format($diff) }} Tk</span>
                                    @elseif($diff == 0)
                                    @else
                                        <span class="badge badge-danger" title="Employee has to pay {{ $diff * -1 }} Tk" data-toggle="tooltip">{{ number_format($diff * -1) }} Tk</span>
                                    @endif
                                </div>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- <div class="mt-2">
                {{ $payslip_payments->withQueryString()->links() }}
            </div> --}}
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
