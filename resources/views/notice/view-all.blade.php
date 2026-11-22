
@extends('layouts.master')
@section('title', "Notice Board")
@section('content')
    <style>
        .designation {
            color: unset; font-size: unset;
        }
    </style>
    <!-- Page Wrapper -->
    <div class="page-wrapper">

        <!-- Page Content -->
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Notice Board</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                            <li class="breadcrumb-item active">View All Notice</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            @include('notice.view-board', [
                'limit' => 500,
                'all' => true
            ])
        </div>
        <!-- /Page Content -->
    </div>
    @include('notice.partials.pdf-preview-modal')
    @include('notice.partials.notice-viewers-modal')
@endsection
