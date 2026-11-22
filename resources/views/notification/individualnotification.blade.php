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
                            <h3 class="text-center">{{$notification->title}}</h3>
                            {{-- <p>{{$notification->message}}</p> --}}
                            <p>{!! $notification->message !!}</p>
                            @if ($notification->url)
                                <a href="{{ $notification->url }}" class="btn btn-primary">
                                    View Page   
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
    <!-- /Page Wrapper -->
