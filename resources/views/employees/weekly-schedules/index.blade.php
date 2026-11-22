@extends('layouts.master')

@section('title', 'Employee Weekly Schedules')

@push('style')
<style>
    .schedule-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .weekday-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        margin-right: 0.25rem;
        margin-bottom: 0.25rem;
    }
    .location-badge {
        background-color: #e9ecef;
        color: #495057;
        border-radius: 0.25rem;
        padding: 0.125rem 0.375rem;
        margin: 0.125rem;
        font-size: 0.75rem;
        display: inline-block;
    }
    .employee-cell {
        min-width: 150px;
    }
    .schedule-cell {
        min-width: 120px;
        max-width: 300px;
    }
    .no-schedule {
        color: #6c757d;
        font-style: italic;
    }
    .actions-cell {
        min-width: 120px;
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
                    <h3 class="page-title">Employee Weekly Schedules</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Weekly Schedules</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('employees.active.list') }}" class="btn btn-outline-primary">
                        <i class="fa fa-users"></i> Manage Employees
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="card-title">Employee Weekly Schedules</h4>
                                <p class="text-muted mb-0">Manage employee weekly location schedules</p>
                            </div>
                            <div class="col-md-6">
                                <div style="width: 300px; float: right">
                                    <label for="">Report Date:</label>
                                    <input type="date" class="form-control" name="date" value="{{ $date ?? today() }}"
                                        onchange="location = '{{ request()->fullUrl() }}?date=' + this.value"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($employees->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover schedule-table init-table">
                                    <thead>
                                        <tr>
                                            <th>S.L</th>
                                            <th class="employee-cell">Employee</th>
                                            <th class="schedule-cell">Saturday</th>
                                            <th class="schedule-cell">Sunday</th>
                                            <th class="schedule-cell">Monday</th>
                                            <th class="schedule-cell">Tuesday</th>
                                            <th class="schedule-cell">Wednesday</th>
                                            <th class="schedule-cell">Thursday</th>
                                            <th class="schedule-cell">Friday</th>
                                            <th class="actions-cell">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employees as $employee)
                                            @php
                                                $schedulesByDay = $employee->weekly_schedules->groupBy('weekday');
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="employee-cell">
                                                    <div>
                                                        <h6 class="mb-1">{{ $employee->name }}</h6>
                                                        <small class="text-muted">
                                                            {{ $employee->designation->name ?? 'N/A' }} -
                                                            {{ $employee->department->name ?? 'N/A' }}
                                                        </small>
                                                        <br>
                                                        <span class="badge badge-info badge-sm">
                                                            {{ $employee->assigned_locations->count() }} Assigned Locations
                                                        </span>
                                                    </div>
                                                </td>

                                                @foreach(['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day)
                                                    <td class="schedule-cell">
                                                        @if(isset($schedulesByDay[$day]) && $schedulesByDay[$day]->count() > 0)
                                                            <div style="width: 300px" class="w">
                                                                @foreach($schedulesByDay[$day] as $schedule)
                                                                    <span style="padding: 2px 5px; border-radius: 7px"
                                                                        @class(
                                                                            $attendances->where('user_id', $employee->id)
                                                                                ->where('location_id', $schedule->location->id)
                                                                                ->first()?->check_at->format('l') === ucfirst($day)
                                                                            ? 'bg-info text-white'
                                                                            : "bg-danger text-white"
                                                                        )
                                                                    >
                                                                        {{ $schedule->location->fullPath }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span class="no-schedule">No schedule</span>
                                                        @endif
                                                    </td>
                                                @endforeach

                                                <td class="actions-cell">
                                                    <div class="btn-group">
                                                        <a href="{{ route('employees.weekly-schedules.edit', $employee) }}"
                                                           class="btn btn-primary btn-sm" title="Edit Schedule">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <a href="{{ route('employees.active.edit', $employee->id) }}"
                                                           class="btn btn-secondary btn-sm" title="Manage Locations">
                                                            <i class="fa fa-map-marker"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Summary Statistics -->
                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $employees->count() }}</h4>
                                            <p class="mb-0">Total Employees</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $employees->filter(function($emp) { return $emp->weekly_schedules->count() > 0; })->count() }}</h4>
                                            <p class="mb-0">With Schedules</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $employees->filter(function($emp) { return $emp->weekly_schedules->count() == 0; })->count() }}</h4>
                                            <p class="mb-0">No Schedule</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $employees->sum(function($emp) { return $emp->assigned_locations->count(); }) }}</h4>
                                            <p class="mb-0">Total Locations</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Active Employees Found</h5>
                                <p class="text-muted">There are no active employees to manage schedules for.</p>
                                <a href="{{ route('employees.active.create') }}" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Add Employee
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable for better table functionality
    if ($('.schedule-table tbody tr').length > 10) {
        $('.schedule-table').DataTable({
            "pageLength": 25,
            "ordering": true,
            "searching": true,
            "pagingType": "simple_numbers",
            "columnDefs": [
                { "orderable": false, "targets": -1 } // Disable sorting on Actions column
            ]
        });
    }

    // Add tooltip to truncated location names
    $('[title]').tooltip();
});
</script>
@endsection
