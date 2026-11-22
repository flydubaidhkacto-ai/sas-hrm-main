@extends('layouts.master')
@section('title', "Salary Cards")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Salary Cards
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">
                            Salary Cards
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        <div class="mt-2">
            <form method="get" action="{{ route('payroll-cards.index') }}" class="row filter-row mb-4">
                @if(
                    role_can('payroll_card')
                    || role_can('payroll_card.show')
                    || role_can('payroll_card.self.show.super_department')
                    || role_can('payroll_card.self.show')
                    || user()->admin_of_super_departments
                )
                @if(
                    !role_can('payroll_card')
                    && !role_can('payroll_card.show')
                    && !user()->admin_of_super_departments
                    && role_can('payroll_card.self.show.super_department')
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
                                G. Salary
                            </th>
                            <th>
                                Net Salary
                            </th>
                            <th>
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                        <tr>
                            <td>
                                {{ $loop->iteration }}
                            </td>
                            <td>
                                {{ $employee->id }}
                            </td>
                            <td>
                                {{ $employee->employee_id }}
                            </td>
                            <td>
                                {{ $employee->name }}
                            </td>
                            <td>
                                {{ $employee->department?->super_department?->name }}:
                                {{ $employee->department?->name }}
                            </td>
                            <td>
                                {{ $employee->designation?->name }}
                            </td>
                            <td>
                                @if ($employee->payroll_card)
                                {{ $employee->payroll_card->gross_salary }} Tk
                                @else
                                --
                                @endif
                            </td>
                            <td>
                                @if ($employee->payroll_card)
                                {{ $employee->payroll_card->net_salary }} Tk
                                @else
                                --
                                @endif
                            </td>
                            <td>
                                @if ($employee->payroll_card)
                                <a href="{{ route('payroll-cards.show', $employee->payroll_card->id) }}"
                                    class="btn btn-primary btn-sm">
                                    Open
                                </a>
                                @else
                                    @if(
                                        role_can('payroll_card')
                                        || role_can('payroll_card.create')
                                        || role_can('payroll_card.create.self.super_department')
                                        || user()->admin_of_super_departments
                                    )
                                    @php
                                    $can = true;
                                    if (
                                        !role_can('payroll_card')
                                        && !role_can('payroll_card.create')
                                        && !user()->admin_of_super_departments
                                        && role_can('payroll_card.create.self.super_department')
                                    )
                                    {
                                        $can = $employee->department?->super_department_id == user()->department?->super_department_id;
                                    }
                                    @endphp
                                        @if($can)
                                        <a href="{{ route('payroll-cards.create', $employee) }}" class="btn btn-info btn-sm">
                                            Create
                                        </a>
                                        @endif
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                {{ $employees->withQueryString()->links() }}
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
