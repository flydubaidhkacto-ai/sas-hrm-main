<div class="dropdown">
    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="actionDropdown{{ $id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu" aria-labelledby="actionDropdown{{ $id }}">
        @isset($viewRoute)
            <a class="dropdown-item" href="{{ $viewRoute }}">
                <i class="fa fa-eye"></i>
                View
            </a>
        @endisset
        @isset($editRoute)
            <a class="dropdown-item" href="{{ $editRoute }}">
                <i class="fa fa-edit"></i>
                Edit
            </a>
        @endisset
        @isset($downloadRoute)
            <a class="dropdown-item" href="{{ $downloadRoute }}" target="_blank" download="{{ $title }}.pdf">
                <i class="fa fa-download"></i>
                Download
            </a>
        @endisset
        @isset($deleteRoute)
            <form action="{{ $deleteRoute }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure?')">
                    <i class="fa fa-trash"></i>
                    Delete
                </button>
            </form>
        @endisset
    </div>
</div>
