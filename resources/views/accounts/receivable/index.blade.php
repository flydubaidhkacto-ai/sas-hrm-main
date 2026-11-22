@extends('layouts.master')
@section('title', "Accounts Receivable")
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Accounts Receivable</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Accounts Receivable</li>
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
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped custom-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Transaction Number</th>
                                            <th>Payment Method</th>
                                            <th>Status</th>
                                            <th>Description</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($receivables as $receivable)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ Carbon\Carbon::parse($receivable->transaction_date)->format('d M, Y') }}</td>
                                            <td>{{ number_format($receivable->amount, 2) }}</td>
                                            <td>{{ $receivable->transaction_number ?? '--' }}</td>
                                            <td>{{ ucfirst($receivable->transaction_method) }}</td>
                                            <td>
                                                @if($receivable->transaction_status == 'approved')
                                                    <span class="badge bg-success">{{ ucfirst($receivable->transaction_status) }}</span>
                                                @elseif($receivable->transaction_status == 'pending')
                                                    <span class="badge bg-warning">{{ ucfirst($receivable->transaction_status) }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ ucfirst($receivable->transaction_status) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $receivable->description ?? '--' }}</td>
                                            <td class="text-right">
                                                {{-- <a href="#" class="btn btn-sm btn-primary">View</a> --}}
                                                <a href="{{ route('account.make.received', $receivable->id) }}" class="btn btn-sm btn-success">Mark as Received</a>
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
