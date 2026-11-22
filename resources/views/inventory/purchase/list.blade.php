@extends('layouts.master')
@section('title', 'Inventory Purchases List')
@push('style')
        {{--  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">  --}}
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/css/select2.min.css" rel="stylesheet">

@endpush
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Inventory Purchases
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('inventory.purchase.index') }}">Purchases</a></li>
                            <li class="breadcrumb-item active">Purchases List</li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a href="{{ route('inventory.purchase.create') }}" class="btn add-btn m-r-5">Create Purchases</a>
                    </div>
                </div>
            </div>
            @include('layouts.alerts')
            <div class="row">
                <div class="col-md-12">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select id="supplierFilter" class="form-control">
                                <option value="">Filter by Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="date" id="dateFilter" class="form-control" placeholder="Filter by Date Range">
                        </div>
                    </div>

                    {{ $dataTable->table(['id' => 'inventory_purchase-table']) }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    {{--  <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>  --}}

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/js/select2.min.js"></script>



    {!! str_replace("http:", is_app_local() ? 'http:' : "https:", $dataTable->scripts(attributes: ['type' => 'module'])) !!}

    <script>
        $(document).ready(function() {

            $('#supplierFilter').on('change', function() {
                let table = $('#inventory_purchase-table').DataTable();
                table.ajax.params().supplierFilter = $(this).val();
                table.ajax.reload();
            });

           $('#dateFilter').on('change', function() {
                let table = $('#inventory_purchase-table').DataTable();
                table.ajax.params().dateFilter = $(this).val();
                table.ajax.reload();
            });

        });
    </script>
@endpush
