@extends('layouts.master')
@section('title', 'Create Transaction')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Create Transaction
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transactions</a></li>
                            <li class="breadcrumb-item active">Create Transaction</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            {!! Toastr::message() !!}
            <!-- Search Filter -->
            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-sm-6 col-md-6 form-group">
                        <label for="">From Account</label>
                        <select name="from_account_id" id="" class="form-control select2">
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                        @error('from_account_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-sm-6 col-md-6 form-group">
                        <label for="">To Account</label>
                        <select name="to_account_id" id="" class="form-control select2">
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                        @error('to_account_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-md-6 form-group">
                        <label for="">Amount</label>
                        <input type="number" class="form-control" name="amount" required>
                        @error('amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-sm-6 col-md-6 form-group">
                        <label for="">Transaction Number</label>
                        <input type="text" name="transaction_number" class="form-control">
                        @error('transaction_number')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-md-6 form-group">
                        <label for="">Transaction Type</label>
                        <select name="transaction_type" id="" class="form-control select2">
                            <option value="debit">Debit</option>
                            <option value="credit">Credit</option>
                        </select>
                        @error('transaction_type')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-sm-6 col-md-6 form-group">
                        <label for="">Transaction Method</label>
                        <select name="transaction_method" id="" class="form-control select2">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="other">Other</option>
                        </select>
                        @error('transaction_method')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6 col-md-6 form-group">
                        <label for="">Transaction Category</label>
                        <select name="transaction_category" id="" class="form-control select2">
                            <option value="sale">Sale</option>
                            <option value="purchase">Purchase</option>
                            <option value="expense">Expense</option>
                            <option value="revenue">Revenue</option>
                            <option value="other">Other</option>
                        </select>
                        @error('transaction_category')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-sm-6 col-md-6 form-group">
                        <label for="">Cash Flow Category</label>
                        <select name="cash_flow_category" id="" class="form-control select2">
                            <option value="operating">Operating</option>
                            <option value="investing">Investing</option>
                            <option value="financing">Financing</option>
                        </select>
                        @error('cash_flow_category')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- <div class="col-sm-6 col-md-6 form-group">
                        <label for="">Transaction Status</label>
                        <select name="transaction_status" id="" class="form-control select2">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        @error('transaction_status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div> --}}
                </div>

                <div class="row">
                    {{-- <div class="col-sm-6 col-md-6 form-group">
                        <label for="">Cash Flow Category</label>
                        <select name="cash_flow_category" id="" class="form-control select2">
                            <option value="operating">Operating</option>
                            <option value="investing">Investing</option>
                            <option value="financing">Financing</option>
                        </select>
                        @error('cash_flow_category')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div> --}}
                    <div class="col-sm-6 col-md-6 form-group">
                        <label for="">Description</label>
                        <textarea name="description" id="" class="form-control">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>


                <div class="form-group row mt-2">
                    <div class="mx-auto">
                        <button type="submit" class="btn btn-primary">
                            Save Transaction
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
