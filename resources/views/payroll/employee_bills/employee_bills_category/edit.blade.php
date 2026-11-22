@extends('layouts.master')

@section('title', 'Edit Emp Bills Category')

@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">

            <x-page-header :title="'Edit Emp Bills Category'" :breadcrumbs="[
                ['name' => 'Emp Bills Categories', 'route' => route('emp-bill-category.index')],
                ['name' => 'Edit', 'active' => true],
            ]" />

            <form id="emp-bill-category-form" action="{{ route('emp-bill-category.update', $emp_bills_category->id) }}"
                method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Name Field -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Category Name<sup class="text-danger">*</sup>:</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter Category Name"
                                required value="{{ old('name', $emp_bills_category->name) }}">
                            @if ($errors->has('name'))
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Active/Inactive Dropdown -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Status<sup class="text-danger">*</sup>:</label>
                            <select class="select form-control" id="is_active" name="is_active" required>
                                <option value="1"
                                    {{ old('is_active', $emp_bills_category->status) == '1' ? 'selected' : '' }}>
                                    Active</option>
                                <option value="0"
                                    {{ old('is_active', $emp_bills_category->status) == '0' ? 'selected' : '' }}>
                                    Inactive</option>
                            </select>
                            @if ($errors->has('is_active'))
                                <span class="text-danger">{{ $errors->first('is_active') }}</span>
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
