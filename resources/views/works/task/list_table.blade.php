<table class="table table-striped custom-table" style="min-height: 300px;">
    <thead>
        <tr>
            <th>SL. No.</th>
            <th>Title</th>
            <th>Category</th>
            <th>Project</th>
            <th>Start Date</th>
            <th>Due Date</th>
            <th>Assigned</th>
            <th>Status</th>
            @if(role_can('work-admin') || role_can('work-employee'))
                <th>Actions</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($tasks as $task)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $task->title ?? '' }}
                @if($task->timeTracker->count() > 0)
                    <span class="badge bg-info" style="color: white;">
                        @php
                            $totalMinutes = $task->timeTracker->sum(function($tracker) {
                                if ($tracker->start_time && $tracker->end_time) {
                                    return Carbon\Carbon::parse($tracker->start_time)->diffInMinutes(Carbon\Carbon::parse($tracker->end_time));
                                }
                                return 0;
                            });

                            $hours = floor($totalMinutes / 60);
                            $minutes = $totalMinutes % 60;

                            if ($hours > 0) {
                                if ($minutes > 0) {
                                    echo "$hours.$minutes hours";
                                } else {
                                    echo "$hours hours";
                                }
                            } else {
                                echo "$minutes minutes";
                            }
                        @endphp
                    </span>
                @endif

                <div class="time-tracker-buttons">
                    @if($task->status != 'done')
                        <div class="timer-controls-{{ $task->id }}">
                            @php
                                $activeTimer = $task->timeTracker->where('end_time', null)->first();
                            @endphp

                            <button class="btn btn-success btn-sm start-timer" data-task-id="{{ $task->id }}"
                                    style="{{ $activeTimer ? 'display: none;' : '' }}">
                                <i class="fa fa-play"></i> Start
                            </button>

                            <span class="timer-display-{{ $task->id }}"
                                style="{{ $activeTimer ? 'display: inline-block;' : 'display: none;' }}">
                                Time: <span class="timer-value">00:00:00</span>
                            </span> <br>

                            <div class="running-timer-controls-{{ $task->id }}"
                                style="{{ $activeTimer ? 'display: inline-block; margin-top: 5px;' : 'display: none;' }}">
                                <button class="btn btn-danger btn-sm stop-timer" data-task-id="{{ $task->id }}">
                                    <i class="fa fa-stop"></i> Stop
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </td>
            <td>{{ $task->taskCategory->name ?? '' }}</td>
            <td>{{ $task->project->name ?? '' }}</td>
            <td>{{ $task->start_date ? Carbon($task->start_date)->format('d-m-Y') : '--' }}</td>
            <td>{{ $task->due_date ? Carbon($task->due_date)->format('d-m-Y') : '--' }}</td>
            {{-- <td>{{ $task->assigned_to ? $task->assignedUser->name : '--' }}</td> --}}
            <td>
                @if($task->assigned_to && $task->assignedUser->picture_file)
                    <img src="{{ asset('uploaded_files/' . $task->assignedUser->picture_file) }}"
                         alt="{{ $task->assignedUser->name }}"
                         title="{{ $task->assignedUser->name }}"
                         class="rounded-circle"
                         style="width: 35px; height: 35px; object-fit: cover;">
                @else
                    {{ $task->assigned_to ? $task->assignedUser->name : '--' }}
                @endif
            </td>
            <td>
                @if(role_can('work-admin') || role_can('work-employee'))
                    <div class="dropdown">
                        <button class="btn btn-sm dropdown-toggle badge" style="background-color: @foreach (App\Models\TaskStatus::all() as $status)
                            {{ $task->status == $status->id ? $status->color : '' }} @endforeach; color: white;"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @foreach (App\Models\TaskStatus::all() as $status)
                                {{ $task->status == $status->id ? $status->name : '' }}
                            @endforeach
                        </button>
                        <ul class="dropdown-menu" style="position: absolute; min-width: 120px; max-height: 150px; overflow-y: auto; z-index: 1000;">
                            <form action="{{ route('tasks.update.status', $task->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                @foreach (App\Models\TaskStatus::all() as $status)
                                    <li><button type="submit" name="status" value="{{ $status->id }}" class="dropdown-item">{{ $status->name }}</button></li>
                                @endforeach
                            </form>
                        </ul>
                    </div>
                @endif
            </td>
            @if(role_can('work-admin') || role_can('work-employee'))
                <td>
                    <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                    </form>
                </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>

<script>
    let timers = {};
    let activeTaskId = null;

    function startTimer(taskId, startFromTime = null) {
        let startTime = startFromTime ? new Date(startFromTime).getTime() : new Date().getTime();
        let timerDisplay = document.querySelector(`.timer-display-${taskId} .timer-value`);

        timers[taskId] = setInterval(() => {
            let now = new Date().getTime();
            let elapsed = now - startTime;

            let hours = Math.floor(elapsed / (1000 * 60 * 60));
            let minutes = Math.floor((elapsed % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((elapsed % (1000 * 60)) / 1000);

            timerDisplay.textContent =
                String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');
        }, 1000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        @foreach($tasks as $task)
            @if($activeTimer = $task->timeTracker->where('end_time', null)->first())
                activeTaskId = {{ $task->id }};
                startTimer({{ $task->id }}, '{{ $activeTimer->start_time }}');
            @endif
        @endforeach
    });

    function showRunningTimerControls(taskId) {
        // Hide start button
        const startButton = document.querySelector(`.timer-controls-${taskId} .start-timer`);
        startButton.style.display = 'none';

        // Show timer display
        const timerDisplay = document.querySelector(`.timer-display-${taskId}`);
        timerDisplay.style.display = 'inline-block';

        // Show push and stop buttons
        const runningControls = document.querySelector(`.running-timer-controls-${taskId}`);
        runningControls.style.display = 'inline-block';
    }

    function resetTimerControls(taskId) {
        // Show start button
        const startButton = document.querySelector(`.timer-controls-${taskId} .start-timer`);
        startButton.style.display = 'inline-block';

        // Hide timer display
        const timerDisplay = document.querySelector(`.timer-display-${taskId}`);
        timerDisplay.style.display = 'none';

        // Hide push and stop buttons
        const runningControls = document.querySelector(`.running-timer-controls-${taskId}`);
        runningControls.style.display = 'none';

        // Reset timer value
        const timerValue = document.querySelector(`.timer-display-${taskId} .timer-value`);
        timerValue.textContent = '00:00:00';
    }

    document.querySelectorAll('.start-timer').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.dataset.taskId;

            // If there's already an active timer, stop it
            if (activeTaskId) {
                clearInterval(timers[activeTaskId]);
                resetTimerControls(activeTaskId);
            }

            activeTaskId = taskId;
            showRunningTimerControls(taskId);
            startTimer(taskId);
            $.ajax({
                error: function(xhr, status, error) {
                    alert('Error starting timer:'+ error);
                    // console.error('Error starting timer:', error);
                },
                url: `/tasks/${taskId}/start-timer`,
                method: 'POST',
            });


        });
    });
    document.querySelectorAll('.stop-timer').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.dataset.taskId;

            // Clear the interval
            clearInterval(timers[taskId]);

            // Reset controls
            resetTimerControls(taskId);
            activeTaskId = null;

            $.ajax({
                error: function(xhr, status, error) {
                    alert('Error stopping timer:'+ error);
                    // console.error('Error stopping timer:', error);
                },
                url: `/tasks/${taskId}/stop-timer`,
                method: 'POST',
            });
        });
    });
</script>
