@extends('layouts.master')
@section('title', "Settings")
@section('content')
<style>
    #map {
        width: 100%;
        height: 350px;
    }
</style>
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        {!! Toastr::message() !!}
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Settings: Attendance Location
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">
                            Attendance Location Setting
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div>
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card mt-2">
                <div class="card-header">
                    <h4>
                        Restrict Check In/Out Location
                        <span class="float-right">
                            @if(!$restrict_attendance_location)
                            <span class="text-warning">
                                <i class="fa fa-dot-circle-o"></i> Off
                            </span>
                            @else
                            <span class="text-success">
                                <i class="fa fa-dot-circle-o"></i> On
                            </span>
                            @endif
                            <form action="{{ route('settings.attendance.update') }}" class="d-inline-block"
                                method="POST">
                                @csrf
                                <input type="hidden" name="key" value="restrict_attendance_location">
                                @if(!$restrict_attendance_location)
                                <input type="hidden" name="value" value="1">
                                <button class="btn btn-success btn-sm" type="submit">
                                    Turn On
                                </button>
                                @else
                                <button class="btn btn-warning btn-sm" type="submit">
                                    Turn Off
                                </button>
                                @endif
                            </form>
                        </span>
                    </h4>
                </div>
                <div class="card-body">
                    <p>
                        Enable it to restrict employee punch location
                    </p>
                </div>
            </div>
            <div class="mt-3">
                <h3>
                    Allowed Regions:

                    <a class="btn btn-info btn-sm float-right" href="{{ route('allowed-regions.create') }}">
                        <i class="fa fa-plus"></i>
                        Create New
                    </a>
                </h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>
                                #
                            </th>
                            <th>
                                Name
                            </th>
                            <th>
                                Latitude
                            </th>
                            <th>
                                Longitude
                            </th>
                            <th>
                                Radius
                            </th>
                            <th>
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($regions as $reg)
                        <tr>
                            <td>
                                {{$reg->id}}
                            </td>
                            <td>
                                {{$reg->name}}
                            </td>
                            <td>
                                {{$reg->latitude}}
                            </td>
                            <td>
                                {{$reg->longitude}}
                            </td>
                            <td>
                                {{$reg->radius_in_meters}}m
                            </td>
                            <td>
                                <a class="btn btn-sm btn-info"
                                    href="{{ route('allowed-regions.create', ['id' => $reg->id]) }}">
                                    <i class="fa fa-pen"></i>
                                </a>
                                <button
                                    onclick="if(confirm('Are you sure?')) {location = '{{ route('allowed-regions.delete', $reg->id) }}'}"
                                    class="btn btn-sm btn-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                Nothing found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $regions->withQueryString()->links() }}
        </div>
    </div>
</div>

@endsection