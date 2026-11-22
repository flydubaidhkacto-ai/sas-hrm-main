
@extends('layouts.master')
@section('title', "Attendance Entry")
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
                        <h3 class="page-title">Attendance Entry</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Attendance Entry</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            @include('layouts.alerts')
            @include('attendance.edit_form', [
                'att' => $att
            ])
        </div>
        <!-- /Page Content -->
    </div>
    <!-- /Page Wrapper -->
@endsection
@section('script')
    <script>

    </script>
@endsection
