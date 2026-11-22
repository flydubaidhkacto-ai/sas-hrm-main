@extends('layouts.master')

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <h1>Add Refueling</h1>

        <form action="{{ route('refuelings.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="vehicle_id" class="form-label">Vehicle *</label>
                <select name="vehicle_id" id="vehicle_id" class="init-select2 form-control" required>
                    <option value="">-- Select Vehicle --</option>
                    @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}">{{ $vehicle->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="fuel_type" class="form-label">Fuel Type *</label>
                <select name="fuel_type" id="fuel_type" class="init-select2 form-control" required>
                    <option value="">-- Select Fuel Type --</option>
                    @foreach($fuelTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="fuel_station" class="form-label">Fuel Station *</label>
                <input type="text" name="fuel_station" id="fuel_station" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="budget" class="form-label">Budget *</label>
                <input type="number" step="0.01" name="budget" id="budget" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="place" class="form-label">Place *</label>
                <input type="text" name="place" id="place" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="km_per_unit" class="form-label">Kilometer Per Unit *</label>
                <input type="number" step="0.01" name="km_per_unit" id="km_per_unit" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="driver_id" class="form-label">Driver *</label>
                <select name="driver_id" id="driver_id" class="form-control init-select2" required>
                    <option value="">-- Select Driver --</option>
                    @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="max_unit" class="form-label">Max Unit *</label>
                <input type="number" step="0.01" name="max_unit" id="max_unit" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="odometer_at_refuel" class="form-label">Odometer at Time of Refueling *</label>
                <input type="number" step="0.01" name="odometer_at_refuel" id="odometer_at_refuel" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="unit_taken" class="form-label">Unit Taken *</label>
                <input type="number" step="0.01" name="unit_taken" id="unit_taken" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="fuel_slip_scan_copy" class="form-label">Fuel Slip Scan Copy</label>
                <input type="file" name="fuel_slip_scan_copy" id="fuel_slip_scan_copy" class="form-control">
            </div>

            <button type="submit" class="btn btn-success">Add Refueling</button>
        </form>
    </div>
</div>
@endsection
