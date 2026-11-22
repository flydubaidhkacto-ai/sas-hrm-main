@extends('layouts.master')
@section('title', "Vehicle Management")
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
                        Vehicle
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{route('vehicle')}}">Vehicle</a></li>
                        <li class="breadcrumb-item active">
                            Vehicle
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="row">
            <form class="col-md-12" action="{{ route('vehicle.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Name:<span class="text-danger"><sup>*</sup></span></label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
        
                <div class="form-group">
                    <label for="price">Price:<span class="text-danger"><sup>*</sup></span></label>
                    <input type="number" step="0.01" id="price" name="price" class="form-control" value="{{ old('price') }}" required>
                </div>
        
                <div class="form-group">
                    <label for="details">Details<span class="text-danger"><sup>*</sup></span></label>
                    <textarea id="details" name="details" class="form-control">{{ old('details') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="seller_details">Seller Details<span class="text-danger"><sup>*</sup></span></label>
                    <textarea id="seller_details" name="seller_details" class="form-control" required>{{ old('seller_details') }}</textarea>
                </div>
        
                <div class="form-group">
                    <label for="sold_record">Sold</label>
                    <input type="hidden" name="sold_record" value="0">
                    <input type="checkbox" id="sold_record" name="sold_record" value="1">
                </div>
                
                <div class="form-group" id="new_owner_details_show" style="display: none;">
                    <label for="new_owner_details">New Owner Details</label>
                    <textarea id="new_owner_details" name="new_owner_details" class="form-control">{{ old('new_owner_details') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="active_location">Active Location</label>
                    <input type="text" id="active_location" name="active_location" class="form-control" value="{{ old('active_location') }}">
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

    document.addEventListener('DOMContentLoaded', function () {
        const sold_record_checkbox = document.getElementById('sold_record');
        const new_owner_details_show = document.getElementById('new_owner_details_show');
        const new_owner_details_field = document.getElementById('new_owner_details');

        function newOwnerDetails() {
            if (sold_record_checkbox.checked) {
                new_owner_details_show.style.display = 'block';
                new_owner_details_field.required = true;
            } else {
                new_owner_details_field.value = ''; 
                new_owner_details_show.style.display = 'none';
            }
        }

        newOwnerDetails();

        sold_record_checkbox.addEventListener('change', newOwnerDetails);
    });

</script>
@endsection
@endsection
