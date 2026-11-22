@extends('layouts.master')

@section('title', 'Edit Weekly Schedule - ' . $employee->name)

@push('style')
<style>
    .schedule-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #dee2e6;
    }
    .schedule-table td {
        border: 1px solid #dee2e6;
        vertical-align: top;
        padding: 15px;
    }
    .weekday-column {
        min-width: 200px;
    }
    .location-item {
        border: 1px solid #e3e6ea;
        border-radius: 0.25rem;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        background-color: #fff;
        transition: all 0.2s ease;
    }
    .location-item:hover {
        background-color: #f8f9fa;
        border-color: #007bff;
    }
    .location-item.selected {
        border-color: #007bff;
        background-color: #e7f3ff;
    }
    .select-buttons {
        margin-bottom: 10px;
    }
    .notes-textarea {
        resize: vertical;
        min-height: 60px;
        margin-top: 10px;
    }
    .employee-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .weekday-header {
        position: sticky;
        top: 0;
        z-index: 100;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Weekly Schedule</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('employees.weekly-schedules.index') }}">Weekly Schedules</a></li>
                        <li class="breadcrumb-item active">Edit Schedule</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('employees.weekly-schedules.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-12">
                <!-- Employee Info Card -->
                <div class="employee-info-card">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="mb-1">{{ $employee->name }}</h4>
                            <p class="mb-1">
                                {{ $employee->designation->name ?? 'N/A' }} -
                                {{ $employee->department->name ?? 'N/A' }}
                            </p>
                            <small>Employee ID: {{ $employee->employee_id ?? $employee->id }}</small>
                        </div>
                        <div class="col-auto">
                            <span class="badge badge-light">{{ $employee->assigned_locations->count() }} Assigned Locations</span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    @if($employee->assigned_locations->count() == 0)
                        <div class="card-body">
                            <div class="text-center py-5">
                                <i class="fa fa-map-marker fa-3x text-warning mb-3"></i>
                                <h5 class="text-warning">No Assigned Locations</h5>
                                <p class="text-muted">This employee has no assigned locations. Please assign locations first before creating a weekly schedule.</p>
                                <a href="{{ route('employees.active.edit', $employee->id) }}" class="btn btn-warning">
                                    <i class="fa fa-map-marker"></i> Assign Locations
                                </a>
                            </div>
                        </div>
                    @else
                        <form action="{{ route('employees.weekly-schedules.update', $employee) }}" method="POST" id="scheduleForm">
                            @csrf
                            @method('PUT')

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table schedule-table">
                                        <thead class="weekday-header">
                                            <tr>
                                                <th rowspan="2" style="vertical-align: middle;">Locations</th>
                                                @foreach($weekdays as $day => $dayName)
                                                    <th class="weekday-column">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span>{{ $dayName }}</span>
                                                            <div class="btn-group btn-group-sm">
                                                                <button type="button" class="btn btn-outline-primary btn-xs select-all-btn" data-day="{{ $day }}">All</button>
                                                                <button type="button" class="btn btn-outline-secondary btn-xs select-none-btn" data-day="{{ $day }}">None</button>
                                                            </div>
                                                        </div>
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($employee->assigned_locations as $location)
                                                <tr>
                                                    <td class="location-info">
                                                        <div style="width: 300px">
                                                            <strong>{{ $location->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $location->getFullHierarchy() }}</small>
                                                            @if($location->pivot && $location->pivot->distance_km)
                                                                <br>
                                                                <small class="text-info">
                                                                    <i class="fa fa-road"></i> {{ $location->pivot->distance_km }}km from home
                                                                </small>
                                                            @endif
                                                        </div>
                                                    </td>

                                                    @foreach($weekdays as $day => $dayName)
                                                        <td class="weekday-column">
                                                            @php
                                                                $daySchedules = $existingSchedules->get($day, collect());
                                                                $isSelected = $daySchedules->contains('location_id', $location->id);
                                                                $scheduleIndex = array_search($day, array_keys($weekdays));
                                                            @endphp

                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox"
                                                                       class="custom-control-input location-checkbox"
                                                                       id="location_{{ $day }}_{{ $location->id }}"
                                                                       name="schedules[{{ $scheduleIndex }}][location_ids][]"
                                                                       value="{{ $location->id }}"
                                                                       data-day="{{ $day }}"
                                                                       {{ $isSelected ? 'checked' : '' }}>
                                                                <label class="custom-control-label" for="location_{{ $day }}_{{ $location->id }}">
                                                                    <span class="checkmark-text">{{ $isSelected ? 'Selected' : 'Select' }}</span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach

                                            <!-- Notes Row -->
                                            <tr class="notes-row">
                                                <td><strong>Notes</strong></td>
                                                @foreach($weekdays as $day => $dayName)
                                                    @php
                                                        $daySchedules = $existingSchedules->get($day, collect());
                                                        $scheduleIndex = array_search($day, array_keys($weekdays));
                                                    @endphp
                                                    <td>
                                                        <textarea class="form-control notes-textarea"
                                                                  name="schedules[{{ $scheduleIndex }}][notes]"
                                                                  placeholder="Notes for {{ $dayName }}..."
                                                                  rows="2">{{ $daySchedules->first()->notes ?? '' }}</textarea>

                                                        <!-- Hidden weekday field -->
                                                        <input type="hidden"
                                                               name="schedules[{{ $scheduleIndex }}][weekday]"
                                                               value="{{ $day }}">
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-save"></i> Save Schedule
                                        </button>
                                        <button type="button" class="btn btn-secondary ml-2" onclick="resetForm()">
                                            <i class="fa fa-undo"></i> Reset
                                        </button>
                                    </div>
                                    <div class="col-auto">
                                        <a href="{{ route('employees.weekly-schedules.index') }}" class="btn btn-outline-secondary">
                                            Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Handle checkbox changes properly
    $('.location-checkbox').change(function() {
        const label = $(this).next('label').find('.checkmark-text');
        if ($(this).is(':checked')) {
            label.text('Selected');
            $(this).closest('td').addClass('table-success');
        } else {
            label.text('Select');
            $(this).closest('td').removeClass('table-success');
        }
    });

    // Trigger change event for already checked boxes
    $('.location-checkbox:checked').trigger('change');

    // Handle select all for each day
    $('.select-all-btn').click(function() {
        const day = $(this).data('day');
        $(`.location-checkbox[data-day="${day}"]`).prop('checked', true).trigger('change');
    });

    // Handle select none for each day
    $('.select-none-btn').click(function() {
        const day = $(this).data('day');
        $(`.location-checkbox[data-day="${day}"]`).prop('checked', false).trigger('change');
    });

    // Form validation
    $('#scheduleForm').submit(function(e) {
        const hasAnySchedule = $('.location-checkbox:checked').length > 0;

        if (!hasAnySchedule) {
            e.preventDefault();
            alert('Please select at least one location for one day before saving the schedule.');
            return false;
        }

        // Show loading state
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    });
});

function resetForm() {
    if (confirm('Are you sure you want to reset the form? All unsaved changes will be lost.')) {
        location.reload();
    }
}

// Add visual feedback for better UX
$(document).on('mouseenter', '.location-checkbox', function() {
    $(this).closest('td').addClass('table-info');
});

$(document).on('mouseleave', '.location-checkbox', function() {
    if (!$(this).is(':checked')) {
        $(this).closest('td').removeClass('table-info');
    }
});
</script>
@endsection
