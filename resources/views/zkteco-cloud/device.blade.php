@extends('layouts.master')
@section('title', "Edit Device")
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">
                        {{ $device->name }}:
                        <small class="">
                            {{ $device->device_sn }}
                        </small>
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('zkteco-cloud') }}">ZkTeco Cloud Devices</a>
                        </li>
                        <li class="breadcrumb-item active">Device</li>
                    </ul>
                </div>
            </div>
        </div>
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div>
            <h4>
                Status:
            </h4>
            <hr>
            <div>
                <b>Total Users:</b> {{ $device->total_users ?: 0}},
                <b>Total Fingers:</b> {{ $device->total_fingers ?: 0}},
                <b>Total Att:</b> {{ $device->att_count ?: 0}},
                <b>Total IP:</b> {{ $device->ip ?: 0}},
                <b>Total FP v:</b> {{ $device->finger_v ?: 0}},
                <b>Total FC v:</b> {{ $device->face_v ?: 0}},
                <b>Total Face:</b> {{ $device->face_count ?: 0}},
                <b>Last Connect:</b> {{ carbon($device->last_connected)->diffForHumans() ?: 0}},
                <b>Command format for:</b> {{ $device->device_type }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mt-3">
                    <h4>
                        Actions:
                    </h4>
                    <hr>
                    @foreach(\App\Models\ZktecoCloudCommand::$supported_devices[$device->device_type] as $command)
                    <a href="{{ route('zkteco-cloud.device.sync', [$device->id, $command]) }}"
                        class="btn mb-2 btn-{{ str_contains($command, 'delete') ? 'danger' : 'info' }} btn-sm"
                        @if (str_contains($command, 'delete_'))
                            onclick="return confirm('Are you sure?')"
                        @endif
                        >
                        {{ ucfirst(str_replace('_', ' ', $command)) }}
                    </a>
                    @endforeach
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <b>Assign Departments:</b>
                <div class="mt-1">
                    <form action="{{ route('zkteco-cloud.device.assign-departments', $device->id) }}"
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
                                    @foreach ($item->departments ?? [] as $item)
                                    <label
                                        for="_{{ $device->id }}departments_department_{{ $item->id }}">
                                        <input
                                            id="_{{ $device->id }}departments_department_{{ $item->id }}"
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
                                <option value="" {{ $device->inoutstate === null ? 'selected' : ''
                                    }}>Same as Machine</option>
                                <option value="0" {{ $device->inoutstate === 0 ? 'selected' : ''
                                    }}>Forced In</option>
                                <option value="1" {{ $device->inoutstate === 1 ? 'selected' : ''
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
        <div class="mt-3">
            <h4>Commands:</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Type</th>
                        <th>Command</th>
                        <th>Return</th>
                        <th>Response</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($commands as $command)
                    <tr>
                        <td>{{ $command->id }}</td>
                        <td>{{ $command->type }}</td>
                        <td>
                            <p style="word-wrap: anywhere">
                                {{ \Str::limit($command->command, 100) }}
                                {{-- {{ $command->command }} --}}
                            </p>
                        </td>
                        <td>
                            <div style="width: 100px; word-wrap: anywhere">{{ \Str::limit($command->return_data ?: '--',
                                50) }}
                            </div>
                        </td>
                        <td>
                            <div style="width: 100px; word-wrap: anywhere">{{ \Str::limit($command->response ?: '--',
                                50) }}
                            </div>
                        </td>
                        <td>
                            @if ($command->reserved_at)
                            <span class="badge badge-info">
                                Success
                            </span>
                            {{-- @elseif ($command->reserved_at)
                            <span class="badge badge-warning">Running</span> --}}
                            @else
                            <span class="badge badge-warning">
                                Pending
                            </span>
                            @endif
                            {{-- @dump($command->return_data) --}}
                        </td>
                        <td>
                            @if(!$command->reserved_at)
                            <a href="{{ route('zkteco-cloud.device.delete-command', $command->id) }}"
                                class="btn btn-danger btn-sm">
                                <i class="fa fa-trash"></i>
                            </a>
                            @else
                            --
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No commands found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $commands->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
