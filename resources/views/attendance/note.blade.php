@extends('layouts.master')
@section('title', "Attendance Note")
@section('content')
{{-- message --}}
{!! Toastr::message() !!}
<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Attendance Note</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Attendance Note</li>
                    </ul>
                </div>
                <div class="col-auto">

                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Search Filter -->
        <form method="get" action="{{ route('attendance.note') }}" class="row filter-row mb-4">
            <div class="mb-2 col-sm-6 col-md-3">
                <label for="">Super Department</label>
                @if(
                !role_can('attendance')
                && role_can('attendance.self.super_department')
                )
                <input type="text" value="{{ user()->department?->super_department?->name }}" class="form-control"
                    readonly>
                <input type="hidden" name="super_department" value="{{ user()->department?->super_department_id }}"
                    class="form-control">
                <script>
                    window.selected_super_department = '{{ user()->department?->super_department_id }}';
                </script>
                @else
                <select name="super_department" id="" class="form-control super-department-select2">
                    @if(request()->super_department)
                    <option value="{{ request()->super_department }}" selected>
                        {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                    </option>
                    @endif
                </select>
                @endif
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
                        <input class="form-control floating datetimepicker" type="text"
                            value="{{ $date->format('d-m-Y') }}" name="date">
                    </div>
                    <label class="focus-label">Date</label>
                </div>
            </div>
            <div class="mb-2 col-sm-6 col-md-3">
                <button type="submit" class="btn btn-success btn-block"> Search </button>
            </div>
        </form>
        <!-- /Search Filter -->
        @include('layouts.alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped custom-table mb-0">
                        <thead>
                            <tr>
                                <th>
                                    Ref.
                                </th>
                                <th>
                                    Emp. ID
                                </th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Super Dpt</th>
                                <th>Note</th>
                                <th>Date</th>
                                <th>W. Hour</th>
                                {{-- <th>Action</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                            <tr data-toggle="collapse" data-target="#demo{{$item->id}}" class="accordion-toggle">
                                <td>
                                    {{ $item->id }}
                                </td>
                                <td>
                                    {{ $item->employee?->employee_id }}
                                </td>
                                <td>
                                    <h2 class="table-avatar">
                                        <a href="{{ route('employees.active.show', ['id' => $item->employee_id]) }}"
                                            class="avatar"><img alt="" src="{{
                                                    $item->employee?->picture_file ? " /uploaded_files/" .
                                                $item->employee?->picture_file :
                                            URL::to('assets/img/profiles/avatar-02.jpg')
                                            }}"></a>
                                        <a href="{{ route('employees.active.show', ['id' => $item->employee_id]) }}">
                                            @if ($item->employee)
                                            {{ $item->employee?->name }}
                                            @endif
                                        </a>
                                    </h2>
                                </td>
                                <td>
                                    {{ $item->employee?->department?->name }}
                                </td>
                                <td>
                                    {{ $item->employee?->department?->super_department->name }}
                                </td>
                                <td style="width: 350px">
                                    <form action="{{ route('attendance.note.save', $item) }}" method="post">
                                        @csrf
                                        <textarea name="notes" class="form-control"
                                            rows="3">{{ $item->notes }}</textarea>
                                        <button class="btn btn-sm btn-info mt-2">
                                            Save
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    {{carbon($item->date)->format('d F')}}
                                </td>
                                <td>
                                    {{ carbon($item->working_time ?? '00:00')->format('H:i') }}
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
@endsection