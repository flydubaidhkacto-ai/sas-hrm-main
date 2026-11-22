@extends('layouts.master')
@section('title', "Edit Device")
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Edit Device</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('zkteco-cloud') }}">ZkTeco Cloud Devices</a>
                            </li>
                            <li class="breadcrumb-item active">Edit Device</li>
                        </ul>
                    </div>
                </div>
            </div>
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <div>
                <form action="{{ route('zkteco-cloud.update', $device->id) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Name of the key" required value="{{ $device->name }}">
                    </div>
                    <div class="form-group">
                        <label for="device_sn">Device SN:</label>
                        <input type="text" name="device_sn" id="device_sn" class="form-control" placeholder="Device SN" required value="{{ $device->device_sn }}" >
                        <small class="text-muted">
                            This is the serial number of the device. Used to identify the device.
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="device_type">Device Type for Commands:</label>
                        <select class="form-control" name="device_type" required>
                            @foreach(\App\Models\ZktecoCloudCommand::$supported_devices as $key => $val)
                                <option
                                    @if($key === $device->device_type) selected @endif
                                    value="{{ $key }}">{{ $key }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-info btn-sm mt-1">
                        Update
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection




