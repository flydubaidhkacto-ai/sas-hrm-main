@extends('layouts.master')
@section('title', 'Shift Scheduling')
@section('content')
<style>
    #shiftModal {
        position: absolute;
        top: 20%;
        left: 30%;
        padding: 20px;
        background: white;
        border: 1px solid #ccc;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }
</style>
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Shift Scheduling
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Shift Scheduling</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <button type="button" class="btn add-btn m-r-5" data-toggle="modal" data-target="#smallModal">
                        Create Schedule
                    </button>
                </div>
            </div>
        </div>
        {{-- show success messages --}}
        {!! Toastr::message() !!}

        <div class="mt-2">
            <div id="shift-scheduling-calendar">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($schedules as $schedule)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $schedule->name }}</td>
                            <td>{{ carbon($schedule->start)->format('d M Y') }}</td>
                            <td>{{ carbon($schedule->end)->format('d M Y') }}</td>
                            <td>
                                <button class="btn btn-primary" data-toggle="modal"
                                    data-target="#fullCalendarModal{{ $schedule->id }}">
                                    <i class="fa fa-eye"></i> &nbsp;
                                    View
                                </button>
                                {{-- <a href="{{ route('scheduling.shifts.delete', $schedule->id) }}"
                                    class="btn btn-danger">Delete</a> --}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                {{ $schedules->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
{{-- small modal --}}
<div class="modal fade" id="smallModal" tabindex="-1" role="dialog" aria-labelledby="smallModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="smallModalLabel">
                    Create Schedule
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('scheduling.shifts.create') }}" method="get">
                    <div class="form-group">
                        <label for="from">From</label>
                        <input type="date" name="from" id="from" class="form-control"
                            value="{{ now()->addDay()->format('Y-m-d') }}" min="{{ now()->addDay()->format('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label for="to">To</label>
                        <input type="date" name="to" id="to" class="form-control"
                            value="{{ now()->addWeek()->format('Y-m-d') }}"
                            min="{{ now()->addDay()->format('Y-m-d') }}">
                    </div>
                    <button type="submit" class="btn btn-primary">Start</button>
                </form>
            </div>
        </div>
    </div>
</div>
@foreach ($schedules as $schedule)
<div class="modal fade" id="fullCalendarModal{{ $schedule->id }}" tabindex="-1" role="dialog"
    aria-labelledby="fullCalendarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullCalendarModalLabel">
                    {{ $schedule->name }}
                </h5>
            </div>
            <div class="modal-body">
                @php
                $formatted = $schedule->formatted();
                @endphp

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee</th>
                                @php
                                $from = carbon($schedule->start);
                                $to = carbon($schedule->end);
                                $days = $from->diffInDays($to);
                                @endphp
                                @for ($i = 0; $i < $days; $i++) <th>{{ $from->addDay()->format('d M Y') }}</th>
                                    @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($formatted as $group => $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div style="width: 250px">
                                        @foreach ($item['employees'] as $emp_id)
                                        <div class="badge badge-info h5 d-block p-2 mt-1">
                                            {{ \App\Models\User::find($emp_id)?->name }}
                                        </div>
                                        @endforeach
                                    </div>
                                </td>
                                @php
                                $from = carbon($schedule->start);
                                @endphp
                                @for ($i = 0; $i < $days; $i++) <td>
                                    <div style="width: 120px">
                                        <div v-if="shift">
                                            @if($item['shifts'][$from->format('Y-m-d')] ?? null)
                                            {{ $item['shifts'][$from->format('Y-m-d')]?->name }}
                                            <br>
                                            (
                                            {{ carbon($item['shifts'][$from->format('Y-m-d')]?->start)->format('h:i a')
                                            }} - {{ carbon($item['shifts'][$from->format('Y-m-d')]?->end)->format('h:i
                                            a') }}
                                            )
                                            @else
                                            <span class="badge badge-danger">
                                                No Shift
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    </td>

                                    @php
                                    $from->addDay();
                                    @endphp
                                    @endfor
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    <button class="btn btn-danger" onclick="deleteSchedule({{ $schedule->id }})">
                        <i class="fa fa-trash"></i>
                        Delete Schedule
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach
{{-- full calendar --}}
<script>
    function deleteSchedule(id) {
            if(confirm('Are you sure you want to delete this?'))
            {
                window.location = '{{ route('scheduling.shifts.delete', ':id') }}'.replace(':id', id);
            }
        }
</script>
@endsection