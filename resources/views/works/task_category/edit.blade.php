@extends('layouts.master')
@section('title', "Edit Task Category")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Edit Task Category: {{ $taskCategory->name }}
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('task-categories.index') }}">Task Category</a></li>
                        <li class="breadcrumb-item active">Edit Task Category</li>
                    </ul>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        {!! Toastr::message() !!}
        <form id="task-category-edit-form" action="{{ route('task-categories.update', $taskCategory->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Category Name <sup class="text-danger">*</sup>:</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            name="name" required value="{{ old('name', $taskCategory->name) }}">
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        Update Task Category
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection
@section('script')
<script>
</script>
@endSection
