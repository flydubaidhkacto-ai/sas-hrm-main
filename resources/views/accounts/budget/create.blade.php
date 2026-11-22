@extends('layouts.master')
@section('title', 'Budget Management')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Budget Management</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('budgets.index') }}">Budget Management</a></li>
                            <li class="breadcrumb-item active">Create Budget</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            {!! Toastr::message() !!}

            <form action="{{ route('budgets.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="mt-2 col-sm-6 col-md-6 form-group">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control @error('category') is-invalid @enderror" id="category" name="category" value="{{ old('category') }}" required>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-2 col-sm-6 col-md-6 form-group">
                        <label for="budget_limit" class="form-label">Budget Limit</label>
                        <input type="number" step="0.01" class="form-control @error('budget_limit') is-invalid @enderror" id="budget_limit" name="budget_limit" value="{{ old('budget_limit') }}" required>
                        @error('budget_limit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="mt-2 col-sm-6 col-md-6 form-group">
                        <label for="amount_spent" class="form-label">Amount Spent</label>
                        <input type="number" step="0.01" class="form-control @error('amount_spent') is-invalid @enderror" id="amount_spent" name="amount_spent" value="{{ old('amount_spent') }}" required>
                        @error('amount_spent')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-2 col-sm-6 col-md-6 form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row mt-2">
                    <div class="mx-auto">
                        <button type="submit" class="btn btn-primary">
                            Save Budget
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- /Page Wrapper -->
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endsection
