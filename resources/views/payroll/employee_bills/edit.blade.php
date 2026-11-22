@extends('layouts.master')

@section('title', 'Edit Employee Bill')

@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">

        <x-page-header :title="'Edit Employee Bill'" :breadcrumbs="[
                ['name' => 'Employee Bills', 'route' => route('emp-bills.index')],
                ['name' => 'Edit', 'active' => true],
            ]" />

        <form id="emp-bills-form" action="{{ route('emp-bills.update', $empBill->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row filter-row">
                <div class="col-sm-6 col-md-3">
                    <label for="">Super Department</label>
                    <select name="super_department" id="" class="form-control super-department-select2">
                        @if(request()->super_department)
                        <option value="{{ request()->super_department }}" selected>
                            {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label for="">Department</label>
                    <select name="department" id="" class="form-control department-select2">
                        @if(request()->department)
                        <option value="{{ request()->department }}" selected>
                            {{ \App\Models\Department::find(request()->department)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label for="">Designation</label>
                    <select name="designation" id="" class="form-control designation-select2">
                        @if(request()->designation)
                        <option value="{{ request()->designation }}" selected>
                            {{ \App\Models\Designation::find(request()->designation)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label for="">Employee</label>
                    <select name="user_id" id="" class="form-control employee-select2" required>
                        @if($empBill->user)
                        <option value="{{ $empBill->user->id }}" selected>
                            {{ $empBill->user->name }}
                        </option>
                        @endif
                    </select>
                </div>
            </div>
            <div class="row">
                <!-- Name Field -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="col-form-label">Name<sup class="text-danger">*</sup>:</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter Bill Name" required
                            value="{{ old('name', $empBill->name) }}">
                        @if ($errors->has('name'))
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-form-label">Amount<sup class="text-danger">*</sup>:</label>
                        <input type="number" step="any" name="amount" class="form-control" placeholder="Enter Amount"
                            required value="{{ old('amount', $empBill->amount) }}">
                        @if ($errors->has('amount'))
                        <span class="text-danger">{{ $errors->first('amount') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-form-label">Category<sup class="text-danger">*</sup>:</label>
                        <select class="select form-control" name="emp_bills_category_id" required>
                            <option value="">--Select Category--</option>
                            @foreach ($empBillsCategories as $category)
                            <option value="{{ $category->id }}" {{ old('emp_bills_category_id', $empBill->
                                emp_bills_category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('emp_bills_category_id'))
                        <span class="text-danger">{{ $errors->first('emp_bills_category_id') }}</span>
                        @endif
                    </div>
                </div>
                <!-- Date Field -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-form-label">Date<sup class="text-danger">*</sup>:</label>
                        <input type="date" name="date" class="form-control" required
                            value="{{ old('date', $empBill->date) }}">
                        @if ($errors->has('date'))
                        <span class="text-danger">{{ $errors->first('date') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Description Field -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="col-form-label">Description:</label>
                        <textarea name="description" class="form-control" rows="3"
                            placeholder="Enter description">{{ old('description', $empBill->description) }}</textarea>
                        @if ($errors->has('description'))
                        <span class="text-danger">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-center">
                    <button class="btn btn-primary px-5">Update</button>
                </div>
            </div>
        </form>

    </div>
</div>

@endsection
