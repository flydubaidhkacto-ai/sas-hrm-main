@extends('layouts.master')
@section('title', "Edit Emp. Expense Category")
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
                        Edit Expense Category
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{route('emp-expenses.category.index')}}">Expense Category</a></li>
                        <li class="breadcrumb-item active">
                            Edit Expense Category
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <form class="col-md-12" action="{{ route('emp-expenses.category.update', $category->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Name<sup class="text-danger">*</sup>:</label>
                            <input class="form-control" type="text" id="name" name="name" required value="{{ $category->name }}">
                            @if ($errors->has('name'))
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Status <sup class="text-danger">*</sup>:</label>
                            <br>
                            <select class="form-control" id="status" name="status" required>
                                <option value="">-- Select Status --</option>
                                <option value="0" {{ $category->status == 0 ? 'SELECTED' : '' }}>Inactive</option>
                                <option value="1" {{ $category->status == 1 ? 'SELECTED' : '' }}>Active</option>
                            </select>
                            @if ($errors->has('status'))
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            @endif
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="con-md-6">
                        <button class="btn btn-info">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

@endsection
