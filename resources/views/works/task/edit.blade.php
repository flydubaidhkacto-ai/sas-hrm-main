@extends('layouts.master')
@section('title', "Edit Task")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Edit Task: {{ $task->title }}
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
                        <li class="breadcrumb-item active">
                            Edit Task
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        {!! Toastr::message() !!}
        <form method="POST" action="{{ route('tasks.update', $task->id) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Task Title<sup class="text-danger">*</sup> :</label>
                                <input type="text" class="form-control" id="title" name="title"
                                    value="{{ old('title', $task->title) }}" required>
                                @error('title')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="task_category_id">Task Category<sup class="text-danger">*</sup> :</label>
                                <select class="form-control select2" id="task_category_id" name="task_category_id" required>
                                    <option value="">Select Task Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('task_category_id', $task->task_category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('task_category_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="project_id">Project<sup class="text-danger">*</sup> :</label>
                                <select class="form-control select2" id="project_id" name="project_id" required>
                                    <option value="">Select Project</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}"
                                            {{ old('project_id', $task->project_id) == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="start_date">Start Date<sup class="text-danger">*</sup> :</label>
                                <input type="text" class="form-control datetimepicker" id="start_date" name="start_date"
                                    value="{{ old('start_date', $task->start_date ? carbon($task->start_date)->format('d-m-Y') : '') }}" required onchange="calculateDuration()">
                                @error('start_date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="due_date">Due Date</label>
                                <input type="text" class="form-control datetimepicker" id="due_date" name="due_date"
                                    value="{{ old('due_date', $task->due_date ? carbon($task->due_date)->format('d-m-Y') : '') }}" onchange="calculateDuration()">
                                @error('due_date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control select2" id="status" name="status">
                                    {{-- <option value="">Select Status</option> --}}
                                    @foreach (App\Models\TaskStatus::all() as $status)
                                        <option value="{{ $status->id }}"
                                            {{ old('status', $task->status) == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description">{{ old('description', $task->description) }}</textarea>
                        @error('description')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority">Priority</label>
                                <select class="form-control select2" id="priority" name="priority">
                                    <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                                @error('priority')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="assigned_to">Assigned To</label>
                                <select class="form-control select2" id="assigned_to" name="assigned_to">
                                    <option value="">Select Assigned To</option>
                                    @foreach (\App\Models\User::active_employees()->get() as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Update Task</button>
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

    calculateDuration = () => {
        let start_date = $('.datetimepicker[name="start_date"]').val();
        let due_date = $('.datetimepicker[name="due_date"]').val();
        if(!start_date || !due_date) return
        start_date = moment(start_date, 'DD-MM-YYYY');
        due_date = moment(due_date, 'DD-MM-YYYY');
        const duration = (due_date.diff(start_date, 'days'));
        const days = duration + 1;
        if(days < 1)
        {
            return document.getElementById('leave_duration').innerText = 'Invalid date';
        }
        document.getElementById('leave_duration').innerText = days + ' ' + (days > 1 ? 'days' : 'day');
    }

</script>
@endSection
