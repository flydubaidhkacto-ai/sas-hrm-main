@extends('layouts.master')
@section('title', 'Edit Transaction')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Edit Transaction
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transactions</a></li>
                            <li class="breadcrumb-item active">Edit Transaction</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            {!! Toastr::message() !!}
            <!-- Search Filter -->

            <form method="POST" action="{{ route('transactions.update', $transaction->id) }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-sm-6 col-md-6 form-group">
                        <label for="">From Account</label>
                        <select name="from_account_id" class="form-control select2">
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}"
                                    {{ $transaction->from_account_id == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
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
                        <select name="to_account_id" class="form-control select2">
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}"
                                    {{ $transaction->to_account_id == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
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
                        <input type="number" class="form-control" name="amount"
                            value="{{ old('amount', $transaction->amount) }}" required>
                        @error('amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-sm-6 col-md-6 form-group">
                        <label for="">Transaction Number</label>
                        <input type="text" name="transaction_number" class="form-control"
                            value="{{ old('transaction_number', $transaction->transaction_number) }}">
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
                        <select name="transaction_type" class="form-control select2">
                            <option value="debit" {{ $transaction->transaction_type == 'debit' ? 'selected' : '' }}>Debit
                            </option>
                            <option value="credit" {{ $transaction->transaction_type == 'credit' ? 'selected' : '' }}>
                                Credit
                            </option>
                        </select>
                        @error('transaction_type')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-sm-6 col-md-6 form-group">
                        <label for="">Transaction Method</label>
                        <select name="transaction_method" class="form-control select2">
                            <option value="cash" {{ $transaction->transaction_method == 'cash' ? 'selected' : '' }}>Cash
                            </option>
                            <option value="bank" {{ $transaction->transaction_method == 'bank' ? 'selected' : '' }}>Bank
                            </option>
                            <option value="credit_card"
                                {{ $transaction->transaction_method == 'credit_card' ? 'selected' : '' }}>
                                Credit Card</option>
                            <option value="other" {{ $transaction->transaction_method == 'other' ? 'selected' : '' }}>
                                Other
                            </option>
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
                        <select name="transaction_category" class="form-control select2">
                            <option value="sale" {{ $transaction->transaction_category == 'sale' ? 'selected' : '' }}>
                                Sale
                            </option>
                            <option value="purchase"
                                {{ $transaction->transaction_category == 'purchase' ? 'selected' : '' }}>
                                Purchase</option>
                            <option value="expense"
                                {{ $transaction->transaction_category == 'expense' ? 'selected' : '' }}>
                                Expense</option>
                            <option value="revenue"
                                {{ $transaction->transaction_category == 'revenue' ? 'selected' : '' }}>
                                Revenue</option>
                            <option value="other" {{ $transaction->transaction_category == 'other' ? 'selected' : '' }}>
                                Other
                            </option>
                        </select>
                        @error('transaction_category')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-sm-6 col-md-6 form-group">
                        <label for="">Cash Flow Category</label>
                        <select name="cash_flow_category" class="form-control select2">
                            <option value="operating"
                                {{ $transaction->cash_flow_category == 'operating' ? 'selected' : '' }}>Operating
                            </option>
                            <option value="investing"
                                {{ $transaction->cash_flow_category == 'investing' ? 'selected' : '' }}>Investing
                            </option>
                            <option value="financing"
                                {{ $transaction->cash_flow_category == 'financing' ? 'selected' : '' }}>Financing
                            </option>
                        </select>
                        @error('cash_flow_category')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- <div class="col-sm-6 col-md-6 form-group">
                        <label for="">Transaction Status</label>
                        <select name="transaction_status" class="form-control select2">
                            <option value="pending" {{ $transaction->transaction_status == 'pending' ? 'selected' : '' }}>
                                Pending
                            </option>
                            <option value="approved"
                                {{ $transaction->transaction_status == 'approved' ? 'selected' : '' }}>
                                Approved</option>
                            <option value="rejected"
                                {{ $transaction->transaction_status == 'rejected' ? 'selected' : '' }}>
                                Rejected</option>
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
                        <select name="cash_flow_category" class="form-control select2">
                            <option value="operating"
                                {{ $transaction->cash_flow_category == 'operating' ? 'selected' : '' }}>Operating
                            </option>
                            <option value="investing"
                                {{ $transaction->cash_flow_category == 'investing' ? 'selected' : '' }}>Investing
                            </option>
                            <option value="financing"
                                {{ $transaction->cash_flow_category == 'financing' ? 'selected' : '' }}>Financing
                            </option>
                        </select>
                        @error('cash_flow_category')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div> --}}

                    <div class="col-sm-6 col-md-6 form-group">
                        <label for="">Description</label>
                        <textarea name="description" class="form-control">{{ old('description', $transaction->description) }}</textarea>
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
                            Update Transaction
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

