@extends('layouts.master')
@section('title', "Asset")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">
                        Asset
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{route('assets-management.index')}}">Asset</a></li>
                        <li class="breadcrumb-item active">
                            Asset
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="row">
            <form class="col-md-12" action="{{ route('assets-management.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <label for="name">Name:</label>
                        <input name="name" type="text" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label for="tag">Tag:</label>
                        <input name="tag" type="text" class="form-control" value="{{ old('tag') }}" required>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-form-label">Category <sup class="text-danger"></sup>:</label>
                            <br>
                            <select class="form-control select2" id="category_id" name="category_id" required>
                                <option value="">-- Select --</option>
                                @foreach ($category as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <label for="status">Status:</label>
                        <select name="status" class="form-control select2" required>
                            <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', 1) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="client">Client:</label>
                        <input name="client" type="text" class="form-control" value="{{ old('client') }}">
                    </div>

                    <div class="col-md-4">
                        <label for="user">User:</label>
                        <input name="user" type="text" class="form-control" value="{{ old('user') }}">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <label for="location_id">Location:</label>
                        <select name="location_id" class="form-control super-department-select2">
                            @if(old('location_id'))
                                <option value="{{ old('location_id') }}" selected>
                                    {{ \App\Models\SuperDepartment::find(old('location_id'))?->name }}
                                </option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="supplier">Supplier:</label>
                        <input name="supplier" type="text" class="form-control" value="{{ old('supplier') }}">
                    </div>

                    <div class="col-md-4">
                        <label for="manufacturer">Manufacturer:</label>
                        <input name="manufacturer" type="text" class="form-control" value="{{ old('manufacturer') }}">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <label for="model">Model:</label>
                        <input name="model" type="text" class="form-control" value="{{ old('model') }}">
                    </div>

                    <div class="col-md-4">
                        <label for="purchase_date">Purchase Date:</label>
                        <input type="text" name="purchase_date" class="form-control datetimepicker" required value="{{ old('purchase_date') }}" onchange="calculateDuration()">
                    </div>

                    <div class="col-md-4">
                        <label for="warranty_expiration">Warranty Expiration:</label>
                        <input type="text" name="warranty_expiration" class="form-control datetimepicker" required value="{{ old('warranty_expiration') }}" onchange="calculateDuration()">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <label for="serial_number">Serial Number:</label>
                        <input name="serial_number" type="text" class="form-control" value="{{ old('serial_number') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="present_value">Present Value:</label>
                        <input name="present_value" type="number" class="form-control" value="{{ old('present_value') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="purchase_value">Purchase Value:</label>
                        <input name="purchase_value" type="number" class="form-control" value="{{ old('purchase_value') }}">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <label for="remarks">Remarks:</label>
                        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
                    </div>
                </div>
                <div class="mt-2">
                    <button class="btn btn-primary">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- /Page Wrapper -->
@section('script')
<script>
    $('.select2').select2({})

    calculateDuration = () => {
            let start_date = $('.datetimepicker[name="start_date"]').val();
            let end_date = $('.datetimepicker[name="end_date"]').val();
            if(!start_date || !end_date) return
            start_date = moment(start_date, 'DD-MM-YYYY');
            end_date = moment(end_date, 'DD-MM-YYYY');
            const duration = (end_date.diff(start_date, 'days'));
            const days = duration + 1;
            if(days < 1)
            {
                return document.getElementById('leave_duration').innerText = 'Invalid date';
            }
            document.getElementById('leave_duration').innerText = days + ' ' + (days > 1 ? 'days' : 'day');
        }

</script>
@endsection
@endsection
