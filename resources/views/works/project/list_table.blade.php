<table class="table table-striped custom-table" style="min-height: 300px;">
    <thead>
        <tr>
            <th>SL. No.</th>
            <th>Name</th>
            <th>Category</th>
            <th>Client</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($projects as $project)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
                <a href="{{ route('projects.show', $project->id) }}">{{ $project->name ?? '' }}</a>
            </td>
            <td>{{ $project->category->name ?? '' }}</td>
            <td>{{ $project->client->name ?? '' }}</td>
            <td>{{ $project->start_date ? Carbon($project->start_date)->format('d-m-Y') : '--' }}</td>
            <td>{{ $project->end_date ? Carbon($project->end_date)->format('d-m-Y') : '--' }}</td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle
                        {{ $project->status == 'completed' ? 'badge bg-success' :
                           ($project->status == 'in_progress' ? 'badge bg-primary' :
                           ($project->status == 'on_hold' ? 'badge bg-secondary' : 'badge bg-warning')) }}"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                    </button>
                    <ul class="dropdown-menu" style="position: absolute; min-width: 120px; max-height: 150px; overflow-y: auto; z-index: 1000;">
                        <form action="{{ route('projects.update-status', $project->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <li><button type="submit" name="status" value="pending" class="dropdown-item">Pending</button></li>
                            <li><button type="submit" name="status" value="in_progress" class="dropdown-item">In Progress</button></li>
                            <li><button type="submit" name="status" value="completed" class="dropdown-item">Completed</button></li>
                            <li><button type="submit" name="status" value="on_hold" class="dropdown-item">On Hold</button></li>
                        </form>
                    </ul>
                </div>
            </td>
            <td>
                <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                <form action="{{ route('projects.destroy', $project->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
