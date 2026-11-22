@extends('layouts.master')
@section('title', 'Overtime Schedule')
@section('content')
{!! Toastr::message() !!}
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-9">
                    <h3 class="page-title">
                        General Overtime Schedule:
                        <span class="text-info">{{ $date->format('d M, Y') }}</span>
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">
                            General Overtime Schedule
                        </li>
                    </ul>
                </div>
                <div class="col-3">
                    @if(role_can('superadmin'))
                    <div class="text-right mb-3">
                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#regenerateModal">
                            <i class="fa fa-refresh"></i>
                            Regenerate
                        </button>
                    </div>
                    @endif 
                    <form action="{{ route('attendance.overtime.schedule.update-default-limit') }}" method="post" class="d-block" style="width: 100%">
                        @csrf
                        <b>Default Overtime Limit:</b>
                        <select name="limit" id="" onchange="$(this).next().next().removeClass('d-none')">
                            <option @if ($limit==0) selected @endif value="">None</option>
                            <option @if ($limit==1) selected @endif value="1">1 Hour</option>
                            <option @if ($limit==2) selected @endif value="2">2 Hours</option>
                            <option @if ($limit==3) selected @endif value="3">3 Hours</option>
                            <option @if ($limit==4) selected @endif value="4">4 Hours</option>
                            <option @if ($limit==5) selected @endif value="5">5 Hours</option>
                            <option @if ($limit==6) selected @endif value="6">6 Hours</option>
                            <option @if ($limit==7) selected @endif value="7">7 Hours</option>
                            <option @if ($limit==9) selected @endif value="9">9 Hours</option>
                            <option @if ($limit==10) selected @endif value="10">10 Hours</option>
                            <option @if ($limit==11) selected @endif value="11">11 Hours</option>
                            <option @if ($limit==12) selected @endif value="12">12 Hours</option>
                        </select>
                        <br>
                        <button class="btn btn-sm btn-info d-none">
                            Save
                        </button>
                    </form>
                </div>
            </div>
            <div class="mt-1 text-right">
                <form method="POST" style="display: inline-block;" class="text-left">
                    @csrf
                    <b style="position: relative; top: -10px">
                        Everyone can Overtime:
                    </b>
                    &nbsp;
                    <label class="switch">
                        <input type="checkbox" name="all_overtime"
                            onchange="this.form.setAttribute('action', this.checked ? '?val=1' : '?val=0');this.form.submit()"
                            @if ($all_overtime) checked @endif>
                        <span class="slider round"></span>
                    </label>

                </form>
            </div>
        </div>

        @include('layouts.alerts')
        @if ($all_overtime)
        <h3 class="text-center">
            Everyone can do Overtime. Please turn it off to schedule according to date and employee
        </h3>
        @else
        <div>
            <form action="{{ route('attendance.overtime.schedule') }}" method="GET">
                <h4>Assign/Search overtime Datewise:</h4>
                <div class="row">
                    <div class="mb-2 col-sm-6 col-md-3">
                        <label for="">Super Department</label>
                        <select name="super_department" id="" class="form-control super-department-select2">
                            @if (request()->super_department)
                            <option value="{{ request()->super_department }}" selected>
                                {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-2 col-sm-6 col-md-3">
                        <label for="">Department</label>
                        <select name="department" id="" class="form-control department-select2">
                            @if (request()->department)
                            <option value="{{ request()->department }}" selected>
                                {{ \App\Models\Department::find(request()->department)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-2 col-sm-6 col-md-3">
                        <label for="">Designation</label>
                        <select name="designation" id="" class="form-control designation-select2">
                            @if (request()->designation)
                            <option value="{{ request()->designation }}" selected>
                                {{ \App\Models\Designation::find(request()->designation)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-2 col-sm-6 col-md-3">
                        <label for="">Employee</label>
                        <select name="employee" id="" class="form-control employee-select2">
                            @if (request()->employee)
                            <option value="{{ request()->employee }}" selected>
                                {{ \App\Models\User::find(request()->employee)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-2 col-md-3">
                        <label for="">Overtime Date:</label>
                        <input type="text" name="date" class="form-control datetimepicker" required
                            value="{{ $date->format('d-m-Y') }}">
                    </div>
                    <div class="mb-2 col-sm-6 col-md-3">
                        <label for=""></label>
                        <br>
                        <button class="mt-3 btn btn-primary">
                            Get Employees
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="mt-2">
            <div class="table-responsive">
                <table class="table table-striped custom-table">
                    <thead>
                        <tr>
                            <th>
                                Code
                            </th>
                            <th>
                                Emp. ID
                            </th>
                            <th>Name</th>
                            <th>
                                Overtime
                            </th>
                            <th>
                                Overtime Limit
                            </th>
                            <th>Department</th>
                            <th>Sup. Department</th>
                            <th>Designation</th>
                            <th>Mobile</th>
                            <th>Shift</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $items)
                        <tr>
                            <td>
                                {{ $items->id }}
                            </td>
                            <td>
                                {{ $items->employee_id }}
                            </td>
                            <td>
                                <h2 class="table-avatar">
                                    <a href="{{ route('employees.active.show', $items) }}">
                                        {{ $items->name }}
                                    </a>
                                </h2>
                            </td>
                            <td>
                                @if (!in_array($items->id, $scheduled_users))
                                {{-- <span class="badge badge-warning">Disabled</span> --}}
                                <form action="{{ route('attendance.overtime.schedule.save', $items->id) }}"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" value="{{ $date->format('Y-m-d') }}" name="date" />
                                    <label class="switch">
                                        <input type="checkbox" onchange="this.form.submit()">
                                        <span class="slider round"></span>
                                    </label>
                                    {{-- <button class="btn btn-sm btn-info">
                                        Enable
                                    </button> --}}
                                </form>
                                @else
                                {{-- <span class="badge badge-success">Enabled</span> --}}
                                <form action="{{ route('attendance.overtime.schedule.save', $items->id) }}"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" value="{{ $date->format('Y-m-d') }}" name="date" />
                                    <label class="switch">
                                        <input type="checkbox" checked onchange="this.form.submit()">
                                        <span class="slider round"></span>
                                    </label>
                                    {{-- <button class="btn btn-sm btn-danger">
                                        Disable
                                    </button> --}}
                                </form>
                                @endif
                            </td>
                            <td>
                                <form
                                    action="{{ route('attendance.overtime.schedule.update-limit', [$items->id, carbon($date)->format('Y-m-d')]) }}"
                                    method="POST">
                                    @php
                                    // max_overtime_hours
                                    $l =
                                    \App\Models\OvertimeSchedule::whereDate('date', $date)
                                    ->where('user_id', $items->id)
                                    ->first()?->max_overtime_hours ?? 0;
                                    $l = $l ?: $limit;
                                    @endphp
                                    @csrf
                                    <select name="limit" id="" onchange="$(this).next().next().removeClass('d-none')">
                                        <option @if ($l==0) selected @endif value="">None</option>
                                        <option @if ($l==1) selected @endif value="1">1 Hour</option>
                                        <option @if ($l==2) selected @endif value="2">2 Hours</option>
                                        <option @if ($l==3) selected @endif value="3">3 Hours</option>
                                        <option @if ($l==4) selected @endif value="4">4 Hours</option>
                                        <option @if ($l==5) selected @endif value="5">5 Hours</option>
                                        <option @if ($l==6) selected @endif value="6">6 Hours</option>
                                        <option @if ($l==7) selected @endif value="7">7 Hours</option>
                                        <option @if ($l==8) selected @endif value="8">8 Hours</option>
                                        <option @if ($l==9) selected @endif value="9">9 Hours</option>
                                        <option @if ($l==10) selected @endif value="10">10 Hours</option>
                                        <option @if ($l==11) selected @endif value="11">11 Hours</option>
                                        <option @if ($l==12) selected @endif value="12">12 Hours</option>
                                    </select>
                                    <br>
                                    <button class="btn btn-sm btn-info d-none mt-1">
                                        Save
                                    </button>
                                </form>
                            </td>
                            <td>{{ $items->department?->name }}</td>
                            <td>{{ $items->department?->super_department?->name }}</td>
                            <td>{{ $items->designation?->name }}</td>
                            <td>{{ $items->phone_number }}</td>
                            <td>
                                @if ($items->shift)
                                {{ $items->shift->name }} -
                                {{ carbon($items->shift->start)->format('h:i A') }} -
                                {{ carbon($items->shift->end)->format('h:i A') }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-2">
                    {{ $employees->withQueryString()->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('table', {
            pageLength: 50
        });
</script>
@endsection
@section('script')
<script>
    function update_overtime(btn) {
            const date = "{{ $date->format('Y-m-d') }}"
        }
</script>
@endsection