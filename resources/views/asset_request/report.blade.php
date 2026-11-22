@extends('layouts.master')
@section('title', 'Asset Request Report')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Asset Request Report</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Asset Request Report</li>
                    </ul>
                </div>
            </div>
        </div>

        {!! Toastr::message() !!}
        @include('layouts.alerts')

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('asset_request.report') }}" class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Date From</label>
                            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Date To</label>
                            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="all" {{ (isset($filters['status']) && $filters['status']=='all')?'selected':'' }}>All</option>
                                <option value="pending" {{ (isset($filters['status']) && $filters['status']=='pending')?'selected':'' }}>Pending</option>
                                <option value="approved" {{ (isset($filters['status']) && $filters['status']=='approved')?'selected':'' }}>Approved</option>
                                <option value="rejected" {{ (isset($filters['status']) && $filters['status']=='rejected')?'selected':'' }}>Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label for="">Employee</label>
                        <select name="employee" id="" class="form-control employee-select2">
                            @if(request()->employee)
                                <option value="{{ request()->employee }}" selected>
                                    {{ \App\Models\User::find(request()->employee)?->name }}
                                </option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Asset</label>
                            <select name="asset_id" class="form-control">
                                <option value="all">All</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" {{ (isset($filters['asset_id']) && $filters['asset_id']==$asset->id)?'selected':'' }}>{{ $asset->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('asset_request.report') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped custom-table mb-0">
                    <thead>
                        <tr>
                            <th>S.L</th>
                            <th>Request Date</th>
                            <th>Emp. ID</th>
                            <th>Emp. Name</th>
                            <th>Asset Name</th>
                            <th>Status</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($asset_requests as $req)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $req->created_at->format('Y-m-d') }}</td>
                            <td>{{ $req->employee_id }}</td>
                            <td>{{ $req->employee->name }}</td>
                            <td>{{ $req->asset->name }}</td>
                            <td>
                                @if($req->status=='pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($req->status=='approved')
                                    <span class="badge badge-info">Approved</span>
                                @elseif($req->status=='rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                            <td>{{ $req->reason }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@section('script')
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    new DataTable('table', {});
</script>
@endsection

@endsection
