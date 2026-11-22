@extends('layouts.master')
@section('title', 'User Activity')
@push('style')
<style>
    /* Apply fixed height and horizontal scrolling */
    .table-responsive {
        /* max-height: 500px; */
        /* Adjust this value as per your needs */
        overflow-y: auto;
        /* Vertical scrolling */
        overflow-x: auto;
        /* Horizontal scrolling */
        /* margin-top: 20px; */
    }

    /* Optional: Add borders for better table appearance */
    .table {
        border-collapse: collapse;
        width: 100%;
    }

    .table th,
    .table td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
    }

    .table th {
        background-color: #f4f4f4;
        font-weight: bold;
    }
</style>
@endpush
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        User Activity
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('half-day-fines.index') }}">User Activity</a></li>
                        <li class="breadcrumb-item active">User Activity</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <form action="{{ route('activity-log.destroy', 1) }}" method="post">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-trash"></i>
                            Clear All
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <form method="get" action="{{ route('activity-log.index') }}" class="row filter-row mb-4">
            @if(
            role_can('activity-log')
            )
            <div class="mb-2 col-sm-6 col-md-3">
                <label for="">Employee</label>
                <select name="employee" id="" class="form-control employee-select2">
                    @if(request()->employee)
                    <option value="{{ request()->employee }}" selected>
                        {{ \App\Models\User::find(request()->employee)?->name }}
                    </option>
                    @endif
                </select>
            </div>
            <div class="mb-2 col-sm-6 col-md-3">
                <label for="">URL</label>
                <input class="form-control" type="text" value="{{ request()->url }}" name="url">
            </div>

            <div class="col-sm-6 col-md-3 mt-4">
                <button type="submit" class="btn btn-success btn-block"> Search </button>
            </div>
            @endif
        </form>

        @include('layouts.alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table id="useractivitylog-table" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Model Name</th>
                                <th>Status</th>

                                <th>Operation Data</th>
                                <th>User ID</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($userActivityLogs as $key => $userActivityLog)
                            <tr>
                                <td>{{ $userActivityLogs->firstItem() + $key }}</td>
                                <td>{{ $userActivityLog->model_name }}</td>
                                <td>
                                    @if ($userActivityLog->status == 0)
                                    Visited
                                    @elseif ($userActivityLog->status == 1)
                                    Created
                                    @elseif ($userActivityLog->status == 2)
                                    Updated
                                    @elseif ($userActivityLog->status == 3)
                                    Deleted
                                    @else
                                    Unknown
                                    @endif
                                </td>

                                <td style="max-width: 400px;">
                                    <div style="word-break: break-all;">
                                        {{ $userActivityLog->operation_data }}
                                    </div>
                                </td>
                                <td>{{ $userActivityLog->rel_to_user->name }}</td>
                                <td>
                                    {{ $userActivityLog->created_at->format('d F, Y h:i A') }}
                                    <br>
                                    <small><i>{{ $userActivityLog->created_at->diffForHumans() }}</i></small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    {{ $userActivityLogs->withQueryString()->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
@push('js')
@endpush
