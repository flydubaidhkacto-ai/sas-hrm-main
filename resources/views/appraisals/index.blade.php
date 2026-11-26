@extends('layouts.master')
@section('title', 'Performance Appraisals')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Performance Appraisals</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Performance Appraisals</li>
                    </ul>
                </div>
                <div class="col-auto">@if(\App\Support\AppraisalAccess::isHr($user))<a href="{{ route('appraisals.setup') }}" class="btn btn-outline-secondary mr-2">Appraisal Setup</a><a href="{{ route('appraisals.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> New Appraisal</a>@endif</div>
            </div>
        </div>
        @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <div class="table-responsive">
            <table class="table table-striped custom-table mb-0">
                <thead><tr><th>Employee</th><th>Department</th><th>Period</th><th>Total %</th><th>Status</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                @forelse($appraisals as $a)
                    <tr>
                        <td>{{ $a->employee->name }}<br><small class="text-muted">{{ $a->employee->designation?->name }}</small></td>
                        <td>{{ $a->employee->department?->name }}</td>
                        <td>{{ $a->period }}</td>
                        <td>{{ $a->total_percent ?? '-' }}</td>
                        <td><span class="badge {{ $a->status === 'completed' ? 'badge-success' : 'badge-warning' }}">{{ $a->statusLabel() }}</span></td>
                        <td class="text-right"><a class="btn btn-sm btn-info" href="{{ route('appraisals.show', $a) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No appraisals yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $appraisals->links() }}</div>
    </div>
</div>
@endsection
