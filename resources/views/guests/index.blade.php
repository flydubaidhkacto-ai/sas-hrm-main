@extends('layouts.master')
@section('title', "Guest Management")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Guest Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Guests</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="{{ route('guests.create') }}" class="btn add-btn">
                        <i class="fa fa-plus"></i> Add Guest
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Search Filter -->
        <form action="{{ route('guests.index') }}" method="get" class="mb-3">
            <div class="row filter-row">
                <div class="mt-2 col-sm-6 col-md-4">
                    <label for="">Search</label>
                    <input name="search" type="text" class="form-control"
                           value="{{ request()->search }}"
                           placeholder="Search by name, email, or phone">
                </div>
                <div class="col-sm-6 col-md-2 mt-2">
                    <label for="">&nbsp;</label>
                    <button type="submit" class="btn btn-success btn-block">Search</button>
                </div>
                <div class="col-sm-6 col-md-2 mt-2">
                    <label for="">&nbsp;</label>
                    <a href="{{ route('guests.index') }}" class="btn btn-secondary btn-block">Reset</a>
                </div>
            </div>
        </form>
        <!-- /Search Filter -->

        {{-- message --}}
        {!! Toastr::message() !!}

        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped custom-table data-table">
                        <thead>
                            <tr>
                                <th>S.L</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Company</th>
                                <th>RFID Card</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($guests as $index => $guest)
                                <tr>
                                    <td>{{ $guests->firstItem() + $index }}</td>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="{{ route('guests.show', $guest->id) }}">
                                                {{ $guest->name }}
                                            </a>
                                        </h2>
                                    </td>
                                    <td>{{ $guest->email ?: '-' }}</td>
                                    <td>{{ $guest->phone_number ?: '-' }}</td>
                                    <td>{{ $guest->company ?: '-' }}</td>
                                    <td>{{ $guest->zk_device_card ?: '-' }}</td>
                                    <td>
                                        <div class="dropdown action-label">
                                            <label class="switch">
                                                <input type="checkbox"
                                                       class="status-toggle"
                                                       data-id="{{ $guest->id }}"
                                                       {{ $guest->is_active ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                            <span class="status-text ms-2 d-none">
                                                {{ $guest->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>{{ $guest->created_at->format('d M, Y') }}</td>
                                    <td class="text-right">
                                        <div class="dropdown dropdown-action">
                                            <a href="#" class="action-icon dropdown-toggle"
                                               data-toggle="dropdown" aria-expanded="false">
                                                <i class="material-icons">more_vert</i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="{{ route('guests.show', $guest->id) }}">
                                                    <i class="fa fa-eye m-r-5"></i> View
                                                </a>
                                                <a class="dropdown-item" href="{{ route('guests.edit', $guest->id) }}">
                                                    <i class="fa fa-pencil m-r-5"></i> Edit
                                                </a>
                                                <a class="dropdown-item" href="#"
                                                   onclick="deleteGuest({{ $guest->id }})">
                                                    <i class="fa fa-trash-o m-r-5"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $guests->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Guest</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this guest?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<style>
/* Toggle Switch Styles */
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
}

input:checked + .slider {
    background-color: #2196F3;
}

input:focus + .slider {
    box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}
</style>

{{-- DataTable --}}
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
    function deleteGuest(id) {
        $('#deleteForm').attr('action', '{{ route("guests.destroy", "") }}/' + id);
        $('#deleteModal').modal('show');
    }

    // AJAX Toggle Status
    $(document).on('change', '.status-toggle', function() {
        const guestId = $(this).data('id');
        const isChecked = $(this).is(':checked');
        const statusText = $(this).closest('td').find('.status-text');

        $.ajax({
            url: `{{ route("guests.index") }}/${guestId}/toggle-status`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    statusText.text(response.status ? 'Active' : 'Inactive');
                    toastr.success(response.message);
                } else {
                    // Revert toggle if failed
                    $(this).prop('checked', !isChecked);
                    toastr.error('Failed to update status');
                }
            },
            error: function() {
                // Revert toggle if failed
                $(this).prop('checked', !isChecked);
                toastr.error('Failed to update status');
            }
        });
    });

    $(document).ready(function() {
        let table = new DataTable('.data-table', {
            pageLength: 25,
            order: [[7, 'desc']] // Sort by created date descending
        });
    });
</script>
@endsection
