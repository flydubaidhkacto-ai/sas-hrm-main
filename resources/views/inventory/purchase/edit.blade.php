@extends('layouts.master')
@section('title', 'Edit Purchase')
@section('content')

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Edit Purchase</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('inventory.purchase.index') }}">Purchase</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form id="employee-form" action="{{ route('inventory.purchase.update', $purchase->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Challan Number<sup class="text-danger">*</sup>:</label>
                            <input type="text" name="challan_number" class="form-control" placeholder="Challan Number"
                                required value="{{ $purchase->challan_number }}">
                            @if ($errors->has('challan_number'))
                                <span class="text-danger">{{ $errors->first('challan_number') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Reference Number<sup class="text-danger">*</sup>:</label>
                            <input type="text" name="ref_no" class="form-control" placeholder="Reference Number"
                                required value="{{ $purchase->ref_no }}">
                            @if ($errors->has('ref_no'))
                                <span class="text-danger">{{ $errors->first('ref_no') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Supplier</label>
                            <select class="select form-control" id="inventory_supplier_id" name="inventory_supplier_id">
                                <option value="">--Select--</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ $supplier->id == $purchase->inventory_supplier_id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('inventory_supplier_id'))
                                <span class="text-danger">{{ $errors->first('inventory_supplier_id') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="col-form-label">Inventory Items<sup class="text-danger">*</sup>:</label>
                        <div id="input-group-container">
                            @foreach ($purchase->rel_to_inv_item as $key => $purchase_item)
                                <div class="input-group mb-3">
                                    <select name="inventory_items[]" class="form-control">
                                        <option value="" disabled selected>Select Item</option>
                                        @foreach ($items as $item)
                                            <option value="{{ $item->id }}" {{ $item->id == $purchase_item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="quantity[]" class="form-control" value="{{ $purchase_item->pivot->quantity }}" placeholder="Quantity"
                                        min="1">

                                    @if ($key == 0)
                                        <button type="button" class="btn btn-primary add-btn">+</button>
                                    @else
                                        <button type="button" class="btn btn-danger remove-btn">−</button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Total Cost<sup class="text-danger">*</sup>:</label>
                            <input type="number" name="total_cost" class="form-control" placeholder="Total Cost"
                                required value="{{ $purchase->total_cost }}">
                            @if ($errors->has('total_cost'))
                                <span class="text-danger">{{ $errors->first('total_cost') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn-block btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).on('click', '.add-btn', function() {
            const newGroup = `
                <div class="input-group mb-3">
                    <select name="inventory_items[]" class="form-control">
                        <option value="" disabled selected>Select Item</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="quantity[]" class="form-control" placeholder="Quantity" min="1">
                    <button type="button" class="btn btn-danger remove-btn">−</button>
                </div>
            `;

            // Append the new group to the container
            $('#input-group-container').append(newGroup);
        });

        $(document).on('click', '.remove-btn', function() {
            if ($('#input-group-container .input-group').length > 1) {
                $(this).closest('.input-group').remove();
            } else {
                alert('At least one row is required!');
            }
        });
    </script>
@endpush
