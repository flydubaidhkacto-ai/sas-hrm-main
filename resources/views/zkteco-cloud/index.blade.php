@extends('layouts.master')
@section('title', "ZkTeco Devices")
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">ZkTeco Cloud Devices</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active">ZkTeco Cloud Devices</li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a class="btn add-btn" href="{{ route('zkteco-cloud.create') }}">
                            <i class="fa fa-plus"></i> Add Device
                        </a>
                    </div>
                </div>
            </div>
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <div>
                @forelse ($devices as $device)
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <b>
                                        {{ $device->name }}: {{ $device->device_sn }}
                                    </b>
                                    <span class="float-right">
                                        <a href="{{ route('zkteco-cloud.edit', $device->id) }}" class="btn btn-info btn-sm">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <button onclick="if(confirm('Are you sure you want to delete this device?')) { window.location.href = '{{ route('zkteco-cloud.delete', $device->id) }}' }" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </span>
                                </div>
                                <div class="col-md-12">
                                    <hr>
                                    <a href="{{ route('zkteco-cloud.device', $device->id) }}" class="btn btn-info btn-sm">
                                        Commands and Logs
                                        &nbsp;
                                        <i class="fa fa-external-link"></i>
                                    </a>
                                    {{-- <button class="btn btn-info btn-sm">
                                        Sync users
                                    </button>
                                    <button class="btn btn-info btn-sm">
                                        Sync fingerprints
                                    </button>
                                    <button class="btn btn-info btn-sm">
                                        Sync photos
                                    </button>
                                    <button class="btn btn-info btn-sm">
                                        Sync time
                                    </button> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info">
                        No devices found
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection




