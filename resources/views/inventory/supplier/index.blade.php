@extends('layouts.master')
@section('title', 'Inventory Supplier List')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Inventory Supplier
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inventory.supplier.index') }}">Supplier</a></li>
                        <li class="breadcrumb-item active">Supplier List</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="{{ route('inventory.supplier.create') }}" class="btn add-btn m-r-5">Create Supplier</a>
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
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Paid Amount</th>
                                <th class="text-right no-sort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suppliers as $key => $supplier)
                            <tr>
                                <td style="width: 5px">
                                    {{ $key + 1 }}
                                </td>
                                <td>{{ $supplier->name }}</td>
                                <td>{{ $supplier->email }}</td>
                                <td>{{ $supplier->phone_number }}</td>
                                <td>
                                    @if ($supplier->status == 1)
                                    <span class="badge badge-success">Active</span>
                                    @else
                                    <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $supplier->paid_amount }}</td>

                                <td class="text-right">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown"
                                            aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-right">

                                            <a class="dropdown-item"
                                                href="{{ route('inventory.supplier.edit', $supplier->id) }}">
                                                <i class="fa fa-pencil m-r-5"></i> Edit
                                            </a>
                                            <form action="{{ route('inventory.supplier.destroy', $supplier->id) }}"
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
                    {{ $suppliers->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection