@extends('layouts.master')
@section('title', "Payslips")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Chart of Accounts</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Chart of Accounts</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto text-right">
                    <a href="{{ route('chart-of-accounts.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add
                        Account</a>
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
                    @include('accounts.chart_accounts.list_table', [
                    'data' => $data
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