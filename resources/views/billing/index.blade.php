@extends('layouts.master')
@section('title', "Attendance Request")
@section('content')
{{-- message --}}
<!-- Page Wrapper -->
<div class="page-wrapper" onmousemove="calculateDuration()" onkeyup="calculateDuration()">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-8">
                    <h3 class="page-title"> Billing</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Billing</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        @include('layouts.alerts')
        {!! Toastr::message() !!}
        <div>
            @if ($membership ?? false)
            <h3>
                Subscription:
                <a href="{{ route('billing', ['action' => 'portal', 'id' => $membership['id']]) }}" target="_blank"
                    class="btn btn-primary btn-sm float-right">
                    <i class="fa fa-external-link"></i> Login to Customer Portal
                </a>
            </h3>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <b>Price: </b>
                    {{ $membership['payment_amount'] }} Tk /{{ $membership['subscription_type'] === 'monthly' ? 'month'
                    : 'year' }}
                </div>
                <div class="col-md-12">
                    <b>User limit: </b>
                    {{ $membership['employee_limit'] }}
                </div>
                <div class="col-md-12">
                    <b>Active Modules: </b>
                    @foreach ($membership['modules'] as $feature)
                    {{ ucfirst(str_replace('_', ' ', $feature)) }},
                    @endforeach
                </div>
                <div class="col-md-12">
                    <b>Expires At: </b>
                    {{ carbon($membership['expire_at'])->format('d M, Y h:i A') }}
                </div>
            </div>
            <h3 class="mt-3">
                Payments:
                <a href="{{ route('billing', ['action' => 'pay-next', 'id' => $membership['id']]) }}" target="_blank"
                    class="btn btn-info btn-sm float-right">
                    <i class="fa fa-external-link"></i> Pay next payment
                </a>
            </h3>
            <hr>
            @if($membership['payments'])
            <table class="table">
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($membership['payments'] as $item)
                    <tr>
                        <td>{{ $item['id'] }}</td>
                        <td>{{ $item['amount'] }} Tk</td>
                        <td>
                            @if ($item['status'] === 'pending')
                            <span class="badge bg-warning text-dark">{{ $item['status'] }}</span>
                            @elseif($item['status'] === 'paid')
                            <span class="badge bg-success">{{ $item['status'] }}</span>
                            @elseif($item['status'] === 'failed')
                            <span class="badge bg-danger">{{ $item['status'] }}</span>
                            @else
                            <span class="badge bg-warning">{{ $item['status'] }}</span>
                            @endif
                        </td>
                        <td>{{ carbon($item['created_at'])->format('d M, Y h:i A') }}</td>
                        <td>{{ carbon($item['updated_at'])->format('d M, Y h:i A') }}</td>
                        <td>
                            <a href="{{ route('billing', ['action' => 'view-invoice', 'id' => $item['id']]) }}"
                                target="_blank" class="btn btn-info btn-sm">
                                <i class="fa fa-external-link"></i> View Invoice
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="alert alert-warning">
                No payment found!
            </div>
            @endif
            @else
            <div class="alert alert-danger">
                We failed to get info from the API! Please reload and try again.
            </div>
            <a href="" class="btn btn-info btn-sm"><i class="fa fa-recycle"></i> Reload now!</a>
            @endif
        </div>
    </div>
</div>
<!-- /Page Wrapper -->
@endsection
