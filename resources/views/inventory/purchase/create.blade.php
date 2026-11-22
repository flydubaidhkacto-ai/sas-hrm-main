@extends('layouts.master')
@section('title', 'Create Purchase')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Create Purchase</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('inventory.purchase.index') }}">Purchase</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ul>
                    </div>
                </div>
            </div>


            <form id="employee-form" action="{{ route('inventory.purchase.store') }}" method="POST">
                @csrf
                @method('POST')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Challan Number<sup class="text-danger">*</sup>:</label>
                            <input type="text" name="challan_number" class="form-control" placeholder="Challan Number"
                                required value="{{ old('challan_number') }}">
                            @if ($errors->has('challan_number'))
                                <span class="text-danger">{{ $errors->first('challan_number') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Reference Number<sup class="text-danger">*</sup>:</label>
                            <input type="text" name="ref_no" class="form-control" placeholder="Reference Number"
                                required value="{{ old('ref_no') }}">
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
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
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
                            <div class="input-group mb-3">
                                <select name="inventory_items[]" class="form-control">
                                    <option value="" disabled selected>Select Item</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="quantity[]" class="form-control" placeholder="Quantity"
                                    min="1">
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary add-btn">+</button>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Total Cost<sup class="text-danger">*</sup>:</label>
                            <input type="number" name="total_cost" class="form-control" placeholder="Total Cost"
                                required value="{{ old('total_cost') }}">
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
            init_select2();
        });

        $(document).on('click', '.remove-btn', function() {
            if ($('#input-group-container .input-group').length > 1) {
                $(this).closest('.input-group').remove();
            } else {
                alert('At least one row is required!');
            }
        });
        function init_select2(){
            $('select').each(function(){
                if(!$(this).hasClass('select2')){
                    $(this).select2();
                }
            });
        }
        init_select2();
    </script>
@endpush
