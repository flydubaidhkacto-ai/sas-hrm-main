@extends('layouts.master')

@section('title', 'Create Emp Bills Category')

@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">

            <x-page-header :title="'Create Emp Bills Category'" :breadcrumbs="[
                ['name' => 'Emp Bills Categories', 'route' => route('emp-bill-category.index')],
                ['name' => 'Create', 'active' => true],
            ]" />

            <form id="emp-bill-category-form" action="{{ route('emp-bill-category.store') }}" method="POST">
                @csrf
                @method('POST')

                <div class="row">
                    <!-- Name Field -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Category Name<sup class="text-danger">*</sup>:</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter Category Name"
                                required value="{{ old('name') }}">
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
                                <option value="">--Select--</option>
                                <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @if ($errors->has('is_active'))
                                <span class="text-danger">{{ $errors->first('is_active') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 text-center">
                        <button class="btn btn-primary px-5">Save</button>
                    </div>
                </div>
            </form>

        </div>
    </div>

@endsection
