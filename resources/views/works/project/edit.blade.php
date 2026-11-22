@extends('layouts.master')
@section('title', "Edit Project")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Edit Project: {{ $project->name }}
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
                        <li class="breadcrumb-item active">
                            Edit Project
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        {!! Toastr::message() !!}
        <form method="POST" action="{{ route('projects.update', $project->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="short_code">Short Code<sup class="text-danger"></sup> :</label>
                        <input type="text" class="form-control" id="short_code" name="short_code"
                            value="{{ old('short_code', $project->short_code) }}">
                        @error('short_code')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Project Name<sup class="text-danger">*</sup> :</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="{{ old('name', $project->name) }}" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="start_date">Start Date<sup class="text-danger">*</sup> :</label>
                        <input type="text" class="form-control datetimepicker" id="start_date" name="start_date"
                            value="{{ old('start_date', $project->start_date ? carbon($project->start_date)->format('d-m-Y') : '') }}" required onchange="calculateDuration()">
                        @error('start_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="text" class="form-control datetimepicker" id="end_date" name="end_date"
                            value="{{ old('end_date', $project->end_date ? carbon($project->end_date)->format('d-m-Y') : '') }}" onchange="calculateDuration()">
                        @error('end_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="category_id">Category<sup class="text-danger">*</sup> :</label>
                        <select class="form-control select2" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $project->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="client_id">Client<sup class="text-danger">*</sup> :</label>
                        <select class="form-control select2" id="client_id" name="client_id" required>
                            <option value="">Select Client</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}"
                                    {{ old('client_id', $project->client_id) == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="project_summary">Project Summary</label>
                        <textarea class="form-control" id="project_summary" name="project_summary">{{ old('project_summary', $project->project_summary) }}</textarea>
                        @error('project_summary')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="project_notes">Project Notes</label>
                        <textarea class="form-control" id="project_notes" name="project_notes">{{ old('project_notes', $project->project_notes) }}</textarea>
                        @error('project_notes')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="project_budget">Project Budget</label>
                        <input type="number" class="form-control" id="project_budget" name="project_budget"
                            value="{{ old('project_budget', $project->project_budget) }}">
                        @error('project_budget')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="estimate_hours">Estimated Hours</label>
                        <input type="number" class="form-control" id="estimate_hours" name="estimate_hours"
                            value="{{ old('estimate_hours', $project->estimate_hours) }}">
                        @error('estimate_hours')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control select2" id="status" name="status">
                            <option value="pending" {{ old('status', $project->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ old('status', $project->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ old('status', $project->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="on_hold" {{ old('status', $project->status) == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        </select>
                        @error('status')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="image">Project Image</label> <br>
                        <img src="{{ $project->image ?? false ? '/uploaded_files/' . $project->image : '' }}"
                        class="w-25">
                        <input type="file" name="image" id="image" accept="image/*"
                            class="form-control-file"
                            onchange="$(this).parent().find('img')[0].src = window.URL.createObjectURL(this.files[0])">
                        @error('image')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Update Project</button>
                </div>
            </div>
        </form>

    </div>
</div>
<style>
    .preview {
        color: gray;
        font-size: 10px
    }
</style>
@endsection
@section('script')
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@endSection
