@extends('layouts.master')
@section('title', 'Inventory Purchases List')
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
                <div class="table-responsive">
                    <table class="table table-striped custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Challan Number</th>
                                <th>Supplier Name</th>
                                <th>Refer Number</th>
                                <th class="text-right no-sort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchases as $key => $purchase)
                            <tr>
                                <td style="width: 5px">
                                    {{ $key + 1 }}
                                </td>
                                <td>{{ $purchase->challan_number }}</td>
                                <td>{{ $purchase->rel_to_inv_supplier->name }}</td>
                                <td>{{ $purchase->ref_no }}</td>
                                <td class="text-right">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown"
                                            aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-right">

                                            <a class="dropdown-item"
                                                href="{{ route('inventory.purchase.edit', $purchase->id) }}">
                                                <i class="fa fa-pencil m-r-5"></i> Edit
                                            </a>

                                            <a class="dropdown-item"
                                                href="{{ route('inventory.purchase.show', $purchase->id) }}">
                                                <i class="fa fa-eye m-r-5"></i> Show
                                            </a>

                                            <form action="{{ route('inventory.purchase.destroy', $purchase->id) }}"
                                                method="POST">
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
                    {{ $purchases->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection