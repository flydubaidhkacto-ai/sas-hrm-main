@extends('layouts.master')

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <h1>Refueling Details</h1>

        <div class="mb-3">
            <strong>Vehicle:</strong> {{ $refueling->vehicle->name }}
        </div>
        <div class="mb-3">
            <strong>Fuel Type:</strong> {{ $refueling->fuel_type }}
        </div>
        <div class="mb-3">
            <strong>Fuel Station:</strong> {{ $refueling->fuel_station }}
        </div>
        <div class="mb-3">
            <strong>Budget:</strong> {{ $refueling->budget }}
        </div>
        <div class="mb-3">
            <strong>Place:</strong> {{ $refueling->place }}
        </div>
        <div class="mb-3">
            <strong>Kilometer Per Unit:</strong> {{ $refueling->km_per_unit }}
        </div>
        <div class="mb-3">
            <strong>Driver:</strong> {{ $refueling->driver->name }}
        </div>
        <div class="mb-3">
            <strong>Max Unit:</strong> {{ $refueling->max_unit }}
        </div>
        <div class="mb-3">
            <strong>Odometer at Refueling:</strong> {{ $refueling->odometer_at_refuel }}
        </div>
        <div class="mb-3">
            <strong>Unit Taken:</strong> {{ $refueling->unit_taken }}
        </div>
        @if($refueling->fuel_slip_scan_copy)
        <div class="mb-3">
            <strong>Fuel Slip Scan Copy:</strong>
            <a href="{{ asset('uploaded_files/' . $refueling->fuel_slip_scan_copy) }}" target="_blank">View</a>
        </div>
        @endif
    </div>
</div>
@endsection
