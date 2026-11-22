@extends('layouts.master')
@section('title', "Apply Fine")
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Apply Fine
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('fines.index') }}">Fine</a></li>
                            <li class="breadcrumb-item active">Apply</li>
                        </ul>
                    </div>
                </div>
            </div>
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <form action="{{ route('fines.apply') }}" method="POST">
                @csrf
                <div class="">
                    <div class="row">
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
                    <div>
                        <div class="row">
                            <div class="col-12">
                                <label for="">Reason:</label>
                                <input type="text" class="form-control" name="reason" value="{{ old('reason') }}">
                            </div>
                            <div class="mt-2 col-sm-6 col-md-3">
                                <label for="">Amount:</label>
                                <input type="number" class="form-control" name="amount" required value="{{ old('amount') }}">
                            </div>
                            <div class="mt-2 col-sm-6 col-md-2">
                                <label for="">Pay as:</label>
                                <select name="cut_off_as" id="" class="form-control">
                                    <option value="once" selected>At once</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                            <div class="mt-2 col-sm-6 col-md-3">
                                <label for="">Pay every month (if pay monthly):</label>
                                <input name="cut_off_amount" type="number" class="form-control" value="{{ old('cut_off_amount') }}">
                            </div>
                            <div class="mt-2 col-sm-4 col-md-2">
                                <label for="">Date:</label>
                                <input name="date" type="date" class="form-control" required value="{{ old('date') }}">
                            </div>
                        </div>
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
