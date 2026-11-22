@extends('layouts.master')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                {!! Toastr::message() !!}
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">SMS</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item active">Send bulk sms</li>
                        </ul>
                    </div>

                </div>
            </div>
            <!-- /Page Header -->

            <!-- Search Filter -->
            
            {{-- message --}}
            {!! Toastr::message() !!}

            <div class="row">
                <div class="col-md-12">

                    <div class="table-responsive">
                        @include('employees.approve_request_table', [
                            'edits' => $edits,
                        ])
                    </div>
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
@endsection
@section('script')
   
@endsection
