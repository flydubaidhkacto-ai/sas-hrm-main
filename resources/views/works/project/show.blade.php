@extends('layouts.master')
@section('title', 'Project Details')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Project Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Project Details</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        @include('layouts.alerts')
        <div class="mt-2">


            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <div class="mt-2">
                <div class="row">

                    <div class="kanban-board" style="display: flex; overflow-x: auto; padding-bottom: 20px;">
                        @foreach (\App\Models\TaskStatus::all() as $allowedStatus)
                        <div class="kanban-column" style="width: 300px; flex: 0 0 auto; margin-right: 15px;">
                            <div class="card">
                                <div class="card-header text-center"
                                    style="border-top: 3px solid {{ $allowedStatus->color }};">
                                    <h5 class="card-title">
                                        {{ $allowedStatus->name }}
                                    </h5>
                                </div>
                                <div class="card-body droppable" data-status="{{ $allowedStatus->id }}"
                                    style="min-height: 300px">
                                    @foreach ($tasks as $task)
                                    @if ($task->status == $allowedStatus->id)
                                    <div class="task-card mb-2 p-2 border rounded draggable"
                                        data-task-id="{{ $task->id }}" style="cursor: grab; background: #f8f9fa;">
                                        <h6>{{ $task->title }}</h6>
                                        <div class="task-details">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> Start:
                                                {{ $task->start_date }}
                                            </small>
                                            @if ($task->due_date)
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> Due: {{ $task->due_date }}
                                            </small>
                                            @endif
                                            <br>
                                            <small
                                                class="badge badge-{{ $task->priority == 'low' ? 'success' : ($task->priority == 'medium' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($task->priority) }}
                                            </small>
                                        </div>
                                    </div>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="mt-2">
                {{-- {{ $projects->withQueryString()->links() }} --}}
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
<script>
    $(document).ready(function() {
            function initializeDraggable() {
                $(".draggable").draggable({
                    revert: "invalid",
                    cursor: "move",
                    opacity: 0.7,
                    zIndex: 100,
                });
            }

            initializeDraggable();

            $(".droppable").droppable({
                accept: ".draggable",
                drop: function(event, ui) {
                    let taskId = ui.helper.data("task-id");
                    let newStatus = $(this).data("status");
                    let droppedElement = ui.helper.clone();
                    ui.helper.remove();

                    $(this).append(droppedElement);
                    droppedElement.removeAttr("style").addClass("draggable");

                    initializeDraggable();

                    $.ajax({
                        url: "{{ route('tasks.update.status.kanban') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            task_id: taskId,
                            status: newStatus
                        },
                        success: function(response) {
                            console.log("Task updated successfully.");
                        },
                        error: function(xhr) {
                            console.error("Error updating task.");
                        }
                    });
                }
            });
        });
</script>
@endsection