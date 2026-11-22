@extends('layouts.master')
@section('title', 'Roster')
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
                            @if ($isDraft)
                                Draft Rosters
                            @else
                                Active Rosters
                            @endif
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Roster</li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        @if(role_can('roster'))
                            <button type="button" class="btn add-btn m-r-5" data-toggle="modal"
                                    data-target="#smallModal">
                                Create Roster
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            {{-- show success messages --}}
            {!! Toastr::message() !!}
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('roster.index') ? 'active' : '' }}"
                       href="{{ route('roster.index') }}">
                        <i class="fa fa-check text-success"></i>
                        Active
                    </a>
                </li>
                @if(role_can('roster'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('roster.draft') ? 'active' : '' }}"
                           href="{{ route('roster.draft') }}">
                            <i class="fa fa-file"></i>
                            Drafts
                        </a>
                    </li>
                @endif

            </ul>

            <form method="GET" action="{{ $isDraft ? route('roster.draft') : route('roster.index') }}"
                  class="form-inline mb-3">
                <div class="form-group mr-2">
                    <label for="super_department_id" class="mr-1">Station</label>
                    <select name="super_department_id" id="super_department_id" class="form-control">
                        <option value="">All</option>
                        @foreach($super_departments as $dept)
                            <option value="{{ $dept->id }}" @selected($filter['super_department_id'] ==
                            $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mr-2">
                    <label for="start" class="mr-1">Start Date</label>
                    <input type="date" name="start" id="start" class="form-control" value="{{ $filter['start'] }}">
                    <button type="button" class="btn btn-sm btn-secondary ml-1" onclick="clearDate('start')">Clear
                    </button>
                </div>

                <div class="form-group mr-2">
                    <label for="end" class="mr-1">End Date</label>
                    <input type="date" name="end" id="end" class="form-control" value="{{ $filter['end'] }}">
                    <button type="button" class="btn btn-sm btn-secondary ml-1" onclick="clearDate('end')">Clear
                    </button>
                </div>

                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                <button type="button" class="btn btn-success"
                        onclick="printFiltered('{{ $isDraft ? 'draft' : 'active' }}')">Print
                </button>
            </form>

            <div class="mt-2">
                <div id="roster-calendar" class="table-responsive roster-table">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Station</th>
                            <th>Name</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if ($data->count() === 0)
                            <tr>
                                <td colspan="5" class="text-center">No rosters found</td>
                            </tr>
                        @endif
                        @foreach ($data as $roster)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $roster->superDepartment->name ?? 'N/A' }}</td>
                                <td>{{ $roster->name }}</td>
                                <td>{{ carbon($roster->start)->format('d M Y') }}</td>
                                <td>{{ carbon($roster->end)->format('d M Y') }}</td>
                                <td>
                                    @if(role_can('roster'))
                                        @if (!$roster->is_active)
    <form action="{{ route('roster.publish_draft', $roster->id) }}"
          method="POST"
          style="display:inline;"
          onsubmit="return confirm('Are you sure you want to publish this roster?');">
        @csrf
        <button type="submit" class="btn btn-success btn-sm">
            <i class="fa fa-check"></i> &nbsp;
            Publish
        </button>
        </form>
    &nbsp;
