@extends('layouts.master')
@section('title', "Notification Edit")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper" onmousemove="calculateDuration()" onkeyup="calculateDuration()">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Notification Edit</h3>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        {{-- message --}}
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        @php
        $today_date = today()->format('d-m-Y');
        @endphp
        <div class="row ">
            <div class="col-md-6 mx-auto">
                <div class="card dash-widget">
                    <div class="card-body mx-auto">
                        <form action="{{ route('notification.update') }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="id" id="e_id" value="{{$notification->id}}">
                            <div class="form-group">
                                <label>Notification Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title_edit" name="title"
                                    value="{{$notification->title}}">
                            </div>

                            <div class="form-group">
                                <label>Notification Message<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="message_edit" name="message"
                                    value="{{$notification->message}}">
                            </div>

                            <div class="form-group">
                                <label>Notify Time <span class="text-danger">*</span></label>
                                <input class="form-control" type="datetime-local" id="dateTimepicker"
                                    value="{{$notification->time_of_notify}}" name="notifyTime">
                            </div>

                            <div class="form-group">
                                <label>
                                    <h3>Send Particular User</h3>
                                </label>
                                <div class="row filter-row">
                                    <div class="col-12 mt-1">
                                        <label for="">Super Department :</label> <br>
                                        @if (role_can('notifications') || user()->admin_of_super_departments)
                                        <select name="super_department" id=""
                                            class="form-control super-department-select2">
                                            @if (request()->super_department)
                                            <option value="{{ request()->super_department }}" selected>
                                                {{ \App\Models\SuperDepartment::find(request()->super_department)?->name
                                                }}
                                            </option>
                                            @endif
                                        </select>
                                        @else
                                        <input type="text" readonly class="form-control"
                                            value="{{ user()->department?->super_department?->name }}">
                                        <input type="hidden" name="super_department"
                                            value="{{ user()->department?->super_department?->id }}">
                                        <script>
                                            window.selected_super_department = {{ user()->department?->super_department?->id }};
                                        </script>
                                        @endif
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label class="mr-5" for="">Department:</label>
                                        <select name="department" id="" class="form-control department-select2">
                                            @if (request()->department)
                                            <option value="{{ request()->department }}" selected>
                                                {{ \App\Models\Department::find(request()->department)?->name }}
                                            </option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label for="">Designation</label>
                                        <select name="designation" id="" class="form-control designation-select2">
                                            @if(request()->designation)
                                            <option value="{{ request()->designation }}" selected>
                                                {{ \App\Models\Designation::find(request()->designation)?->name }}
                                            </option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label class="mr-5" for="">Employee :</label>
                                        <select name="employee" id="employee" class="form-control employee-select2">
                                            <option value="{{$notification->user}}" selected>
                                                {{$notification->user ? $notification->user->name :
                                                \App\Models\User::find(request()->employee)?->name }}
                                            </option>
                                        </select>
                                    </div>

                                </div>

                            </div>

                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
