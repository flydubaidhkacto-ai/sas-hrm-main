@extends('layouts.master')
@section('title', "Notification")
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper" onmousemove="calculateDuration()" onkeyup="calculateDuration()">
        <!-- Page Content -->
        <div class="content container-fluid" >
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Notification</h3>
                        <a href="/notification/markalldeleted/{{ user()->id }}" class="btn btn-danger btn-sm float-right">Mark All Deleted</a>
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
                @if($notifications->count() > 0)
                    @foreach ($notifications as $notification)
                    <div class="col-md-3"></div>
                    <div class="col-md-6 mx-auto">
                        <div class="card dash-widget">
                            <div class="card-body">
                                <button type="button" class="close position-absolute text-danger" style="right: 15px; top: 10px;">
                                    <span aria-hidden="true" onclick="location = '/notification/vanish/{{ $notification->id }}/{{ user()->id }}'">&times;</span>
                                </button>
                                <h3 class="text-center">{{$notification->title}}</h3>
                                <p>{!! $notification->message !!}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3"></div>
                    @endforeach
                @else
                    <div class="col-md-6 mx-auto">
                        <div class="card">
                            <div class="card-body text-center">
                                <h4>No notifications available</h4>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="d-flex justify-content-center">
                {!! $notifications->links() !!}
            </div>
        </div>
    </div>
@endsection
    <!-- /Page Wrapper -->
