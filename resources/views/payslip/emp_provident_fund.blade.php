
@extends('layouts.master')
@section('title', "Emp. Provident Fund Balance")
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
                        Emp. Provident Fund Balance
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Emp. Provident Fund Balance</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Search Filter -->
        <form action="{{ route('payslip.emp_provident_fund') }}" method="get" class="mb-3">
            <div class="row filter-row">

                @php
                    $isSelfOnlyRole = in_array(auth()->user()->assinged_role?->name, [
                        'Employee',
                        'IT',
                        'Data Entry'
                    ]);
                @endphp

                <div class="mt-2 col-sm-6 col-md-3">
                    <label>Employee</label>

                    <select name="employee"
                            class="form-control employee-select2"
                            {{ $isSelfOnlyRole ? 'disabled' : '' }}>

                        @if($isSelfOnlyRole)
                            <option value="{{ auth()->id() }}" selected>
                                {{ auth()->user()->name }}
                            </option>
                            <input type="hidden" name="employee" value="{{ auth()->id() }}">
                        @else
                            @if(request()->employee)
                                <option value="{{ request()->employee }}" selected>
                                    {{ \App\Models\User::find(request()->employee)?->name }}
                                </option>
                            @endif
                        @endif

                    </select>
                </div>
                <div class=" col-sm-6 col-md-3 mt-2">
                    <label for="">&nbsp;</label>
                    <button type="sumit" class="btn btn-success btn-block"> Search </button>
                </div>
            </div>
        </form>
        <!-- Search Filter -->
        {{-- message --}}
        {!! Toastr::message() !!}
        @if($data ?? null)
            <div>
                <h4>Current Balance:
                    <span class="text-info">
                        {{ $data->first()?->balance_with_interest ?: 0 }} Tk
                    </span>
                </h4>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table
                            class="table table-striped custom-table"
                        >

                        <thead>
                            <tr>
                                <th>
                                    S.L
                                </th>
                                <th>
                                    Code
                                </th>
                                <th>
                                    Emp. ID
                                </th>
                                <th>Name</th>
                                <th>Sup. Department</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Month</th>
                                <th>Fund Balance</th>
                                <th>Interest Rate</th>
                                <th>Fund Interest</th>
                                <th>
                                    Balance with Interest
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                                <tr>
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        {{ $item->user->id }}
                                    </td>
                                    <td>
                                        {{ $item->user->employee_id }}
                                    </td>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="{{ route('employees.active.show', $item->user->id) }}">
                                                {{ $item->user->name }}
                                            </a>
                                        </h2>
                                    </td>
                                    <td>{{ $item->user->department?->super_department?->name }}</td>
                                    <td>{{ $item->user->department?->name }}</td>
                                    <td>{{ $item->user->designation?->name }}</td>
                                    <td>
                                        {{ carbon($item->month_date)->format('M, Y') }}
                                    </td>
                                    <td>
                                        {{ $item->balance }}
                                    </td>
                                    <td>
                                        {{ $item->interest }}
                                    </td>
                                    <td>
                                        {{ $item->interest_amount }}
                                    </td>
                                    <td>
                                        {{ $item->balance_with_interest }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        </table>

                    </div>
                </div>
            </div>
        @endif
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
        let table = new DataTable('table', {
            pageLength: 50
        });
    });
</script>
@endsection
