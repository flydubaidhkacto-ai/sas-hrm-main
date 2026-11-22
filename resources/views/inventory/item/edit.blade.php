@extends('layouts.master')
@section('title', 'Item Edit')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Edit Item</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('inventory.item.index') }}">Item</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ul>
                    </div>
                </div>
            </div>

            @include('layouts.alerts')
            <form id="employee-form" action="{{ route('inventory.item.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Name<sup class="text-danger">*</sup>:</label>
                            <input type="text" name="name" class="form-control" required
                                value="{{ $item->name }}">
                            @if ($errors->has('name'))
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Alert Quantity<sup class="text-danger">*</sup>:</label>
                            <input type="number" name="alert_quantity" class="form-control" required value="{{ $item->alert_quantity }}">
                            @if ($errors->has('alert_quantity'))
                                <span class="text-danger">{{ $errors->first('alert_quantity') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="select form-control" id="status" name="status">
                                <option value="">--Select--</option>
                                <option value="0" {{ $item->status == 0 ? 'selected' : '' }}> Deactive
                                </option>
                                <option value="1" {{ $item->status == 1 ? 'selected' : '' }}> Active
                                </option>
                            </select>
                            @if ($errors->has('status'))
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Inventory Category</label>
                            <select class="select form-control" id="inventory_category_id" name="inventory_category_id">
                                <option value="">--Select--</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ $category->id == $item->inventory_category_id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('inventory_category_id'))
                                <span class="text-danger">{{ $errors->first('inventory_category_id') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Inventory Category</label>
                            <select class="select form-control" id="inventory_unit_id" name="inventory_unit_id">
                                <option value="">--Select--</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" {{ $unit->id == $item->inventory_unit_id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('inventory_unit_id'))
                                <span class="text-danger">{{ $errors->first('inventory_unit_id') }}</span>
                            @endif
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn-block btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
