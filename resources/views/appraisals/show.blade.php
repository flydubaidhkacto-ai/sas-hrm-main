@php
    $editSup = $appraisal->status === 'pending_supervisor' && $canAct;
    $stages = ['supervisor' => 'Supervisor', 'hod' => 'Head of Department', 'hr' => 'Human Resource (HR)', 'md' => 'Managing Director'];
@endphp
@extends('layouts.master')
@section('title', 'Appraisal')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Appraisal: {{ $appraisal->employee->name }} ({{ $appraisal->period }})</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('appraisals.index') }}">Appraisals</a></li>
                        <li class="breadcrumb-item active">Form</li>
                    </ul>
                </div>
                <div class="col-auto"><a href="{{ route('appraisals.print', $appraisal) }}" target="_blank" class="btn btn-secondary">Print form</a></div>
            </div>
        </div>
        @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        <div class="card"><div class="card-body"><div class="row">
            <div class="col-md-3"><small class="text-muted d-block">Employee</small>{{ $appraisal->employee->name }}</div>
            <div class="col-md-3"><small class="text-muted d-block">Designation / ID</small>{{ $appraisal->employee->designation?->name }} / {{ $appraisal->employee->employee_id }}</div>
            <div class="col-md-3"><small class="text-muted d-block">Department</small>{{ $appraisal->employee->department?->name }}</div>
            <div class="col-md-3"><small class="text-muted d-block">Status</small><strong>{{ $appraisal->statusLabel() }}</strong></div>
        </div></div></div>

        @if($editSup)
        <form method="POST" action="{{ route('appraisals.supervisor', $appraisal) }}">
            @csrf @method('PUT')
        @endif

        <h4 class="bg-light p-2 mt-3">Section ONE - Performance Rating</h4>
        @include('appraisals._ratings', ['list' => $ratings->filter(fn ($r) => $r->criterion->section === 'one'), 'edit' => $editSup])

        <h4 class="bg-light p-2 mt-4">Section TWO - Job Performance Standards</h4>
        <p class="text-muted small">BS: Below Standard · MS: Meets Standards · ES: Exceeds Standards</p>
        <div class="table-responsive"><table class="table table-bordered bg-white">
            <thead><tr><th style="width:40px">#</th><th>Job Description / Performance</th><th>Satisfactory?</th><th>Standard</th></tr></thead>
            <tbody>
            @for($i = 0; $i < 10; $i++)
                @php $j = $appraisal->jobItems[$i] ?? null; @endphp
                @if($editSup || $j)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    @if($editSup)
                        <td><input type="text" name="jobs[{{ $i }}][description]" value="{{ $j?->description }}" class="form-control"></td>
                        <td>
                            <select name="jobs[{{ $i }}][is_satisfactory]" class="form-control">
                                <option value="">-</option>
                                <option value="1" @selected($j && $j->is_satisfactory === true)>Satisfactory</option>
                                <option value="0" @selected($j && $j->is_satisfactory === false)>Not Satisfactory</option>
                            </select>
                        </td>
                        <td>
                            <select name="jobs[{{ $i }}][standard]" class="form-control">
                                <option value="">-</option>
                                @foreach(['BS', 'MS', 'ES'] as $s)<option @selected($j?->standard === $s)>{{ $s }}</option>@endforeach
                            </select>
                        </td>
                    @else
                        <td>{{ $j->description }}</td>
                        <td>{{ $j->is_satisfactory === null ? '-' : ($j->is_satisfactory ? 'Satisfactory' : 'Not Satisfactory') }}</td>
                        <td>{{ $j->standard ?? '-' }}</td>
                    @endif
                </tr>
                @endif
            @endfor
            </tbody>
        </table></div>

        <h4 class="bg-light p-2 mt-4">Section THREE - Overall Work Performance</h4>
        @include('appraisals._ratings', ['list' => $ratings->filter(fn ($r) => $r->criterion->section === 'three'), 'edit' => $editSup])

        @if($editSup)
            <div class="card mt-3"><div class="card-body">
                <h5>To be filled by Supervisor</h5>
                @include('appraisals._review_fields', ['review' => $appraisal->review('supervisor'), 'prefix' => 'review'])
                <small class="text-muted">Leave marks empty to use the auto-calculated total.</small><br>
                <button class="btn btn-primary mt-3">Submit &amp; sign</button>
            </div></div>
        </form>
        @endif

        <h4 class="bg-light p-2 mt-4">Section FOUR - Employee's Comment</h4>
        @if($appraisal->status === 'pending_employee' && $canAct)
            <form method="POST" action="{{ route('appraisals.employee', $appraisal) }}" class="card card-body">
                @csrf @method('PUT')
                <small class="text-muted mb-2">Write any other information you would like to inform the management (education/training, drawbacks, self evaluation).</small>
                <textarea name="employee_comment" rows="5" class="form-control">{{ $appraisal->employee_comment }}</textarea>
                <button class="btn btn-primary mt-3">Save &amp; sign</button>
            </form>
        @else
            <div class="card card-body" style="white-space:pre-line">{{ $appraisal->employee_comment ?: '—' }}
                @if($appraisal->employee_signed_at)<small class="text-muted d-block mt-2">Signed: {{ $appraisal->employee_signed_at->format('d M Y H:i') }}</small>@endif
            </div>
        @endif

        @foreach($stages as $key => $label)
            @continue($key === 'supervisor' && $editSup)
            @php $rv = $appraisal->review($key); $myTurn = $appraisal->status === "pending_$key" && $canAct; @endphp
            <h4 class="bg-light p-2 mt-4">{{ $label }}</h4>
            @if($myTurn)
                <form method="POST" action="{{ route('appraisals.review', [$appraisal, $key]) }}" class="card card-body">
                    @csrf
                    @include('appraisals._review_fields', ['review' => $rv, 'prefix' => ''])
                    <div><button class="btn btn-primary">Submit &amp; sign</button></div>
                </form>
            @elseif($rv)
                <div class="card card-body"><div class="row">
                    <div class="col-md-3"><small class="text-muted d-block">Marks</small><strong>{{ $rv->marks }}</strong> ({{ $rv->result_band }})</div>
                    <div class="col-md-3"><small class="text-muted d-block">Recommendation</small>{{ $rv->recommendation }}</div>
                    <div class="col-md-3"><small class="text-muted d-block">Increment</small>{{ $rv->increment_steps }}-Step{{ $rv->increment_steps > 1 ? 's' : '' }}</div>
                    <div class="col-md-3"><small class="text-muted d-block">Signed</small>{{ $rv->reviewer?->name }}<br>{{ $rv->signed_at?->format('d M Y') }}</div>
                    <div class="col-12 mt-2"><small class="text-muted d-block">Comments</small>{{ $rv->comment ?: '—' }}</div>
                </div></div>
            @else
                <div class="card card-body text-muted">Not yet reviewed.</div>
            @endif
        @endforeach
    </div>
</div>
@endsection
