@extends('layouts.master')
@section('title', 'Shift Scheduling')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Shift Scheduling: Create
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('scheduling.shifts.index') }}">
                                    Shift Scheduling
                                </a>
                            </li>
                            <li class="breadcrumb-item active">Create </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="shift-scheduling-create" employees="{{ $employees }}" dates="{{ json_encode($dates) }}"></div>
        </div>
    </div>
@endsection



