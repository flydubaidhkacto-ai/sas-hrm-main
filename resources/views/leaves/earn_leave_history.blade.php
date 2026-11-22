@extends('layouts.master')
@section('title', "Earn Leave History")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Earn Leave History
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Earn Leave History</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="mt-2">
            {{-- <form method="get" action="{{
                    $type == 'pending' ? route('leaves.all') : ($type == 'approved' ? route('leaves.all.approve') : route('leaves.all.rejected'))
                    }}" class="row filter-row mb-4">
                @if(
                role_can('leave')
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
                @elseif(\App\Models\ApprovalProcess::canApproveEmployees('leave', user()))
                <div class="mb-2 mt-2 col-12">
                    <select name="employee" id="" class="form-control init-select2" onchange="loadBalance(this)">
                        <option value="">Select an employee</option>
                        <option value="{{ user()->id }}" @if(request()->employee == user()->id) selected @endif>{{
                            user()->name }}</option>
                        @foreach (\App\Models\ApprovalProcess::canApproveEmployees('leave', user())['employees'] as  $employee)
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
            </form> --}}
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <div class="mt-2">
                <div class="table-responsive">
                    <table class="table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>S.L</th>
                                <th>Emp. code</th>
                                <th>Emp. ID</th>
                                <th>Emp. Name</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Added</th>
                                <th>New Balance</th>
                                <th>Prev Balance</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->user?->id }}</td>
                                <td>{{ $item->user?->employee_id }}</td>
                                <td>{{ $item->user?->name }}</td>
                                <td>{{ $item->user?->department?->name }}</td>
                                <td>{{ $item->user?->designation?->name }}</td>
                                <td>{{ $item->added }}</td>
                                <td>{{ $item->new_balance }}</td>
                                <td>{{ $item->old_balance }}</td>
                                <td>{{ $item->note }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="mt-2">
                {{ $data->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')

@endsection
