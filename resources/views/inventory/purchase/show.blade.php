@extends('layouts.master')
@section('title', 'Purchase Details')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Purchase Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inventory.purchase.index') }}">Purchases</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Purchase Information</h4>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <th>Challan Number:</th>
                            <td>{{ $purchase->challan_number }}</td>
                        </tr>
                        <tr>
                            <th>Reference Number:</th>
                            <td>{{ $purchase->ref_no }}</td>
                        </tr>
                        <tr>
                            <th>Supplier:</th>
                            <td>{{ $purchase->rel_to_inv_supplier->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Total Cost:</th>
                            <td>{{ number_format($purchase->total_cost, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td>{{ $purchase->created_at->format('d-m-Y') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h4>Purchased Items</h4>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchase->rel_to_inv_item as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->pivot->quantity }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('inventory.purchase.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>

@endsection
