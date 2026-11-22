@extends('layouts.master')
@section('title', "Student Attendance Report")
@section('content')
{{-- message --}}
{!! Toastr::message() !!}
<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        {{-- student tab --}}
        {{-- <div class="mb-3">
            @include('tabs.student')
        </div> --}}

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Student Attendance Report </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Student Attendance Report</li>
                    </ul>
                </div>
                <div class="col-auto">

                </div>
            </div>
        </div>
        <!-- /Page Header -->
        <!-- Search Filter -->
        <form method="get" action="{{ route('attendance.students.show') }}" class="row filter-row">
            @if (role_can('student_attendance'))
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
                <label for="">Class</label>
                <select name="class" id="" class="form-control class-select2">
                    <option value="">All</option>
                    @foreach ($classes as $item)
                    <option value="{{ $item }}" {{ request()->class == $item ? 'selected' : '' }}>
                        {{ $item }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-2 col-sm-6 col-md-3">
                <label for="">Students</label>
                <select name="student" id="" class="form-control student-select2">
                    @if(request()->student)
                    <option value="{{ request()->student }}" selected>
                        {{ \App\Models\User::find(request()->student)?->name }}
                    </option>
                    @endif
                </select>
            </div>
            @endif
            <div class="mb-2 col-sm-6 col-md-3">
                <div class="form-group ">
                    <label class="focus-label">From</label>
                    <div class="cal-icon">
                        <input class="form-control datetimepicker" type="text" value="{{ $from->format('d-m-Y') }}"
                            name="start_date">
                    </div>
                </div>
            </div>
            <div class="mb-2 col-sm-6 col-md-3">
                <div class="form-group">
                    <label class="focus-label">To</label>
                    <div class="cal-icon">
                        <input class="form-control datetimepicker" type="text" value="{{ $to->format('d-m-Y') }}"
                            name="end_date">
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <button style="margin-top: 25px;" type="submit" class="btn btn-success"> Search </button>
            </div>
        </form>

        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped custom-table">
                        <thead>
                            <tr>
                                <th>
                                    S.L
                                </th>
                                <th>Code</th>
                                <th>
                                    Emp. ID
                                </th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Class</th>
                                <th>Shift</th>
                                <th>Date</th>
                                <th>In Time</th>
                                <th>Out Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                            <tr>
                                <td>
                                    {{ $loop->iteration }}
                                </td>
                                <td>
                                    {{ $item->employee?->id }}
                                </td>
                                <td>
                                    {{ $item->employee?->employee_id }}
                                </td>
                                <td>
                                    @if($for_export ?? false)
                                    {{ $item->employee?->name }}
                                    @else
                                    <h2 class="table-avatar">
                                        <a href="{{ route('students.show', $item->employee_id) }}">
                                            @if ($item->employee)
                                            {{ $item->employee?->name }}
                                            @endif
                                        </a>
                                    </h2>
                                    @endif
                                </td>
                                <td>
                                    {{ $item->employee?->department?->name }}
                                </td>
                                <td>
                                    {{ $item->employee?->class }}
                                </td>
                                <td>
                                    {{ $item->shift?->name }}
                                </td>
                                <td>
                                    {{carbon($item->date)->format('d F')}}

                                    @if($item->added_manually_by)
                                    @php
                                    $user = \App\Models\User::find($item->added_manually_by);
                                    @endphp
                                    @if($user)
                                    <br>
                                    <i class="small">Added manually By: {{ $user->name }}</i>
                                    @endif
                                    @endif
                                </td>

                                @php
                                $all_checks = $item->used_in_out_records()
                                    ->orderBy('date_time', 'asc')
                                    ->get();
                                @endphp
                                <td>
                                    @if($item->check_in)
                                        {{ carbon($item->check_in ?? '00:00')->format('h:i A') }}
                                    @else
                                    --:--
                                    @endif
                                </td>
                                <td>
                                    @if($item->check_out)
                                        {{ carbon($item->check_out ?? '00:00')->format('h:i A') }}
                                    @else
                                    --:--
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm btn-xs" data-toggle="collapse"
                                        data-target="#demo{{$item->id}}" class="accordion-toggle">
                                        See In-Out
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-xs"
                                        onclick="if(confirm('Are you sure? This will delete all in-out records of this attendance.')) $('#deleteForm{{$item->id}}').submit()">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    <form action="{{ route('attendance.destroy', $item) }}" method="post"
                                        id="deleteForm{{$item->id}}">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="attendance_id" value="{{ $item->id }}">
                                    </form>
                                </td>
                            </tr>
                            <tr class="accordian-body collapse" id="demo{{$item->id}}">
                                <td colspan="10" class="hiddenRow">
                                    <div>
                                        <table class="table table-striped custom-table m-auto mb-0"
                                            style="max-width: 1600px">
                                            <thead>
                                                <tr>
                                                    <th>S.L</th>
                                                    <th>In</th>
                                                    <th>Out</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $intervals = [];
                                                for ($i = 0; $i < $all_checks->count() - 1; $i += 2) {
                                                    $intervals[] = [
                                                        'start' => $all_checks[$i]->date_time,
                                                        'end'   => $all_checks[$i + 1]->date_time,
                                                    ];
                                                }
                                                @endphp
                                                @foreach ($intervals as $key => $value)
                                                    @php
                                                    $in = $value['start'];
                                                    $out = $value['end'];
                                                    @endphp
                                                <tr>
                                                    <td>
                                                        {{ $key + 1 }}
                                                    </td>
                                                    <td class="in">
                                                        @if($in)
                                                        {{ ($in)->format('h:i:s A') }}
                                                        @else
                                                        --
                                                        @endif
                                                    </td>
                                                    <td class="out">
                                                        @if($out)
                                                        {{ ($out)->format('h:i:s A') }}
                                                        @else
                                                        --
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                                <td colspan="1">
                                    @if($item->system_notes)
                                    System Notes:
                                    <br>
                                    {!! $item->system_notes !!}
                                    @endif
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
        <!-- /Content End -->
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->

{{--
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script> --}}
<script>
    // let table = new DataTable('table', {
        //     pageLength: 50
        // });
</script>
@endsection
