@extends('layouts.master')
@section('title', "Accounts Payable")
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Accounts Payable</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Accounts Payable</li>
                        </ul>
                    </div>
                    {{-- <div class="col-auto float-right ml-auto text-right">
                        <a href="{{ route('profit.loss') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add New</a>
                    </div> --}}
                </div>
            </div>
			<!-- /Page Header -->

            {{-- message --}}
            {!! Toastr::message() !!}
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>0-30 Days</h5>
                            <h3>{{ number_format($aging['0-30'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>31-60 Days</h5>
                            <h3>{{ number_format($aging['31-60'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>61-90 Days</h5>
                            <h3>{{ number_format($aging['61-90'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>90+ Days</h5>
                            <h3>{{ number_format($aging['90+'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payables List -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped custom-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Bill #</th>
                                            <th>Vendor</th>
                                            <th>Due Date</th>
                                            <th>Amount</th>
                                            <th>Paid</th>
                                            <th>Balance</th>
                                            <th>Status</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payables as $bill)
                                        <tr>
                                            <td>{{ $bill->bill_number }}</td>
                                            <td>{{ $bill->vendor->name }}</td>
                                            <td>{{ $bill->due_date->format('d M Y') }}</td>
                                            <td>{{ number_format($bill->amount, 2) }}</td>
                                            <td>{{ number_format($bill->paid_amount, 2) }}</td>
                                            <td>{{ number_format($bill->amount - $bill->paid_amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $bill->status == 'paid' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($bill->status) }}
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <a href="{{ route('bills.show', $bill->id) }}" class="btn btn-sm btn-primary">View</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Content -->
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
