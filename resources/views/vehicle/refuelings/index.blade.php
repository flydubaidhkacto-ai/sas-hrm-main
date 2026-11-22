
@extends('layouts.master')
@section('title', "Vehicle Refueling")
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
                            Refueling
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                            <li class="breadcrumb-item active">
                                Refueling
                            </li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a href="{{ route('refuelings.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add Refueling</a>
                    </div>
                </div>
            </div>

            <!-- /Page Header -->
            {!! Toastr::message() !!}
            @include('layouts.alerts')

            <div class="exports">
                <div style="text-align: end" class="mb-2">
                    <a href="{{ add_query_to_current_route(['export' => 'xlsx']) }}" 
                        class="btn btn-sm btn-info" target="_blank">
                        <i class="fa fa-file-excel"></i>
                        Excel
                    </a>
                    <a href="{{ add_query_to_current_route(['export' => 'pdf']) }}" class="btn btn-sm btn-info"
                        target="_blank">
                        <i class="fa fa-file-pdf"></i>
                        PDF
                    </a>

                    <a href="{{ add_query_to_current_route(['export' => 'print']) }}" class="btn btn-sm btn-info"
                        target="_blank">
                        <i class="fa fa-print"></i>
                        Print
                    </a> 
                </div>
            </div>
            <div class="mt-2">
                <div class="table-responsive">
                @include('vehicle.refuelings.list_table', [
                        'refuelings' => $refuelings
                    ])
                </div>
            </div>
        </div>
        <!-- /Page Content -->
    </div>
    <!-- /Page Wrapper -->


    <link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        let table = new DataTable('table', {
            pageLength: 50
        });;
    </script>
    @section('script')
    <script>
        
    </script>
    @endsection
@endsection
