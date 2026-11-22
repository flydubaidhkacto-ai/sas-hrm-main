@extends('layouts.master')
@section('title', 'Shift List')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        {{-- student tab --}}
        {{-- <div class="mb-3">
            @include('tabs.student')
        </div> --}}

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        @if (request()->students)
                        Student Shifts
                        @else
                        Employee Shifts
                        @endif
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        @if (request()->students)
                        <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
                        @else
                        <li class="breadcrumb-item"><a href="{{ route('employees.active.list') }}">Employees</a>
                        </li>
                        @endif
                        <li class="breadcrumb-item active">Shift List</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="{{ route('shifts.create', ['students' => request()->students]) }}"
                        class="btn add-btn m-r-5">Create Shift</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Shift Name</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>
                                    Duration
                                </th>
                                <th>Break Time</th>
                                <th>Break before OT</th>
                                <th>Assign</th>
                                <th class="text-right no-sort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($shifts->where('is_24_hour_shift', false) as $item)
                            <tr>
                                <td>
                                    {{ $item->id }}
                                </td>
                                <td>
                                    {{ $item->name }}
                                    @if ($item->is_night_shift)
                                    {{-- <span class="badge badge-dark">Night Shift</span> --}}
                                    <span class="text-dark ml-2">
                                        <i class="fa fa-moon"></i>
                                    </span>
                                    @else
                                    <span class="text-warning ml-2">
                                        <i class="fa fa-sun"></i>
                                    </span>
                                    @endif
                                    <br>
                                    <small>
                                        @php
                                        $days = [];
                                        if ($item->saturday) {
                                        $days[] = 'Saturday';
                                        }
                                        if ($item->sunday) {
                                        $days[] = 'Sunday';
                                        }
                                        if ($item->monday) {
                                        $days[] = 'Monday';
                                        }
                                        if ($item->tuesday) {
                                        $days[] = 'Tuesday';
                                        }
                                        if ($item->wednesday) {
                                        $days[] = 'Wednesday';
                                        }
                                        if ($item->thursday) {
                                        $days[] = 'Thursday';
                                        }
                                        if ($item->friday) {
                                        $days[] = 'Friday';
                                        }
                                        @endphp
                                        {{ join(', ', $days) }}
                                    </small>
                                </td>
                                <td>
                                    {{ carbon($item->start)->format('h:i A') }}
                                    {!!  $item->start_date ? '<br><i> Starts from: ' . carbon($item->start_date)->format('d F, Y') .'</i>' : ''  !!}
                                </td>
                                <td>
                                    @php
                                    $start = carbon($item->start);
                                    $end = carbon($item->end);
                                    @endphp
                                    {{ carbon($item->end)->format('h:i A') }}
                                    @if ($item->is_night_shift)
                                    @php
                                    $end = $end->addDay();
                                    @endphp
                                    (Next day)
                                    @endif
                                </td>
                                <td>
                                    {{ seconds_to_hh_mm($item->duration) }}
                                </td>
                                <td>
                                    {{ $item->break_time_minutes ?: '0' }} min
                                    @if ($item->track_break_by_checkout)
                                        <br>
                                        <small class="text-info">CHECK-OUT is required <br>for break time calculations.</small>
                                    @endif
                                </td>
                                <td>{{ $item->break_time_before_ot_minutes ?: '0' }} min</td>
                                <td>
                                    <a href="{{ route('shifts.assign', ['shift' => $item->id, 'students' => request()->students]) }}"
                                        class="btn btn-info btn-sm">
                                        @if (request()->students)
                                        Students
                                        @else
                                        Employees
                                        @endif
                                        ({{ $item->employees()->where('is_active', true)->count() }})
                                    </a>
                                </td>
                                <td class="text-right">
                                    <form action="{{ route('shifts.destroy', $item->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                    </form>
                                    <button class="btn btn-danger btn-sm"
                                        dusk-delete{{ $item->id }}
                                        onclick="if(confirm('Are you sure you want to delete it?')) {event.preventDefault();$(this).prev().submit()}"><i
                                            class="fa fa-trash m-r-5"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if(config('services.have_24_shift') && !request()->students)
            <hr>
            <div class="row">
                <h4 class="col-8">
                    24 Hour Shifts
                </h4>
                <div class="col-auto float-right ml-auto mb-3">
                    <a href="{{ route('shifts.create', ['24_shift' => 1]) }}"
                    class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i>
                        Create 24H Shift
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Shift Name</th>
                                    <th>Max Working H.</th>
                                    <th>Max Overtime</th>
                                    <th>Assign</th>
                                    <th class="text-right no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($shifts->where('is_24_hour_shift', true) as $item)
                                <tr>
                                    <td>
                                        {{ $item->id }}
                                    </td>
                                    <td>
                                        {{ $item->name }}
                                    </td>
                                    <td>{{ seconds_to_hh_mm($item->max_working_minutes*60) }}</td>
                                    <td>{{ seconds_to_hh_mm($item->max_overtime_minutes*60) }}</td>
                                    <td>
                                        <a href="{{ route('shifts.assign', ['shift' => $item->id, 'students' => request()->students]) }}"
                                            class="btn btn-info btn-sm">
                                            @if (request()->students)
                                            Students
                                            @else
                                            Employees
                                            @endif
                                            ({{ $item->employees()->where('is_active', true)->count() }})
                                        </a>
                                    </td>
                                    <td class="text-right">
                                        <form action="{{ route('shifts.destroy', $item->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                        </form>
                                        <button class="btn btn-danger btn-sm"
                                            dusk-delete{{ $item->id }}
                                            onclick="if(confirm('Are you sure you want to delete it?')) {event.preventDefault();$(this).prev().submit()}"><i
                                                class="fa fa-trash m-r-5"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
