@extends('layouts.master')
@section('title', "View Leave")
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper" onmousemove="calculateDuration()">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            View Leave
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('leaves.history') }}">Leaves</a></li>
                            <li class="breadcrumb-item active">View</li>
                        </ul>
                    </div>
                </div>
            </div>
            @include('leaves.show', [
                'leave' => $leave
            ])
        </div>
    </div>
@endsection
