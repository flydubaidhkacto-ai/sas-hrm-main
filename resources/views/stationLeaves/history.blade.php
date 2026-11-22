@extends('layouts.master')
@section('title', "Station Leave History")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Station Leave History
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('station.leave.history') }}">Station Leaves</a></li>
                        <li class="breadcrumb-item active">Station Leave History</li>
                    </ul>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        <div class="mt-2">
            <form method="get" action="{{ route('station.leave.history') }}" class="row filter-row mb-4">
                @if(
                    role_can('station_leave')
                    || user()->admin_of_super_departments
                )
                @if(
                    !role_can('station_leave')
                    && !user()->admin_of_super_departments

                )
                <div class="mb-2 col-sm-6 col-md-3">
                    <label for="">Super Department</label>
                    <input type="hidden" name="super_department"
                        value="{{ user()->department?->super_department?->id }}">
                    <input type="text" class="form-control" value="{{ user()->department?->super_department?->name }}">
                    <script>
                        window.selected_super_department = '{{ user()->department?->super_department_id }}';
                    </script>
                </div>
                @endif
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
                <div class="mb-2 col-sm-6 col-md-3">
                    <div class="form-group form-focus">
                        <div class="cal-icon">
                            <input class="form-control floating month-year-picker" type="text"
                                value="{{ request()->month }}" name="month">
                        </div>
                        <label class="focus-label"> Select Month</label>
                    </div>
                </div>

                <div class="col-sm-6 col-md-3">
                    <button type="submit" class="btn btn-success btn-block"> Search </button>
                </div>
                @else
                <select name="employee" id="" class="form-control d-none">
                    <option value="{{ user()->id }}" selected>
                        {{ user()->name }}
                    </option>
                </select>
                @endif
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
                {{ $data->withQueryString()->links() }}
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
                @include('stationLeaves.show', [
                'leave' => $item
                ])
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@section('script')

<script>
    function openPrintPage(leaveId) {
        var printWindow = window.open('/print-leave/' + leaveId, '_blank');
        printWindow.focus();
    }
</script>
@endsection
