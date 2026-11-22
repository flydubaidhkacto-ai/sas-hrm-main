@extends('layouts.master')
@section('title', 'Approval Processes')
@section('content')
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Approval Processes</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Approval Processes</li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a href="{{ route('approval-processes.create') }}" class="btn add-btn">
                            <i class="fa fa-plus"></i> Create Approval Process
                        </a>
                    </div>
                </div>
            </div>
            {!! Toastr::message() !!}
            @include('layouts.alerts')

            <div>
                @php
                    $used = [];
                @endphp
                @foreach ($processes as $process)
                    @if($process->group)
                        @if(in_array($process->group, $used))
                            @continue
                        @else
                            @php $used[] = $process->group; @endphp
                        @endif
                    @endif
                    <div class="card mb-3">
                        <div class="card-header">
                            <strong>{{ $process->name }}</strong>
                            {{-- @dump($process) --}}
                            @if($process->group)
                                @foreach ($processes->where('group', $process->group) as $item)
                                    <span
                                        class="badge bg-info text-white">
                                        {{ ucwords(str_replace('_', ' ', $item->type)) }}
                                    </span>
                                @endforeach
                            @else
                                <span
                                    class="badge bg-info text-white">{{ ucwords(str_replace('_', ' ', $process->type)) }}</span>
                            @endif
                            <form action="{{ route('approval-processes.destroy', $process->id) }}" method="POST"
                                class="float-right d-inline-block">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Are you sure you want to delete this approval process?')"
                                    class="btn btn-sm btn-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                            <a href="{{ route('approval-processes.edit', $process->id) }}"
                                class="btn btn-sm btn-warning float-right">
                                <i class="fa fa-pen"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <h5>Assigned Users:</h5>
                            <ul>
                                @if ($process->for_users)
                                    @foreach ($process->for_users ?: [] as $userId)
                                        @php $user = $users->find($userId); @endphp
                                        @if ($user)
                                            <li>
                                                {{ $user->name }}
                                            </li>
                                        @endif
                                    @endforeach
                                @else
                                    <li>All Users</li>
                                @endif
                            </ul>
                            <h5>Steps:</h5>
                            @if ($process->steps->isEmpty())
                                <p>No steps configured yet.</p>
                            @else
                                <ul class="list-group">
                                    @foreach ($process->steps->sortBy('step_order') as $step)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>

                                                Step {{ $step->step_order }}: {{ $step->approver->name }}
                                                @if($step->is_publisher)
                                                <span
                                                class="badge bg-success text-white">
                                                <i class="fa fa-eye"></i>
                                                    Publisher
                                                </span>
                                                @endif
                                            </span>
                                            <form
                                                action="{{ route('approval-processes.removeStep', ['process' => $process->id, 'step' => $step->id]) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
