@extends('layouts.master')
@section('title', "Api Keys (Legacy)")
@section('content')
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Api Keys (Legacy)</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Api keys (Legacy)</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a class="btn add-btn" onclick="this.nextElementSibling.classList.toggle('d-none');this.remove()"
                        href="#">
                        <i class="fa fa-plus"></i> Generate New
                    </a>
                    <form action="{{ route('api-keys.create') }}" method="post" class="d-none">
                        @csrf
                        <input type="text" name="name" id="" class="form-control" placeholder="Name of the key"
                            required>
                        <button class="btn btn-info btn-sm mt-1">
                            <i class="fa fa-plus"></i> Generate
                        </button>
                    </form>
                </div>
            </div>
        </div>
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div>
            @foreach ($keys as $item)
            <div class="card card-body pb-0">
                <b>
                    {{ $item->name }}:
                </b>
                {{ $item->key }}
                <br>
                {{-- <i style="color: gray"> Expires at:
                    {{ $item->expire_at->format('d M, Y') }}
                </i> --}}
                <form action="{{ route('api-keys.delete', $item) }}" method="post">
                    @csrf @method('delete')
                    <button type="submit" class="btn btn-danger btn-sm float-right">Delete</button>
                </form>
                <div class="mt-2">
                    <strong>Clients:</strong>
                    <br>
                    @if($item->clients->count() == 0)
                    <div class="mb-2">
                        No clients connected yet!
                    </div>
                    @endif
                    @php
                    $clients = $item->clients()
                    ->latest()
                    ->paginate(5, ['*'], 'clients_' . $item->id);
                    @endphp
                    @foreach ($item->clients as $client)
                    <div class="card mt-2">
                        <div class="card-body">
                            <span class="float-right">
                                @if($client->is_blocked)
                                <span class="badge badge-danger">Disabled</span>
                                <form action="{{ route('api-keys.block-client', $client->id) }}" method="POST"
                                    class="d-inline-block">
                                    @csrf
                                    <button class="btn btn-sm btn-info">
                                        Unblock
                                    </button>
                                </form>
                                @else
                                {{-- <span class="badge badge-success">Enabled</span> --}}
                                <form action="{{ route('api-keys.block-client', $client->id) }}" method="POST"
                                    class="d-inline-block">
                                    @csrf
                                    <button class="btn btn-sm btn-danger">
                                        Delete
                                    </button>
                                </form>
                                @endif
                            </span>
                            <div class="row">
                                <div class="col-md-6">
                                    <b>Name: </b>
                                    {{ $client->device_name }}
                                    <br>
                                    <b>IP: </b>
                                    {{ $client->device_ip }}
                                    <br>
                                    <b>Local IP: </b>
                                    {{ $client->device_local_ip }}
                                    <br>
                                    <b>OS: </b>
                                    {{ $client->device_os ?? 'N/A' }}
                                    <br>
                                    <b>ID: </b>
                                    {{ $client->device_mac }}
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
                                ->get()
                                ->map(fn($item) => $item->department_id)
                                ->toArray();
                                @endphp
                                <div class="col-md-6">
                                    <b>Assign Departmets:</b>
                                    <div class="mt-1">
                                        <form action="{{ route('api-keys.assign-departments', $client->id) }}"
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
                    @if($clients->count())
                    <div class="mt-2">
                        {{ $clients->withQueryString()->links() }}
                    </div>
                    @endif
                    @php
                    $clients = null;
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