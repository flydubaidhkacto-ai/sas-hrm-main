@extends('layouts.master')
@section('title', 'Emp. Daily expenses: New Entry')
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
                        Emp. Daily Expenses: New Entry
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('emp-expenses.index') }}">Emp. Daily Expenses</a>
                        </li>
                        <li class="breadcrumb-item active">
                            Add New
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="">
            <form class="" action="{{ route('emp-expenses.store') }}" method="POST">
                @csrf
                <div class="row">
                    @if (role_can('emp-expenses') || user()->admin_of_super_departments)
                    <!-- First Row: Super Department, Department, Designation -->
                    <div class="mt-2 col-sm-6 col-md-4">
                        <label for="">Super Department</label>
                        <select name="super_department" class="form-control super-department-select2">
                            @if (request()->super_department)
                            <option value="{{ request()->super_department }}" selected>
                                {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-4">
                        <label for="">Department</label>
                        <select name="department" class="form-control department-select2">
                            @if (request()->department)
                            <option value="{{ request()->department }}" selected>
                                {{ \App\Models\Department::find(request()->department)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-4">
                        <label for="">Designation</label>
                        <select name="designation" class="form-control designation-select2">
                            @if (request()->designation)
                            <option value="{{ request()->designation }}" selected>
                                {{ \App\Models\Designation::find(request()->designation)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>

                    <!-- Second Row: Employee (Full Width) -->
                    <div class="mt-2 col-12">
                        <label for="">Employee</label>
                        <select name="employee" class="form-control employee-select2" required>
                            @if (request()->employee)
                            <option value="{{ request()->employee }}" selected>
                                {{ \App\Models\User::find(request()->employee)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    @else
                    <input type="hidden" name="employee" value="{{ user()->id }}">
                    @endif

                    <!-- Third Row: Expense Category, Name, Amount -->
                    <div class="mt-2 col-sm-6 col-md-4">
                        <label for="expensed_category_id">Expense Category</label>
                        <select name="expensed_category_id" id="expensed_category_id" class="form-control init-select2">
                            <option value="" selected disabled>Select Expense Category</option>
                            @foreach ($expenseCategories as $expenseCategory)
                            <option value="{{ $expenseCategory->id }}">
                                {{ $expenseCategory->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-4">
                        <label for="">Name</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                    </div>
                    <div class="mt-2 col-sm-6 col-md-4">
                        <label for="">Amount</label>
                        <input type="number" step="any" name="amount" class="form-control" required min="1"
                            value="{{ old('amount') }}">
                    </div>

                    <!-- Fourth Row: Date -->
                    <div class="mt-2 col-sm-6 col-md-4">
                        <label for="">Date</label>
                        <input type="date" name="date" class="form-control" required
                            value="{{ old('date') ?: now()->format('Y-m-d') }}"
                            min="{{ now()->subMonth()->format('Y-m-d') }}">
                    </div>

                    <!-- Fifth Row: Description -->
                    <div class="mt-2 col-12">
                        <label for="">Description</label>
                        <textarea name="description" class="form-control" cols="30"
                            rows="4">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary" type="submit">
                        Create
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
<!-- /Page Content -->
</div>

<!-- /Page Wrapper -->
@section('script')

@endsection
@endsection
