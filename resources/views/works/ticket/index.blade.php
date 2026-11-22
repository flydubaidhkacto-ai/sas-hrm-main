@extends('layouts.master')
@section('title', "Tickets")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Tickets</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Tickets</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto text-right">
                    <a href="{{ route('tickets.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Open
                        Ticket</a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        @include('layouts.alerts')
        <div class="mt-2">

            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <div class="mt-2">
                <div class="table-responsive">
                    @include('works.ticket.list_table', [
                    'tickets' => $tickets
                    ])
                </div>
            </div>
            <div class="mt-2">
                {{-- {{ $tickets->withQueryString()->links() }} --}}
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('table', {
            pageLength: 50
        });
</script>
@endsection