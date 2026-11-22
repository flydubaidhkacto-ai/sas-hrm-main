
@extends('layouts.master')
@section('title', "Settings")
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            {!! Toastr::message() !!}
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Settings: Interests
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">
                                Interests Setting
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div>
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h4>
                            Loan Interests
                            <span class="float-right">
                                @if(!$loan_have_interests)
                                    <span class="text-warning">
                                        <i class="fa fa-dot-circle-o"></i> Off
                                    </span>
                                @else
                                    <span class="text-success">
                                        <i class="fa fa-dot-circle-o"></i> On
                                    </span>
                                @endif
                                <form action="{{ route('settings.interests.update') }}" class="d-inline-block" method="POST">
                                    @csrf
                                    <input type="hidden" name="key" value="loan_have_interests">
                                    @if(!$loan_have_interests)
                                        <input type="hidden" name="value" value="1">
                                        <button class="btn btn-success btn-sm" type="submit">
                                            Turn On
                                        </button>
                                    @else
                                        <button class="btn btn-warning btn-sm" type="submit">
                                            Turn Off
                                        </button>
                                    @endif
                                </form>
                            </span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.interests.update') }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="loan_interest_rate">Interest Rate</label>
                                    <input required type="number" step="0.01" class="form-control" name="loan_interest_rate" value="{{ $loan_interest_rate ?? old('loan_interest_rate') ?? '0' }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="loan_interest_type">Interest Type</label>
                                    <select required name="loan_interest_type" class="form-control">
                                        <option
                                            @if($loan_interest_type == 'reducing') selected @endif
                                            value="reducing">Reducing</option>
                                        <option
                                            @if($loan_interest_type == 'fixed') selected @endif
                                            value="fixed">Fixed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-info">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-header">
                        <h4>
                            Provident Fund Interests
                            <span class="float-right">
                                @if(!$provident_fund_have_interests)
                                    <span class="text-warning">
                                        <i class="fa fa-dot-circle-o"></i> Off
                                    </span>
                                @else
                                    <span class="text-success">
                                        <i class="fa fa-dot-circle-o"></i> On
                                    </span>
                                @endif
                                <form action="{{ route('settings.interests.update') }}" class="d-inline-block" method="POST">
                                    @csrf
                                    <input type="hidden" name="key" value="provident_fund_have_interests">
                                    @if(!$provident_fund_have_interests)
                                        <input type="hidden" name="value" value="1">
                                        <button class="btn btn-success btn-sm" type="submit">
                                            Turn On
                                        </button>
                                    @else
                                        <button class="btn btn-warning btn-sm" type="submit">
                                            Turn Off
                                        </button>
                                    @endif
                                </form>
                            </span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.interests.update') }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="provident_fund_interest_rate">Interest Rate</label>
                                    <input required type="number" step="0.01" class="form-control" name="provident_fund_interest_rate" value="{{ $provident_fund_interest_rate ?? '0' }}">
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-info">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
