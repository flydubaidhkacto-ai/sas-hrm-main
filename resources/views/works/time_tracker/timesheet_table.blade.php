<table class="table table-striped custom-table" style="min-height: 300px;">
    <thead>
        <tr>
            <th>SL. No</th>
            <th>Project</th>
            <th>Task</th>
            <th>Employee</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Duration</th>
            {{-- <th>Action</th> --}}
        </tr>
    </thead>
    <tbody>
        @foreach($timeTrackers as $timeTracker)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $timeTracker->task->project->name }}</td>
            <td>{{ $timeTracker->task->title }}</td>
            <td>{{ $timeTracker->employee_info->name }}</td>
            <td>{{ \Carbon\Carbon::parse($timeTracker->start_time)->format('d-m-Y h:i:s A') }}</td>
            <td>{{ \Carbon\Carbon::parse($timeTracker->end_time)->format('d-m-Y h:i:s A') }}</td>
            <td>
                @php
                    $start = \Carbon\Carbon::parse($timeTracker->start_time);
                    $end = \Carbon\Carbon::parse($timeTracker->end_time);
                    $diffInMinutes = $start->diffInMinutes($end);
                    $hours = floor($diffInMinutes / 60);
                    $minutes = $diffInMinutes % 60;
                    $duration = ($hours > 0 ? $hours : '0') . '.' . str_pad($minutes, 2, '0', STR_PAD_LEFT) . 'h';
                @endphp
                {{ $duration }}
                {{-- @if($timeTracker->task->status == 'done')
                    <i class="fas fa-check-circle text-success"
                       data-toggle="tooltip"
                       title="{{ ucfirst($timeTracker->task->status) }}"></i>
                @elseif($timeTracker->task->status == 'in-progress')
                    <i class="fas fa-spinner text-warning"
                       data-toggle="tooltip"
                       title="{{ ucfirst($timeTracker->task->status) }}"></i>
                @elseif($timeTracker->task->status == 'to-do')
                    <i class="fas fa-hourglass-start text-info"
                       data-toggle="tooltip"
                       title="{{ ucfirst($timeTracker->task->status) }}"></i>
                @endif --}}

            </td>
            {{-- <td>
                <a href="" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                @if(role_can('work-admin'))
                    <form action="" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                    </form>
                @endif
            </td> --}}
        </tr>
        @endforeach
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
