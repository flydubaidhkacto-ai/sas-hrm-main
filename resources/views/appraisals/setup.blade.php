@extends('layouts.master')
@section('title', 'Appraisal Setup')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Appraisal Setup</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Appraisal Setup</li>
                    </ul>
                </div>
                
            </div>
        </div>
        @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <p class="text-muted">Choose the appraisal role (Supervisor / HOD / HR / MD) and the supervisor for each employee. Leave role empty for normal employees. HOD acts only on employees of the same department.</p>
        <form method="POST" action="{{ route('appraisals.setup.save') }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-striped custom-table mb-0">
                    <thead><tr><th>Employee</th><th>Department</th><th>Appraisal role</th><th>Supervisor</th></tr></thead>
                    <tbody>
                    @foreach($users as $u)
                        <tr>
                            <td>{{ $u->name }} <small class="text-muted">{{ $u->employee_id }}</small></td>
                            <td>{{ $u->department?->name }}</td>
                            <td>
                                <select name="users[{{ $u->id }}][appraisal_role]" class="form-control form-control-sm">
                                    <option value="">-</option>
                                    @foreach($roles as $k => $label)
                                        <option value="{{ $k }}" @selected($u->appraisal_role === $k)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="users[{{ $u->id }}][appraisal_supervisor_id]" class="form-control form-control-sm">
                                    <option value="">-</option>
                                    @foreach($users as $s)
                                        @continue($s->id === $u->id)
                                        <option value="{{ $s->id }}" @selected($u->appraisal_supervisor_id == $s->id)>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <button class="btn btn-primary mt-3">Save</button>
        </form>
    </div>
</div>
@endsection
