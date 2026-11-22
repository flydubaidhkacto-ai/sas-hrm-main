@extends('layouts.master')
@section('title', "Create Reward")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper" onmousemove="calculateDuration()">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Add Reward
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('rewards') }}">Emp. Rewards</a></li>
                        <li class="breadcrumb-item active">Add</li>
                    </ul>
                </div>
            </div>
        </div>
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <form action="{{ route('rewards.store') }}" method="POST">
            @csrf
            <div class="">
                @if(
                    role_can('rewards')
                    || role_can('rewards.self.super_department')
                    || user()->admin_of_super_departments
                )
                <div class="row">
                    @if(
                        !role_can('rewards')
                        && role_can('rewards.self.super_department')
                    )
                    <div class="col-sm-6 col-md-3">
                        <label for="">Super Department</label>
                        <input type="hidden" name="super_department"
                            value="{{ user()->department?->super_department?->id }}">
                        <input type="text" readonly class="form-control"
                            value="{{ user()->department?->super_department?->name }}">
                        <script>
                            window.selected_super_department = '{{ user()->department?->super_department_id }}';
                        </script>
                    </div>
                    @else
                    <div class="col-sm-6 col-md-3">
                        <label for="">Super Department</label>
                        <select name="super_department" id="" class="form-control super-department-select2">
                            @if(request()->super_department)
                            <option value="{{ request()->super_department }}" selected>
                                {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    @endif
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
                        <label for="">Designation</label>
                        <select name="designation" id="" class="form-control designation-select2">
                            @if(request()->designation)
                            <option value="{{ request()->designation }}" selected>
                                {{ \App\Models\Designation::find(request()->designation)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="mb-2 mt-2">
                    <label for="">Select Employee:</label>
                    <select name="employee" id="" class="form-control employee-select2" required>
                        @if(request()->employee)
                        <option value="{{ request()->employee }}" selected>
                            {{ \App\Models\User::find(request()->employee)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                @else
                <select name="employee" id="" class="form-control d-none" required>
                    <option value="{{ user()->id }}" selected>
                        {{ user()->name }}
                    </option>
                </select>
                @endif
                <div class="mb-2">
                    <div class="row">
                        <div class="col-md-8">
                            <label for="">Reason:</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="">Date:</label>
                            <input type="text" name="date" class="form-control datetimepicker" required
                                value="{{ old('date') ?: date('d-m-Y') }}" style="max-width: 200px">
                        </div>
                    </div>
                </div>
                <div class="mb-2">
                    <label for="">Amount: (Tk)</label>
                    <input type="number" name="amount" class="form-control" value="{{ old('amount') }}"
                        style="width: 200px" step="any" required>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
@endsection
@section('script')
<script>

</script>
@endSection
