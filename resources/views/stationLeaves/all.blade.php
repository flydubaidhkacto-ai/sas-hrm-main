@extends('layouts.master')
@section('title', "Leaves")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        {{ ucfirst($type) }} Station Leaves
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('station.leave.history') }}">Station Leaves</a></li>
                        <li class="breadcrumb-item active">Applications</li>
                    </ul>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        <div class="mt-2">
            <form method="get" action="{{
                    $type == 'pending' ? route('station.leave.pending') : ($type == 'approved' ? route('station.leave.all.approve') : route('station.leave.all.rejected'))
                    }}" class="row filter-row mb-4">
                @if(
                    role_can('station_leave')
                    || user()->admin_of_super_departments
                )
                <div class="mb-2 col-sm-6 col-md-3">
                    <label for="">Super Department</label>
                    <select name="super_department" id="" class="form-control super-department-select2">
                        @if(request()->super_department)
                        <option value="{{ request()->super_department }}" selected>
                            {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="mb-2 col-sm-6 col-md-3">
                    <label for="">Department</label>
                    <select name="department" id="" class="form-control department-select2">
                        @if(request()->department)
                        <option value="{{ request()->department }}" selected>
                            {{ \App\Models\Department::find(request()->department)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="mb-2 col-sm-6 col-md-3">
                    <label for="">Designation</label>
                    <select name="designation" id="" class="form-control designation-select2">
                        @if(request()->designation)
                        <option value="{{ request()->designation }}" selected>
                            {{ \App\Models\Designation::find(request()->designation)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="mb-2 col-sm-6 col-md-3">
                    <label for="">Employee</label>
                    <select name="employee" id="" class="form-control employee-select2">
                        @if(request()->employee)
                        <option value="{{ request()->employee }}" selected>
                            {{ \App\Models\User::find(request()->employee)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                @elseif(\App\Models\ApprovalProcess::canApproveEmployees('station_leave', user()))
                <div class="mb-2 mt-2 col-12">
                    <select name="employee" id="" class="form-control init-select2">
                        <option value="">Select an employee</option>
                        <option value="{{ user()->id }}" @if(request()->employee == user()->id) selected @endif>{{
                            user()->name }}</option>
                        @foreach (\App\Models\ApprovalProcess::canApproveEmployees('station_leave', user())['employees'] as  $employee)
                        <option value="{{ $employee->id }}" @if(request()->employee == $employee->id) selected @endif
                            >
                            {{ $employee->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @else
                <select name="employee" id="" class="form-control d-none">
                    <option value="{{ user()->id }}" selected>
                        {{ user()->name }}
                    </option>
                </select>
                @endif
                <div class="mb-2 col-sm-6 col-md-3">
                    <div class="form-group form-focus">
                        <div class="cal-icon">
                            <input class="form-control floating datetimepicker" type="text"
                                value="{{ $from?->format('d-m-Y') }}" name="start_date">
                        </div>
                        <label class="focus-label">Applied From</label>
                    </div>
                </div>
                <div class="mb-2 col-sm-6 col-md-3">
                    <div class="form-group form-focus">
                        <div class="cal-icon">
                            <input class="form-control floating datetimepicker" type="text"
                                value="{{ $to?->format('d-m-Y') }}" name="end_date">
                        </div>
                        <label class="focus-label">Applied To</label>
                    </div>
                </div>
                <div class="mb-2 col-sm-6 col-md-3">
                    <button type="submit" class="btn btn-success btn-block"> Search </button>
                </div>
            </form>
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <div class="mt-2">
                <div class="table-responsive">
                    <div class="exports">
                        {{-- @include('layouts.exports_excel') --}}
                        <div style="text-align: end" class="mb-2">
                            {{-- @if($no_docx ?? false)
                            @else
                            <a href="{{ add_query_to_current_route(['export' => 'docx']) }}" class="btn btn-sm btn-info"
                                target="_blank">
                                <i class="fa fa-file-word-o"></i>
                                Docx
                            </a>
                            @endif --}}
                            <a href="{{ add_query_to_current_route(['export' => 'xlsx']) }}" class="btn btn-sm btn-info"
                                target="_blank">
                                <i class="fa fa-file-excel"></i>
                                Excel
                            </a>
                            <a href="{{ add_query_to_current_route(['export' => 'pdf']) }}" class="btn btn-sm btn-info"
                                target="_blank">
                                <i class="fa fa-file-pdf"></i>
                                PDF
                            </a>
                            <a href="{{ add_query_to_current_route(['export' => 'print']) }}"
                                class="btn btn-sm btn-info" target="_blank">
                                <i class="fa fa-print"></i>
                                Print
                            </a>
                        </div>
                    </div>
                    @include('stationLeaves.all_table', [
                    'data' => $data
                    ])
                </div>
            </div>
            <div class="mt-2">
            </div>
        </div>
    </div>
</div>

@foreach ($data as $item)
<!-- Modal -->
<div class="modal fade edit-appl" id="editAppl{{ $item->id }}" tabindex="-1" role="dialog"
    aria-labelledby="editAppl{{ $item->id }}Title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Station Leave Application of: {{ $item->employee?->name }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" onkeyup="calculateDuration()" onmousemove="calculateDuration()"
                onmouseup="calculateDuration()">
                <div class="station-leave-details"
     data-url="{{ route('station.leave.show', $item) }}">
    <div class="text-center p-3">
        Loading details when opened...
    </div>
</div>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@section('script')

<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
    let table = new DataTable('table.table', {
        pageLength: 50
    });

    $(document).on('show.bs.modal', '.edit-appl', function () {
        const container = $(this).find('.station-leave-details');
        const url = container.data('url');

        if (container.data('loaded') || container.data('loading')) {
            return;
        }

        container.data('loading', true);
        container.html('<div class="text-center p-4">Loading Station Leave details...</div>');

        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (html) {
                container.html(html);
                container.data('loaded', true);

                if (typeof window.calculateDuration === 'function') {
                    window.calculateDuration();
                }
            },
            error: function () {
                container.html(
                    '<div class="alert alert-danger">Unable to load Station Leave details. Please close and reopen the popup to retry.</div>'
                );
            },
            complete: function () {
                container.data('loading', false);
            }
        });
    });
</script>

@endsection
