@extends('layouts.master')
@section('title', "Balance Sheet")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Balance Sheet</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Balance Sheet</li>
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
        <div class="mb-4">
            <form method="get" action="{{ route('balance.sheet') }}" class="row filter-row mb-4">
                @if(
                role_can('admin-accounts')
                )
                <div class="mb-2 col-sm-6 col-md-3">
                    <div class="form-group form-focus">
                        <div class="cal-icon">
                            <input class="form-control floating datetimepicker" type="text"
                                value="{{ $startDate?->format('d-m-Y') }}" name="start_date">
                        </div>
                        <label class="focus-label">Applied From</label>
                    </div>
                </div>
                <div class="mb-2 col-sm-6 col-md-3">
                    <div class="form-group form-focus">
                        <div class="cal-icon">
                            <input class="form-control floating datetimepicker" type="text"
                                value="{{ $endDate?->format('d-m-Y') }}" name="end_date">
                        </div>
                        <label class="focus-label">Applied To</label>
                    </div>
                </div>
                <div class="mb-2 col-sm-6 col-md-3">
                    <button type="submit" class="btn btn-success btn-block"> Search </button>
                </div>
                @endif
            </form>
            <div class="exports text-right">
                {{-- @include('layouts.exports', [
                'no_docx' => true,
                ]) --}}
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    @include('accounts.balance_sheet.list_table', [
                    'totalAssets' => $totalAssets,
                    'totalLiabilities' => $totalLiabilities,
                    'totalEquity' => $totalEquity,
                    'startDate' => $startDate,
                    'endDate' => $endDate
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