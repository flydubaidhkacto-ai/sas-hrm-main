@extends('layouts.app')
@section('title', "Too Many Requests")
@section('content')
    <div class="main-wrapper">
        <div class="error-box">
            <h1>419</h1>
            <h3><i class="fa fa-warning"></i> Oops! Too Many Requests are sent!</h3>
            <p>Please calm down and wait for a moment. Otherwise the system will CRASH!</p>
            <a href="{{ url('/') }}" class="btn btn-custom">
                I waited enough!
            </a>
        </div>
    </div>
@endsection
