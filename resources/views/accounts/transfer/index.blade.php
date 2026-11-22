@extends('layouts.master')
@section('title', "Transfer Account")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Transfer Account</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Transfer Account</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto text-right">
                    <a href="{{ route('transfers.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add New</a>
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
                    @include('accounts.transfer.list_table', [
                    'transfers' => $transfers
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

</script>
@endsection