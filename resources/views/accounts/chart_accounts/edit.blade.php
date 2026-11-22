@extends('layouts.master')
@section('title', "Chart of Accounts")
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
            <form method="POST" action="{{ route('chart-of-accounts.update', $data->id) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="mt-2 col-sm-6 col-md-12 form-group">
                        <label for="">Name</label><br>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                            name="name" value="{{ old('name', $data->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="mt-2 col-sm-6 col-md-6 form-group">
                        <label for="">Account Type</label><br>
                        <select id="type" class="form-control select @error('type') is-invalid @enderror" name="type" required>
                            <option value="Asset" {{ $data->type == 'Asset' ? 'selected' : '' }}>Asset</option>
                            <option value="Liability" {{ $data->type == 'Liability' ? 'selected' : '' }}>Liability</option>
                            <option value="Equity" {{ $data->type == 'Equity' ? 'selected' : '' }}>Equity</option>
                            <option value="Revenue" {{ $data->type == 'Revenue' ? 'selected' : '' }}>Revenue</option>
                            <option value="Expense" {{ $data->type == 'Expense' ? 'selected' : '' }}>Expense</option>
                        </select>
                        @error('type')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mt-2 col-sm-6 col-md-6 form-group">
                        <label for="">Amount</label><br>
                        <input id="amount" type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror"
                            name="amount" value="{{ old('amount', $data->amount) }}" required>
                        @error('amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="mt-2 col-sm-6 col-md-12 form-group">
                        <label for="">Description</label><br>
                        <textarea id="description" class="form-control @error('description') is-invalid @enderror"
                            name="description" rows="3">{{ old('description', $data->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-6 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            Update Account
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

@endsection
