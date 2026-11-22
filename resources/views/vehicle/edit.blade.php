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
                        Edit Vehicle: {{ $vehicle->name }}
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{route('vehicle')}}">Vehicle</a></li>
                        <li class="breadcrumb-item active">
                            Edit Vehicle
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="row">
            <form class="col-md-12" action="{{ route('vehicle.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="" value="{{ $vehicle->id }}">
                <div class="form-group">
                    <label for="name">Name:<span class="text-danger"><sup>*</sup></span></label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name',$vehicle->name) }}" required>
                </div>
        
                <div class="form-group">
                    <label for="price">Price:<span class="text-danger"><sup>*</sup></span></label>
                    <input type="number" step="0.01" id="price" name="price" class="form-control" value="{{ old('price',$vehicle->price) }}" required>
                </div>
        
                <div class="form-group">
                    <label for="details">Details:<span class="text-danger"><sup>*</sup></span></label>
                    <textarea id="details" name="details" class="form-control">{{ old('details',$vehicle->details) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="seller_details">Seller Details:<span class="text-danger"><sup>*</sup></span></label>
                    <textarea id="seller_details" name="seller_details" class="form-control">{{ old('seller_details',$vehicle->seller_details) }}</textarea>
                </div>
        
                {{-- <div class="form-group">
                    <label for="sold_record">Sold</label>
                    <input type="checkbox" id="sold_record" name="sold_record" value="1" {{ isset($vehicle) && $vehicle->sold_record ? 'checked' : '' }}>
                </div> 

                <div class="form-group" id="new_owner_details_show" style="display: none;">
                    <label for="new_owner_details">New Owner Details</label>
                    <textarea id="new_owner_details" name="new_owner_details" class="form-control">{{ old('new_owner_details', $record->new_owner_details ?? '') }}</textarea>
                </div> --}}
        

                <div class="form-group">
                    <label for="sold_record">Sold</label>
                    <input type="hidden" name="sold_record" value="0">
                    <input type="checkbox" id="sold_record" name="sold_record" value="1" {{ isset($vehicle) && $vehicle->sold_record ? 'checked' : '' }}>
                </div>
                
                <div class="form-group" id="new_owner_details_show" style="display: none;">
                    <label for="new_owner_details">New Owner Details:<span class="text-danger"><sup>*</sup></span></label>
                    <textarea id="new_owner_details" name="new_owner_details" class="form-control">{{ old('new_owner_details', $vehicle->new_owner_details ?? '') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="active_location">Active Location</label>
                    <input type="text" id="active_location" name="active_location" class="form-control" value="{{ old('active_location',$vehicle->active_location) }}">
                </div>

                <div class="mt-2">
                    <button class="btn btn-primary">
                        Update
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

    let old_data = new_owner_details_field.value;

    function newOwnerDetails() {
        if (sold_record_checkbox.checked) {
            new_owner_details_show.style.display = 'block';
            new_owner_details_field.required = true;

            if (new_owner_details_field.value.trim() === '') {
                new_owner_details_field.value = old_data;
            }
        } else {
            old_data = new_owner_details_field.value;
            new_owner_details_field.value = '';
            new_owner_details_show.style.display = 'none';
            new_owner_details_field.required = false;
        }
    }

    newOwnerDetails();

    sold_record_checkbox.addEventListener('change', newOwnerDetails);
});



</script>
@endsection
@endsection
