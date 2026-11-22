<table class="table table-striped custom-table" style="min-height: 300px;">
    <thead>
        <tr>
            <th>SL. No</th>
            <th>Title</th>
            <th>Project</th>
            <th>Status</th>
            <th>User</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tickets as $ticket)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $ticket->title }}</td>
            <td>{{ $ticket->project->name }}</td>
            <td style="position: relative;">
                @if(role_can('work-admin'))
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle
                        {{ $ticket->status == 'solved' ? 'badge bg-success' :
                           ($ticket->status == 'rejected' ? 'badge bg-danger' :
                           ($ticket->status == 'in_progress' ? 'badge bg-info' : 'badge bg-primary')) }}"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                    </button>
                    <ul class="dropdown-menu" style="position: absolute; min-width: 120px; max-height: 150px; overflow-y: auto; z-index: 1000;">
                        <form action="{{ route('tickets.update.status', $ticket->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <li><button type="submit" name="status" value="open" class="dropdown-item">Open</button></li>
                            <li><button type="submit" name="status" value="in_progress" class="dropdown-item">In Progress</button></li>
                            <li><button type="submit" name="status" value="solved" class="dropdown-item">Solved</button></li>
                            <li><button type="submit" name="status" value="rejected" class="dropdown-item">Rejected</button></li>
                        </form>
                    </ul>
                </div>
                @else
                <span class="badge bg-{{ $ticket->status == 'solved' ? 'success' : ($ticket->status == 'rejected' ? 'danger' : 'warning') }}">
                    {{ ucfirst($ticket->status) }}
                </span>
                @endif
            </td>
            <td>{{ $ticket->ticketUser->name }}</td>
            <td>
                <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                @if(role_can('work-admin'))
                    <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                    </form>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
