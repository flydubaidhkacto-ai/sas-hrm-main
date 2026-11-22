@extends('layouts.master')
@section('title', "Student list")
@section('content')
    @include('employees.terminate-modal')
    @include('employees.activate-modal')
    <div class="page-wrapper">
        <div class="content container-fluid">

            {{-- student tab --}}
            {{-- <div class="mb-3">
                @include('tabs.student')
            </div> --}}

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Students</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Students</li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        @if(
                            ((get_software_license()['employee_limit'] ?? 0) - \App\Models\User::user_count()) > 0
                        )
                            <a href="{{ route('students.create') }}" class="btn add-btn">
                                <i class="fa fa-plus"></i>
                                Add Student
                            </a>
                            <br>
                        @else
                            <span class="text-danger">
                                User limit reached!
                            </span>
                            <br>
                        @endif
                        <span class="text-muted">
                            User&nbsp;used/limit: {{ \App\Models\User::user_count() }}/{{ get_software_license()['employee_limit'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>
			<!-- /Page Header -->

            <!-- Search Filter -->
            <form action="{{ route('students.index') }}" method="get" class="mb-3">
                <div class="row filter-row">
                    <div class="col-sm-6 col-md-3">
                        <label for="">Department</label>
                        <select name="department" id="" class="form-control department-select2">
                            @if(request()->department)
                                <option value="{{ request()->department }}" selected>
                                    {{ \App\Models\Department::find(request()->department)?->name }}
                                </option>
                            @endif
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label for="">Class</label>
                        <select name="class_id" id="" class="form-control init-select2">
                            <option value="">All</option>
                            @foreach (\App\Models\StudentClass::all() as $class)
                                <option value="{{ $class->id }}" @if(request()->class_id == $class->id) selected @endif>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label for="">Student</label>
                        <select name="student" id="" class="form-control student-select2">
                            @if(request()->student)
                                <option value="{{ request()->student }}" selected>
                                    {{ \App\Models\User::find(request()->student)?->name }}
                                </option>
                            @endif
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <button style="margin-top: 25px;" type="submit" class="btn btn-success btn-block"> Search </button>
                    </div>
                </div>
            </form>
            <!-- Search Filter -->
            {{-- message --}}
            {!! Toastr::message() !!}
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    {{-- <div class="exports">
                        @include('layouts.exports')
                    </div> --}}
                    <div class="table-responsive">
                        <table
                            @if($for_export ?? false)
                                style="width: 100%"
                            @else
                                class="table table-striped custom-table"
                            @endif
                        >
                            <thead>
                                <tr>
                                    <th style="width: 3px">
                                        S.L
                                    </th>
                                    <th style="width: 16px; height: 20px;">
                                        Code
                                    </th>
                                    <th>
                                        St. ID
                                    </th>
                                    <th>Name</th>
                                    <th style="width: 30px;">
                                        Department
                                    </th>
                                    {{-- <th>Designation</th> --}}
                                    <th style="width: 30px;">Class</th>
                                    <th>Roll</th>
                                    <th>Registration</th>
                                    <th>Mobile</th>
                                    <th>Shift</th>
                                    <th>
                                        Adm. Date
                                    </th>
                                    <th>ID Card </th>
                                    <th class="text-right no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 1
                                @endphp
                                @foreach ($students as $item )
                                    <tr>
                                        <td style="width: 5px">
                                            {{ $i++ }}
                                        </td>
                                        <td style="width: 20px; height: 20px">
                                            {{$item->id}}
                                        </td>
                                        <td>
                                            {{ $item->student_id }}
                                        </td>
                                        <td>
                                            @if($for_export ?? false)
                                                {{ $item->name . ' ' }}
                                                @if ($item->zk_device_fingerprint_json)
                                                    <i class="fa-solid fa-fingerprint"></i>
                                                @endif
                                            @else
                                                <h2 class="table-avatar">
                                                    <a href="{{ route('students.show', $item->id) }}">
                                                        {{ $item->name }}
                                                        @php
                                                            $count = $item->zk_fingerprints()->count();
                                                        @endphp
                                                        @if($count)
                                                            (<i class="fa-solid fa-fingerprint"></i> {{ $count }})
                                                        @endif
                                                        @if(!$item->is_active)
                                                            <br />
                                                            <span class="text-muted">Terminated since {{ carbon($item->updated_at)->format('d M, Y h:i A') }}</span>
                                                        @endif
                                                    </a>
                                                </h2>
                                                @if($item->schedule_termination_at)
                                                    <br>
                                                    <span class="badge badge-warning">
                                                        <i class="fa fa-clock-o"></i> Scheduled for termination at
                                                        {{ carbon($item->schedule_termination_at)->format('d M, Y h:i A') }}
                                                    </span>
                                                @endif
                                                @if($item->schedule_re_activation_at)
                                                    <br>
                                                    <span class="badge badge-success">
                                                        <i class="fa fa-clock-o"></i> Scheduled for re-activation at
                                                        {{ carbon($item->schedule_re_activation_at)->format('d M, Y h:i A') }}
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>{{ $item->department?->name }}</td>
                                        {{-- <td>{{ $item->designation?->name }}</td> --}}
                                        <td>{{ $item->studentClass?->name }}</td>
                                        <td>{{ $item->roll }}</td>
                                        <td>{{ $item->registration }}</td>
                                        <td>{{ $item->phone_number }}</td>
                                        <td>
                                            {{-- @if($item->shift)
                                                {{ $item->shift->name }}
                                            @endif --}}
                                            @foreach ($item->shifts as $shift)
                                                <span class="badge badge-info">
                                                    {{ $shift->shift?->name }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>
                                            {{ carbon($item->join_date)->format('d M, Y') }}
                                        </td>
                                        <td>
                                            @if($item->zk_device_card)
                                                {{ $item->zk_device_card }}
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <div class="dropdown dropdown-action">
                                                <a href="#" class="action-icon dropdown-toggle" id="dropdown_toggle_{{ $item->id }}" dusk="dropdown_toggle_{{ $item->id }}" data-toggle="dropdown">
                                                    <i class="material-icons">more_vert</i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" href="{{ route('students.show', $item->id) }}">
                                                        <i class="fa fa-eye" aria-hidden="true"></i> View
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('students.edit', $item->id) }}">
                                                        <i class="fa fa-pencil m-r-5"></i> Edit
                                                    </a>
                                                    <a class="dropdown-item" id="delete_student_{{ $item->id }}" dusk="delete_student_{{ $item->id }}" href="{{ route('students.delete', $item->id) }}" onclick="return confirm('Are you sure you want to delete this employee?')">
                                                        <i class="fa fa-trash m-r-5"></i> Delete
                                                    </a>
                                                </div>

                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Content -->

    </div>
    <!-- /Page Wrapper -->
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        let table = new DataTable('table', {
            pageLength: 50
        });;
    </script>
@endsection
@section('script')
    <script>
        $('.init-select2-shift').select2({}).on('change', function () {
            $(this).next().next().next().css('display', 'block')
        })
    </script>
@endsection
