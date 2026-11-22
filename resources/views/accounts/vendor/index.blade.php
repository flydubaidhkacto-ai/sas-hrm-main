@extends('layouts.master')
@section('title', "Vendor Management")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Vendor Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Vendor Management</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto text-right">
                    <a href="{{ route('vendors.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add New</a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        {{-- message --}}
        {!! Toastr::message() !!}
        <div class="exports text-right">
            {{-- @include('layouts.exports', [
            'no_docx' => true,
            ]) --}}
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    @include('accounts.vendor.list_table', [
                    'vendors' => $vendors
                    ])
                    <div class="mt-2">
                        {{-- {{ $data->withQueryString()->links() }} --}}
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