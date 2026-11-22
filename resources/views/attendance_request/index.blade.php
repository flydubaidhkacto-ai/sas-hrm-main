@extends('layouts.master')
@section('title', "Attendance Request")
@section('content')
{{-- message --}}
<!-- Page Wrapper -->
<div class="page-wrapper" onmousemove="calculateDuration()" onkeyup="calculateDuration()">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-8">
                    <h3 class="page-title">{{ "Att. In/Out
                        Request" }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">{{ "Att. In/Out Request" }}</li>
                    </ul>
                </div>
                <div class="col-sm-4 text-right">
                    @if(role_can('attendance-requests.apply') || role_can('attendance-requests'))
                    <a href="{{ route('attendance-requests.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i>
                        New Request
                    </a>
                    @endif
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        @include('layouts.alerts')
        {!! Toastr::message() !!}
        <div class="row">
            <form class="col-md-12" method="GET">
                <div class="row">
                    @if(
                    role_can('attendance-requests')
                    )
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Super Department</label>
                        <select name="super_department" id="" class="form-control super-department-select2">
                            @if (request()->super_department)
                            <option value="{{ request()->super_department }}" selected>
                                {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Department</label>
                        <select name="department" id="" class="form-control department-select2">
                            @if (request()->department)
                            <option value="{{ request()->department }}" selected>
                                {{ \App\Models\Department::find(request()->department)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Designation</label>
                        <select name="designation" id="" class="form-control designation-select2">
                            @if (request()->designation)
                            <option value="{{ request()->designation }}" selected>
                                {{ \App\Models\Designation::find(request()->designation)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Employee</label>
                        <select name="employee" id="" class="form-control employee-select2">
                            @if (request()->employee)
                            <option value="{{ request()->employee }}" selected>
                                {{ \App\Models\User::find(request()->employee)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    @elseif(\App\Models\ApprovalProcess::canApproveEmployees('attendance_request', user()))
                    <div class="mt-2 col-12">
                        <label for="">Select Employee:</label>
                        <select name="employee" id="" class="form-control init-select2" onchange="loadBalance(this)">
                            <option value="">Select an employee</option>
                            <option value="{{ user()->id }}" @if(request()->employee == user()->id) selected @endif>{{
                                user()->name }}</option>
                            @foreach (\App\Models\ApprovalProcess::canApproveEmployees('attendance_request',
                            user())['employees'] as $employee)
                            <option value="{{ $employee->id }}" @if(request()->employee == $employee->id) selected
                                @endif
                                >
                                {{ $employee->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <input type="hidden" name="employee" value="{{ user()->id }}">
                    @endif
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">From Date:</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $from->format('Y-m-d') }}">
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">To Date:</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $to->format('Y-m-d') }}">
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <div class="form-group form-focus select-focus">
                            <label class="-label">Status</label>
                            <select class="select" name="type">
                                <option @if($type==="pending" ) selected @endif>Pending</option>
                                <option @if($type==="approved" ) selected @endif>Approved</option>
                                <option @if($type==="rejected" ) selected @endif>Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <button class="btn btn-primary">
                        Search
                    </button>
                </div>
            </form>
        </div>
        <div class="row">
            <div class="col-12 mt-2">
                <div class="table-responsive">
                    <table class="table table-striped custom-table">
                        <thead>
                            <tr>
                                <th>S.L</th>
                                <th>Ref.</th>
                                <th>ID</th>
                                <th>Emp. ID</th>
                                <th>Employee</th>
                                <th>Sup. Department</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>
                                    Date
                                </th>
                                <th>
                                    Punch In
                                </th>
                                <th>
                                    Punch Out
                                </th>
                                <th>
                                    Applied At
                                </th>
                                <th>
                                    Status
                                </th>
                                <th>
                                    Duty Type
                                </th>
                                <th>
                                    Note
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->user_id }}</td>
                                <td>{{ $item->employee?->employee_id }}</td>
                                <td>{{ $item->employee?->name }}</td>
                                <td>{{ $item->employee?->department?->super_department?->name }}</td>
                                <td>{{ $item->employee?->department?->name }}</td>
                                <td>{{ $item->employee?->designation?->name }}</td>
                                <td>{{ carbon($item->date)->format('d M, Y') }}</td>
                                <td>
                                    {{ $item->punch_in ? carbon($item->punch_in)->format('d M, Y h:i A') : '--' }}
                                </td>
                                <td>
                                    {{ $item->punch_out ? carbon($item->punch_out)->format('d M, Y h:i A') : '--' }}
                                </td>
                                <td>
                                    {{ carbon($item->created_at)->format('d M, Y h:i A') }}
                                </td>
                                <td>
                                    <div style="width: 100px; text-align: center;">
                                        @if($item->status === 'approved')
                                        <span class="badge badge-info">Approved</span>
                                        @elseif($item->status === 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                        @else
                                        <span class="badge badge-warning">Pending</span>
                                        <br>
                                        @endif

                                        <button class="btn btn-sm btn-info" data-toggle="modal"
                                            data-target="#detailsModal{{$item->id}}">
                                            Details
                                        </button>
                                        <form action="{{ route('attendance-requests.delete', $item) }}" method="post"
                                            class="d-inline-block">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this request?')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    {{ $item->duty_type ?? '--' }}
                                </td>
                                <td>
                                    {{ \Str::limit($item->notes ?? '--', 10) }}
                                </td>
                            </tr>
                            @empty

                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->

@foreach ($requests as $item)
<div class="modal fade" id="detailsModal{{$item->id}}" tabindex="-1" role="dialog"
    aria-labelledby="detailsModal{{$item->id}}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModal{{$item->id}}">Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @include('attendance_request.view', [
                'attendance_request' => $item,
                ])
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
    let table = new DataTable('table', {
        pageLength: 50
    });
</script>
@endsection