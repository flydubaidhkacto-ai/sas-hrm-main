@extends('layouts.master')

@section('title', 'My Weekly Schedule')

@push('style')
<style>
    .schedule-card {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        overflow: hidden;
    }
    .weekday-header {
        font-weight: 600;
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
    }
    .today-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .active-header {
        background-color: #e7f3ff;
        border-left: 4px solid #007bff;
    }
    .empty-header {
        background-color: #f8f9fa;
        color: #6c757d;
    }
    .location-badge {
        background-color: #fff;
        border: 1px solid #dee2e6;
        color: #495057;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        margin: 0.25rem;
        display: inline-block;
        font-size: 0.875rem;
    }
    .today-badge {
        background-color: rgba(255,255,255,0.9);
        color: #495057;
        font-weight: 600;
    }
    .distance-info {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
    .notes-section {
        background-color: #f8f9fa;
        padding: 0.75rem;
        border-top: 1px solid #dee2e6;
        font-style: italic;
    }
    .today-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        text-align: center;
    }
    .today-locations {
        background-color: rgba(255,255,255,0.1);
        border-radius: 0.375rem;
        padding: 0.75rem;
        margin-top: 0.75rem;
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
                    <h3 class="page-title">My Weekly Schedule</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Weekly Schedule</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('locations.checkin') }}" class="btn btn-primary">
                        <i class="fa fa-map-marker"></i> Check In
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        @if($todaySchedules->count() > 0)
            <!-- Today's Schedule Banner -->
            <div class="today-banner">
                <h4 class="mb-2">
                    <i class="fa fa-calendar-check mr-2"></i>
                    Today's Schedule - {{ ucfirst($todayWeekday) }}
                </h4>
                <p class="mb-0">You have {{ $todaySchedules->count() }} location(s) to visit today</p>
                <div class="today-locations">
                    @foreach($todaySchedules as $schedule)
                        <span class="location-badge today-badge">
                            <i class="fa fa-map-marker mr-1"></i>{{ $schedule->location->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Weekly Location Schedule</h4>
                        <p class="text-muted mb-0">Your assigned locations for each day of the week</p>
                    </div>
                    <div class="card-body">
                        @if($schedules->count() > 0)
                            <div class="row">
                                @foreach($weekdays as $day => $dayName)
                                    <div class="col-lg-6 col-xl-4">
                                        <div class="schedule-card">
                                            @php
                                                $daySchedules = $schedules->get($day, collect());
                                                $isToday = $day === $todayWeekday;
                                                $hasLocations = $daySchedules->count() > 0;
                                            @endphp

                                            <div class="weekday-header {{ $isToday ? 'today-header' : ($hasLocations ? 'active-header' : 'empty-header') }}">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="fa fa-calendar mr-2"></i>{{ $dayName }}
                                                        @if($isToday)
                                                            <span class="badge badge-light ml-2">Today</span>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <span class="badge {{ $isToday ? 'badge-light' : ($hasLocations ? 'badge-primary' : 'badge-secondary') }}">
                                                            {{ $daySchedules->count() }} {{ $daySchedules->count() == 1 ? 'location' : 'locations' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-3">
                                                @if($hasLocations)
                                                    @foreach($daySchedules as $schedule)
                                                        <div class="mb-3">
                                                            <h6 class="mb-1">{{ $schedule->location->name }}</h6>
                                                            <small class="text-muted d-block">{{ $schedule->location->getFullHierarchy() }}</small>

                                                            @php
                                                                $assignedLocation = $employee->assigned_locations->where('id', $schedule->location->id)->first();
                                                            @endphp

                                                            @if($assignedLocation && $assignedLocation->pivot && $assignedLocation->pivot->distance_km)
                                                                <div class="distance-info">
                                                                    <i class="fa fa-road mr-1"></i>
                                                                    {{ $assignedLocation->pivot->distance_km }}km from home
                                                                </div>
                                                            @endif

                                                            @if($schedule->notes)
                                                                <div class="mt-2">
                                                                    <small class="text-muted">
                                                                        <i class="fa fa-sticky-note mr-1"></i>
                                                                        {{ $schedule->notes }}
                                                                    </small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="text-center text-muted py-3">
                                                        <i class="fa fa-calendar-times fa-2x mb-2"></i>
                                                        <p class="mb-0">No locations scheduled</p>
                                                        <small>You have no visits scheduled for this day</small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-calendar-times fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No Schedule Assigned</h5>
                                <p class="text-muted">You don't have any weekly schedule assigned yet. Please contact your HR administrator.</p>
                                <a href="{{ route('home') }}" class="btn btn-primary">
                                    <i class="fa fa-home"></i> Go to Dashboard
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($schedules->count() > 0)
            <!-- Quick Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <a href="{{ route('locations.checkin') }}" class="btn btn-primary btn-lg btn-block">
                                            <i class="fa fa-map-marker fa-2x mb-2"></i>
                                            <br>Check In to Location
                                        </a>
                                        <small class="text-muted">Punch in at your scheduled locations</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <a href="{{ route('locations.checkin.report') }}" class="btn btn-info btn-lg btn-block">
                                            <i class="fa fa-chart-line fa-2x mb-2"></i>
                                            <br>Check-In History
                                        </a>
                                        <small class="text-muted">View your location check-in history</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <a href="{{ route('home') }}" class="btn btn-secondary btn-lg btn-block">
                                            <i class="fa fa-home fa-2x mb-2"></i>
                                            <br>Dashboard
                                        </a>
                                        <small class="text-muted">Return to main dashboard</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Add some interactivity if needed
    $('.schedule-card').hover(
        function() { $(this).addClass('shadow-sm'); },
        function() { $(this).removeClass('shadow-sm'); }
    );

    // Auto-refresh every 5 minutes to update "today" status
    setTimeout(function() {
        location.reload();
    }, 5 * 60 * 1000);
});
</script>
@endsection
