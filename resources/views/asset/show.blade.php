@extends('layouts.master')
@section('title', "Show Asset")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">
                        Show Asset: {{ $asset->name }}
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{route('assets-management.index')}}">Asset</a></li>
                        <li class="breadcrumb-item active">
                            Show Asset
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Name:</label>
                        <div class="form-control-plaintext">{{ $asset->name }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Tag:</label>
                        <div class="form-control-plaintext">{{ $asset->tag }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Category:</label>
                        <div class="form-control-plaintext">{{ $asset->asset_category?->name }}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Status:</label>
                        <div class="form-control-plaintext">
                            {{ $asset->status == 1 ? 'Active' : 'Inactive' }}
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Client:</label>
                        <div class="form-control-plaintext">{{ $asset->client }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>User:</label>
                        <div class="form-control-plaintext">{{ $asset->user }}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Location:</label>
                        <div class="form-control-plaintext">{{ $asset->super_department?->name ?? $asset->location }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Supplier:</label>
                        <div class="form-control-plaintext">{{ $asset->supplier }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Manufacturer:</label>
                        <div class="form-control-plaintext">{{ $asset->manufacturer }}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Model:</label>
                        <div class="form-control-plaintext">{{ $asset->model }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Purchase Date:</label>
                        <div class="form-control-plaintext">
                            {{ $asset->purchase_date ? date('d-m-Y', strtotime($asset->purchase_date)) : '' }}
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Warranty Expiration:</label>
                        <div class="form-control-plaintext">
                            {{ $asset->warranty_expiration ? date('d-m-Y', strtotime($asset->warranty_expiration)) : '' }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Serial Number:</label>
                        <div class="form-control-plaintext">{{ $asset->serial_number }}</div>
                    </div>
                    @if(role_can('asset-manage'))
                        <div class="col-md-4 mb-3">
                            <label>Present Value:</label>
                            <div class="form-control-plaintext">{{ $asset->present_value }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Purchase Value:</label>
                            <div class="form-control-plaintext">{{ $asset->purchase_value }}</div>
                        </div>
                    @endif
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Remarks:</label>
                        <div class="form-control-plaintext">{{ $asset->remarks }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- /Page Wrapper -->
@endsection