@endif
                                    @endif
                                    <button class="btn btn-primary btn-sm" data-toggle="modal"
                                            data-target="#fullCalendarModal{{ $roster->id }}">
                                        <i class="fa fa-eye"></i> &nbsp;
                                        View
                                    </button>
                                    @if(role_can('roster'))
                                        &nbsp;
                                        <a class="btn btn-warning btn-sm"
                                           href="{{ route('roster.edit', $roster->id) }}">
                                            <i class="fa fa-pencil"></i> &nbsp;
                                            Edit
                                        </a>
                                        <a class="btn btn-info btn-sm" href="{{ route('roster.copy', $roster->id) }}">
                                            <i class="fa fa-copy"></i> &nbsp;
                                            Copy
                                        </a>
                                    @endif

                                    <button class="btn btn-success btn-sm" onclick="openPrintPage({{ $roster->id }})">
                                        Print
                                    </button>

                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    {{ $data->withQueryString()->links() }}
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
                        Create Roster
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('roster.create') }}" method="get">
                        <div class="form-group">
                            <label>Station <span class="text-danger">*</span></label>

                            <select name="super_department_id" class="form-control super-department-select2" required>

                                <option value="">Select Station</option>

                                @if(isset($super_departments))
                                    @foreach($super_departments as $dept)
                                        <option value="{{ $dept->id }}"
                                                @selected(old('super_department_id', request()->super_department_id) ==
                                        $dept->id)>
                                        {{ $dept->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="from">From</label>
                            <input type="date" name="from" id="from" class="form-control"
                                   value="{{ now()->addDay()->format('Y-m-d') }}"
                                   Hmin="{{ now()->addDay()->format('Y-m-d') }}">
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
    <style>
        .modal-fullscreen {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            max-width: none;
        }
    </style>
    @foreach ($data as $roster)
        <div class="modal fade" id="fullCalendarModal{{ $roster->id }}" tabindex="-1" role="dialog"
             aria-labelledby="fullCalendarModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fullCalendarModalLabel">
                            {{ $roster->name }}
                        </h5>
                    </div>
                    <div class="modal-body">
                        @php
                            $formatted = $roster->formatted();
                        @endphp
                        <button class="btn btn-success mb-3" onclick="openPrintPage({{ $roster->id }})">Print</button>

                        <div class="d-flex">
                            <div>
                                <div class="table-responsive roster-table roster-view">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Employee</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($formatted as $i1 => $item)
                                            <tr style="height: 61px">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <div style="width: 130px; overflow: auto; text-overflow: ellipsis">
                                                        @foreach ($item['employees'] as $emp_id)
                                                            <div class="badge badge-info h5 d-block p-2 mt-1"
                                                                 title="{{ \App\Models\User::find($emp_id)?->name }}"
                                                                 data-toggle="tooltip"
                                                            >
                                                                {{ \App\Models\User::find($emp_id)?->name }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="table-responsive roster-table roster-view"
                                 style="overflow: auto;"
                            >
                                <table class="table">
                                    <thead>
                                    <tr>
                                        @php
                                            $from = carbon($roster->start);
                                            $to = carbon($roster->end);
                                            $days = $from->diffInDays($to);
                                        @endphp
                                        @for ($i = 0; $i <= $days; $i++)
                                            <th class="table-col">{{
                                        $from->copy()->addDay($i)->format('d M Y') }}</th>
                                        @endfor
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($formatted as $i1 => $item)
                                        <tr style="height: 61px">
                                            @php
                                                $from = carbon($roster->start);
                                            @endphp
                                            @for ($i = 0; $i <= $days; $i++)
                                                <td class="table-col">
                                                    <div>
                                                        @php
                                                            $shiftfound = false;
                                                        @endphp
                                                        @foreach ($item['shifts'][$from->format('Y-m-d')] ?? [] as $shift)
                                                            @if ($shift)
                                                                <div class="shift">
                                                                    <div class="shift-name">
                                                                        
@include('roster.partials.shift-label', [
    'employeeIds' => $item['employees'] ?? [],
    'date' => $from->format('Y-m-d'),
])

                                                                    </div>
                                                                    @php
                                                                        $shiftfound = true;
                                                                    @endphp
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                        @if (
                                                        !$shiftfound &&
                                                        ($roster->no_shift_duty &&
                                                        ($roster->no_shift_duty[$i1] ?? null) &&
                                                        ($roster->no_shift_duty[$i1][$from->format('Y-m-d')] ?? null)))
                                                            <div class="no-duty">
                                                                @include('roster.partials.no-shift-duty', ['duty' => $roster->no_shift_duty[$i1][$from->format('Y-m-d')]])
                                                            </div>
                                                        @elseif(!$shiftfound)
                                                            <div class="no-duty">

                                                            </div>
                                                        @endif
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
                        </div>
                        @if(role_can('roster'))
                            <div class="mt-2">
                                <button class="btn btn-danger" onclick="deleteRoster({{ $roster->id }})">
                                    <i class="fa fa-trash"></i>
                                    Delete Roster
                                </button>
                            </div>
                        @endif
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
        function deleteRoster(id) {
            if (confirm('Are you sure you want to delete this?')) {
                window.location = '{{ route('roster.delete', ':id') }}'.replace(':id', id);
            }
        }

        function openPrintPage(param) {
            // If param is a number, rosterId is assumed.
            if (!isNaN(param)) {
                var printWindow = window.open('/print-roster/' + param, '_blank');
                printWindow.focus();
            } else {
                // If param is string ('draft' or 'active'), filtered print
                const params = new URLSearchParams({
                    super_department_id: document.getElementById('super_department_id').value,
                    start: document.getElementById('start').value,
                    end: document.getElementById('end').value,
                });
                window.open('/print-roster/' + param + '?' + params.toString(), '_blank');
            }
        }

        function clearDate(fieldId) {
            document.getElementById(fieldId).value = '';
        }

        function printFiltered(type) {
            let status = type === 'draft' ? 0 : 1;

            const params = new URLSearchParams({
                super_department_id: document.getElementById('super_department_id').value,
                start: document.getElementById('start').value,
                end: document.getElementById('end').value,
                status: status
            });

            window.open('/print-rosters?' + params.toString(), '_blank');
        }
    </script>

@endsection
