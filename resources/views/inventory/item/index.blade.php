@extends('layouts.master')
@section('title', 'Inventory Item List')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Inventory Item
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inventory.item.index') }}">Item</a></li>
                        <li class="breadcrumb-item active">Item List</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="{{ route('inventory_barcode_scanner') }}" class="btn btn-info mr-2"><i class="fa fa-qrcode"></i> Barcode Scanner</a>
                    <a href="{{ route('inventory.item.create') }}" class="btn add-btn m-r-5">Create Item</a>
                </div>
            </div>
        </div>
        @include('layouts.alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th>Status</th>
                                <th>Curr Qty</th>
                                <th>Alert Qty</th>
                                <th class="text-right no-sort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $key => $item)
                            <tr>
                                <td style="width: 5px">
                                    {{ $key + 1 }}
                                </td>

                                <td>{{ $item->code }}</td>

                                <td>{{ $item->name }}</td>
                                <td>{{ $item->rel_to_inv_category?->name }}</td>
                                <td>{{ $item->rel_to_inv_unit?->name }}</td>

                                <td>
                                    @if ($item->status == 1)
                                    <span class="badge badge-success">Active</span>
                                    @else
                                    <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $item->current_quantity }}
                                </td>
                                <td>{{ $item->alert_quantity }}</td>

                                <td class="text-right">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown"
                                            aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-right">

                                            <a class="dropdown-item"
                                                href="{{ route('inventory.item.edit', $item->id) }}">
                                                <i class="fa fa-pencil m-r-5"></i> Edit
                                            </a>
                                            <form
                                                action="{{ route('inventory.item.destroy', $item->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="dropdown-item"
                                                    onclick="return confirm('Are you sure you want to delete this item?')">
                                                    <i class="fa fa-trash m-r-5"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    {{-- {{ $items->withQueryString()->links() }} --}}
                </div>
            </div>
        </div>
    </div>
</div>


<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('table', {

        });
</script>
@endsection
