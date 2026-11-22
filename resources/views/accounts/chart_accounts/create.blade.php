@extends('layouts.master')
@section('title', 'Chart of Accounts')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Chart of Accounts
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Chart of Accounts</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            {!! Toastr::message() !!}
            <!-- Search Filter -->
            <form action="{{ route('chart-of-accounts.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="mt-2 col-sm-6 col-md-12 form-group">
                        <label for="">Name</label>
                        <input type="text" class="form-control" name="name" required>
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="mt-2 col-sm-6 col-md-6 form-group">
                        <label for="">Type</label>
                        <select name="type" id="" class="form-control select">
                            <option value="Asset">Asset</option>
                            <option value="Liability">Liability</option>
                            <option value="Equity">Equity</option>
                            <option value="Revenue">Revenue</option>
                            <option value="Expense">Expense</option>
                        </select>
                        @error('type')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="mt-2 col-sm-6 col-md-6 form-group">
                        <label for="">Amount</label>
                        <input type="number" class="form-control" name="amount" required>
                        @error('amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="mt-2 col-sm-6 col-md-12 form-group">
                        <label for="">Description</label>
                        <textarea name="description" id="" class="form-control"></textarea>
                        @error('description')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="form-group row mt-2">
                    <div class="col-md-6 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            Save
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
            $('.select').select2();
        });
    </script>
@endsection

