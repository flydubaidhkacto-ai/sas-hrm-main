@extends('layouts.master')
@section('title', "ZkTeco Devices")
@section('content')
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">ZkTeco Devices</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">ZkTeco Devices</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a class="btn add-btn" onclick="this.nextElementSibling.classList.toggle('d-none');this.remove()"
                        href="#">
                        <i class="fa fa-plus"></i> Generate Api Key
                    </a>
                    <form action="{{ route('zkteco.create') }}" method="post" class="d-none">
                        @csrf
                        <input type="text" name="name" id="" class="form-control" placeholder="Name of the key"
                            required>
                        <button class="btn btn-info btn-sm mt-1">
                            <i class="fa fa-plus"></i> Generate Api Key
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <p>
            Use the Api key to securely connect the devices.
        </p>
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div>
            @foreach ($keys as $item)
            <div class="card card-body pb-0">
                <span>
                    <b>
                        {{ $item->name }}:
                    </b> {{ $item->key }}
                    <i>
                        <a href="#" data-copy="{{ $item->key }}" onclick="copyTextBtn(this)"
                            class="btn btn-link btn-sm ">
                            Copy
                        </a>
                    </i>
                </span>
                <form action="{{ route('zkteco.delete', $item) }}" method="post">
                    @csrf @method('delete')
                    <a class="btn btn-danger btn-sm float-right" href="#"
                        onclick="if(confirm('Are you sure?')){$(this).closest('form').submit()};">Delete</a>
                </form>
                <div class="mt-0">
                    <strong>Devices:</strong>
                    <br>
                    @if($item->devices->count() == 0)
                    <div class="mb-2">
                        No Devices are connected yet!
                    </div>
                    @endif
                    @php
                    $devices = $item->devices()
                    ->latest()
                    ->paginate(5, ['*'], 'devices_' . $item->id);
                    @endphp
                    @foreach ($item->devices as $client)
                    <div class="card mt-2">
                        <div class="card-body">
                            <span class="float-right">
                                <form action="{{ route('zkteco.delete-device', $client->id) }}" method="POST"
                                    class="d-inline-block">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-sm btn-danger">
                                        Delete
                                    </button>
                                </form>
                            </span>
                            <div class="row">
                                <div class="col-md-6">
                                    <b>Name: </b>
                                    {{ $client->name }}
                                    <br>
                                    <b>Model: </b>
                                    {{ $client->device_name }}
                                    <br>
                                    <b>Local IP: </b>
                                    {{ $client->ip }}
                                    <br>
                                    <b>Local Port: </b>
                                    {{ $client->port }}
                                    <br>
                                    <b>Users: </b>
                                    {{ $client->users_used }}/{{ $client->users_capacity }}
                                    <br>
                                    <b>Attendance: </b>
                                    {{ $client->attendance_used }}/{{ $client->attendance_capacity }}
                                    <br>
                                    <b>Fingers: </b>
                                    {{ $client->fingers_used }}/{{ $client->fingers_capacity }}
                                    <br>
                                    <span @if($client->updated_at->lessThan(now()->subHours(2)))
                                        style="color: gray; font-size: 12px;"
                                        @else
                                        style="font-size: 12px;" class="text-success"
                                        @endif
                                        >
                                        Last connected: {{ $client->updated_at->diffForHumans() }}
                                    </span>
                                </div>
                                @php
                                $assigned_departments = $client->departments()
                                ?->get()
                                ->pluck('id')
                                ->toArray() ?: [];
                                @endphp
                                <div class="col-md-6">
                                    <b>Assign Departments:</b>
                                    <div class="mt-1">
                                        <form action="{{ route('zkteco.assign-departments', $client->id) }}"
                                            method="post">
                                            @csrf
                                            <div style="max-height: 300px; overflow-y: auto;">
                                                @foreach ($departments as $item)
                                                <div>
                                                    <label>
                                                        <b>
                                                            {{ $item->name }}:
                                                        </b>
                                                        <span onclick="if($(this).parent().parent().find('input').prop('checked')) {
                                                                                $(this).parent().parent().find('input').prop('checked', false)
                                                                            }else {
                                                                                $(this).parent().parent().find('input').prop('checked', true)
                                                                            }" class="btn btn-link">
                                                            Select All
                                                        </span>
                                                    </label>
                                                    <div class="pl-3">
                                                        @foreach ($item->departments as $item)
                                                        <label
                                                            for="_{{ $client->id }}departments_department_{{ $item->id }}">
                                                            <input
                                                                id="_{{ $client->id }}departments_department_{{ $item->id }}"
                                                                type="checkbox" name="departments[]"
                                                                value="{{ $item->id }}" class="mr-2" {{
                                                                in_array($item->id, $assigned_departments) ? 'checked' :
                                                            '' }}>
                                                            {{ $item->name }}
                                                        </label>
                                                        <br>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            <div class="mt-1">
                                                <label for="">Punch State Overwrite</label>
                                                <select name="inoutstate" id="inoutstate" class="form-control">
                                                    <option value="" {{ $client->inoutstate === null ? 'selected' : ''
                                                        }}>Same as Machine</option>
                                                    <option value="0" {{ $client->inoutstate === 0 ? 'selected' : ''
                                                        }}>Forced In</option>
                                                    <option value="1" {{ $client->inoutstate === 1 ? 'selected' : ''
                                                        }}>Forced Out</option>
                                                </select>
                                            </div>
                                            <div class="mt-1">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    Save
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @if($devices->count())
                    <div class="mt-2">
                        {{ $devices->withQueryString()->links() }}
                    </div>
                    @endif
                    @php
                    $devices = null;
                    @endphp
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-2">
            {{ $keys->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
