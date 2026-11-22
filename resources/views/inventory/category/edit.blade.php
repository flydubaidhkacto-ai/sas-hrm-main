@extends('layouts.master')
@section('title', 'Category Edit')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Edit Category</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('inventory.category.index') }}">Category</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ul>
                    </div>
                </div>
            </div>

            @include('layouts.alerts')
            <form id="employee-form" action="{{ route('inventory.category.update', $category->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Name<sup class="text-danger">*</sup>:</label>
                            <input type="text" name="name" class="form-control" required
                                value="{{ $category->name }}">
                            @if ($errors->has('name'))
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="select form-control" id="status" name="status">
                                <option value="">--Select--</option>
                                <option value="0" {{ $category->status == 0 ? 'selected' : '' }}> Deactive
                                </option>
                                <option value="1" {{ $category->status == 1 ? 'selected' : '' }}> Active
                                </option>
                            </select>
                            @if ($errors->has('status'))
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            @endif
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn-block btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
