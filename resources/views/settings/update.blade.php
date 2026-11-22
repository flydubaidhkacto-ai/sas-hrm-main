
@extends('layouts.master')
@section('title', "Settings: Update system")
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            <h2>
                System Update:
            </h2>
            {!! Toastr::message() !!}
            <div class="d-none">
                @if (config('app.version') === $update)
                    <div class="alert alert-success">
                        <i class="fa fa-check"></i>
                        No update available
                    </div>
                    Cuurent Version: {{ config('app.version') }}
                @else
                    {{-- show update avaialble mess --}}
                    @include('layouts.alerts')
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        Update available. New Version is: {{ $update }}.
                        <span class="text-danger">
                            Current Version: {{ config('app.version') }}
                        </span>
                    </div>
                    @if(cache()->get('update_request'))
                        <div class="alert alert-success">
                            <i class="fa fa-check"></i>
                            Update Requested
                        </div>
                    @else
                        <a href="{{ route('update', ['update' => 1]) }}" class="btn btn-primary" >
                            <i class="fa fa-download"></i>
                            Request for Update
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
