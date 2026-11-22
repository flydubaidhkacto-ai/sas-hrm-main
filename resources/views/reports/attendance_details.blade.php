@extends('layouts.master')
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
                    <h3 class="page-title">Attendance Report</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Attendance Details</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="#" class="btn btn-primary">PDF</a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Content Starts -->
        <div class="row justify-content-center">
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h3><b>{{ \App\Models\User::employees()->count() }}</b></h3>
                        <p>Total Employees</p>
                    </div>
                </div>
            </div>
            @php

            @endphp
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-success">
                            {{-- <b>{{ \App\Models\AttendanceMonthly::where('total_late_check_in',
                                false)->where('month_name', $month)->count() }}</b> --}}
                            <b>0</b>
                        </h3>
                        <p>Present</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-danger"><b>12</b></h3>
                        <p>Absent</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h3><b>5</b></h3>
                        <p>Days Left</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Search Filter -->
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped custom-table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Started</th>
                                <th>Ended</th>
                                <th>Worked</th>
                                <th>Break</th>
                                <th>Overtime</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                            <tr data-toggle="collapse" data-target="#demo{{$item->id}}" class="accordion-toggle">
                                <td>
                                    {{carbon($item->date)->format('d F')}}
                                </td>
                                <td>
                                    {{ carbon($item->check_in)->format('h:i A') }}
                                </td>
                                <td>
                                    {{ carbon($item->check_out)->format('h:i A') }}
                                </td>
                                <td>
                                    {{ carbon($item->working_time)->format('H:i') }}
                                </td>
                                <td>0</td>
                                <td>0</td>
                                <td>
                                    <button class="btn btn-warning btn-sm btn-xs">
                                        See In-Out
                                    </button>
                                </td>
                            </tr>
                            <tr class="accordian-body collapse" id="demo{{$item->id}}">
                                <td colspan="12" class="hiddenRow">
                                    <div>
                                        <table class="table table-striped custom-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Time</th>
                                                    <th>In/Out</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $used = $item->used_in_out_records->map(function($inout){
                                                return $inout->id;
                                                })->toArray();
                                                @endphp
                                                @foreach ($item->used_in_out_records() as $inout)
                                                <tr>
                                                    <td>
                                                        {{ carbon($inout->date_time)->format('h:i A') }}
                                                    </td>
                                                    <td>
                                                        {{ $inout->status == 1 ? 'In' : ($inout->status == 0 ? 'Out' :
                                                        'Invalid') }}
                                                    </td>
                                                    <td>
                                                        @if(in_array($inout->id, $used))
                                                        <span class="badge badge-success">
                                                            Used
                                                        </span>
                                                        @else
                                                        <span class="badge badge-danger">
                                                            Not Used
                                                        </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /Content End -->
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->

@endsection
