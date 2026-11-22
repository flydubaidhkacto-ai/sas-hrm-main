@extends('layouts.master')
@section('title', 'Create Task Category')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Create Task Category
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('task-categories.index') }}">Task Category</a></li>
                            <li class="breadcrumb-item active">Create Task Category</li>
                        </ul>
                    </div>
                </div>
            </div>

            @include('layouts.alerts')
            {!! Toastr::message() !!}
            <form id="employee-form" action="{{ route('task-categories.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Category Name <sup class="text-danger">*</sup>:</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" required value="{{ old('name') }}">
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            Create Task Category
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
