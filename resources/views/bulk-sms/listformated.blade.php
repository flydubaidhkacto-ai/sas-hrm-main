@extends('layouts.master')
@section('title', "SMS List Formated")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        SMS: Format
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item active">Formated SMS</li>
                    </ul>
                </div>
            </div>
        </div>
        {!! Toastr::message() !!}
        <div class="row">

            <div class="card" style="width: 100%;">
                <ul class="list-group list-group-flush">
                    <a href="{{route('finemessage.formate')}}">
                        <li class="list-group-item">Fine message</li>
                    </a>
                    <a href="{{route('warningmessage.formate')}}">
                        <li class="list-group-item">Late warning message</li>
                    </a>
                    <a href="{{route('leavmessage.formate')}}">
                        <li class="list-group-item">Leave application decision message</li>
                    </a>
                    <a href="{{route('loan_application.formate')}}">
                        <li class="list-group-item">Loan message</li>
                    </a>
                    <a href="{{route('promotion.formate')}}">
                        <li class="list-group-item">Promotion message</li>
                    </a>
                    <a href="{{route('rewards.formate')}}">
                        <li class="list-group-item">Reward message</li>
                    </a>
                    <a href="{{route('student_check_in.formate')}}">
                        <li class="list-group-item">Student Check-In message</li>
                    </a>
                    <a href="{{route('student_check_out.formate')}}">
                        <li class="list-group-item">Student Check-Out message</li>
                    </a>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
