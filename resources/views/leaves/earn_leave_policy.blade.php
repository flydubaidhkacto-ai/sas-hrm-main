@extends('layouts.master')
@section('title', 'Earn Leave Policy')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            {!! Toastr::message() !!}
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Settings: Earn Leave Policy
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">
                                Earn Leave Policy
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div>
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="card mt-2">
                    <div class="card-header">
                        <h4>
                            One Leave after Each weekend.
                            <span class="float-right">
                                <form>
                                    @csrf
                                    <input type="hidden" name="key" value="enabled_one_earn_leave_after_each_weekend">
                                    <label class="switch">
                                        <input type="checkbox" name="enabled_one_earn_leave_after_each_weekend" onchange="this.form.submit()"
                                            {{ $enabled_one_earn_leave_after_each_weekend ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </form>
                            </span>
                        </h4>
                    </div>
                    <form method="get" class="card-body">
                        <input type="hidden" name="key" value="one_earn_leave_after_each_weekend">
                        @csrf
                        <p>
                            Upon enabling this option, the system will add one earn leave after each
                            <select name="one_earn_leave_after_each_weekend" id="">
                                @foreach (["sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday"] as $item)
                                    <option value="{{ $item }}" @if($one_earn_leave_after_each_weekend == $item) selected @endif>{{ $item }}</option>
                                @endforeach
                            </select>
                            .
                            <br>
                            No Attendance required. It will be added automatically to his balance categorized as "Earn Leave".
                                <option value="saturday">Saturday</option>
                            </select>
                            .
                            <br>
                            No Attendance required. It will be added automatically to his balance categorized as "Earn Leave".
                        </p>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-info">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
