@extends('layouts.master')
@section('title', 'My Roster')
@section('content')
    <style>
        #shiftModal {
            position: absolute;
            top: 20%;
            left: 30%;
            padding: 20px;
            background: white;
            border: 1px solid #ccc;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
    </style>
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            @if (role_can('roster'))
                                Emp.
                            @else
                                My
                            @endif
                            Roster
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Roster</li>
                        </ul>
                    </div>
                </div>
            </div>
            {{-- show success messages --}}
            {!! Toastr::message() !!}
            <div class="mt-2">
                <div>
                    <div>
                        <form class="">
                            <div class="row">
                                @if(role_can('roster'))
                                    <div class="col-md-6 mb-2">
                                        <div class="form-group">
                                            <label class="d-block" for="">Employee</label>
                                            <div>
                                                <select name="employee" class="form-control employee-select2">
                                                    @if ($user)
                                                        <option value="{{ $user->id }}">
                                                            {{ $user->name }}
                                                        </option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-3 mb-3 form-group">
                                    <label for="startDate" class="form-label">
                                        Start Date:
                                    </label>
                                    <input type="date" id="startDate" name="start" class="form-control"
                                           value="{{ request('start') ?? $from->format('Y-m-d') }}">
                                </div>
                                <div class="col-md-3 mb-3 form-group">
                                    <label for="endDate" class="form-label">
                                        End Date:
                                    </label>
                                    <input type="date" id="endDate" class="form-control form-control" name="end"
                                           value="{{ request('end') ?? $to->format('Y-m-d') }}">
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary">
                                        Search
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="mt-2">
                            <div>
                                <div v-else>
                                    <b>
                                        My Roster from {{ $from->format('d M, Y') }} - {{ $to->format('d M, Y') }}:
                                    </b>
                                    <table class="table table-bordered table-striped mt-1">
                                        <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Start - End Time</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($items as $item)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($item['date'])->format('d M, Y') }}</td>
                                                <td colspan="2">
                                                    @if($item['shift'] ?? null)
                                                        <span>
                                                            <i class="fa fa-clock"></i>
                                                                {{ \Carbon\Carbon::parse($item['shift']['start'])->format('h:m A') }} - {{ \Carbon\Carbon::parse($item['shift']['end'])->format('h:m A') }}
                                                        </span>
                                                    @elseif($item['no_shift_duty'] ?? null)
                                                        <span v-else-if="item.no_shift_duty">
                                                            <h3 class="badge bg-info text-white"
                                                                style="font-size: 15px;">
                                                                @include('roster.partials.no-shift-duty', ['duty' => $item['no_shift_duty']])
                                                            </h3>
                                                        </span>
                                                    @elseif($item['not_set'] ?? null)
                                                        <span v-else-if="item.not_set" class="text-muted">
                                                            <i class="text-warning">
                                                                <i class="fa fa-exclamation-triangle"></i>
                                                                No Roster Added
                                                            </i>
                                                        </span>
                                                    @else
                                                        <span v-else class="text-muted">
                                                            No Duty
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.select2')
@endsection
