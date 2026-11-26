@extends('layouts.master')
@section('title', 'New Appraisal')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">New Appraisal</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">New Appraisal</li>
                    </ul>
                </div>
                
            </div>
        </div>
        @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('appraisals.store') }}">
                @csrf
                <div class="form-group">
                    <label>Employee <small class="text-muted">(only employees with an appraisal supervisor are listed)</small></label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">-- select --</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}" @selected(old('employee_id') == $e->id)>{{ $e->name }} ({{ $e->employee_id }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Period (e.g. Jan 2026 - Dec 2026)</label>
                    <input type="text" name="period" value="{{ old('period') }}" class="form-control" required>
                </div>
                <button class="btn btn-primary">Create</button>
                <a href="{{ route('appraisals.index') }}" class="btn btn-light">Cancel</a>
            </form>
        </div></div>
    </div>
</div>
@endsection
