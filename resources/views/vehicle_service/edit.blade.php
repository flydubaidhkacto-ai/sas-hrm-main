@extends('layouts.master')
@section('title', "Vehicle Services Management")
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
                        Edit Vehicle Services
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{route('vehicle_service')}}">Vehicle Services</a></li>
                        <li class="breadcrumb-item active">
                            Edit Vehicle Services
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="row">
            <form class="col-md-12" action="{{ route('vehicle_service.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="" value="{{ $service->id }}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vehicle_id">Vehicle: <span class="text-danger"><sup>*</sup></span></label>
                            <select id="vehicle_id" name="vehicle_id" class="form-control select2" required>
                                <option value="" selected>Select a vehicle</option>
                                @foreach ($vehicle as $row)
                                    <option value="{{ $row->id }}" 
                                        {{ (old('vehicle_id') ?? $service->vehicle_id) == $row->id ? 'selected' : '' }}>
                                        {{ $row->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cost">Cost: <span class="text-danger"><sup>*</sup></span></label>
                            <input type="number" step="0.01" id="cost" name="cost" class="form-control" 
                                value="{{ old('cost') ?? $service->cost }}" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reason">Reason: <span class="text-danger"><sup>*</sup></span></label>
                    <input type="text" id="reason" name="reason" class="form-control" 
                        value="{{ old('reason') ?? $service->reason }}" required>
                </div>
                
                <div class="form-group">
                    <label for="changed_parts">Changed Parts: <span class="text-danger"><sup>*</sup></span></label>
                    <textarea id="changed_parts" name="changed_parts" class="form-control" required>{{ old('changed_parts') ?? $service->changed_parts }}</textarea>
                </div>
                
                @if(role_can('vehicle-management') && user()->is_super_admin())
                    <div class="form-group">
                        <label for="responsible_person_id">Responsible Person: <span class="text-danger"><sup>*</sup></span></label>
                        <select id="responsible_person_id" name="responsible_person_id" class="form-control select2" required>
                            <option value="" selected>Select a responsible person</option>
                            @foreach ($employee as $row)
                                <option value="{{ $row->id }}" 
                                    {{ (old('responsible_person_id') ?? $service->responsible_person_id) == $row->id ? 'selected' : '' }}>
                                    {{ $row->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="responsible_person_id" value="{{ $service->responsible_person_id ?? user()->id }}">
                @endif
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="repairman_name">Repairman Name</label>
                            <input type="text" id="repairman_name" name="repairman_name" class="form-control" 
                                value="{{ old('repairman_name') ?? $service->repairman_name }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="repairman_location">Repairman Location</label>
                            <input type="text" id="repairman_location" name="repairman_location" class="form-control" 
                                value="{{ old('repairman_location') ?? $service->repairman_location }}">
                        </div>
                    </div>
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

</script>
@endsection
@endsection
