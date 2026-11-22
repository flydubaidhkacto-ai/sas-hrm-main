@extends('layouts.master')
@section('title', "Guest Details")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Guest Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('guests.index') }}">Guests</a></li>
                        <li class="breadcrumb-item active">Guest Details</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="{{ route('guests.edit', $guest->id) }}" class="btn btn-primary">
                        <i class="fa fa-pencil"></i> Edit
                    </a>
                    <a href="{{ route('guests.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        {{-- message --}}
        {!! Toastr::message() !!}

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Guest Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="col-form-label"><strong>Name:</strong></label>
                                    <p class="form-control-plaintext">{{ $guest->name }}</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="col-form-label"><strong>Email:</strong></label>
                                    <p class="form-control-plaintext">{{ $guest->email ?: 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="col-form-label"><strong>Phone:</strong></label>
                                    <p class="form-control-plaintext">{{ $guest->phone_number ?: 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="col-form-label"><strong>Company:</strong></label>
                                    <p class="form-control-plaintext">{{ $guest->company ?: 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="col-form-label"><strong>Status:</strong></label>
                                    <p class="form-control-plaintext">
                                        <span class="badge {{ $guest->is_active ? 'badge-success' : 'badge-danger' }}">
                                            {{ $guest->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="col-form-label"><strong>Created:</strong></label>
                                    <p class="form-control-plaintext">{{ $guest->created_at->format('d M, Y H:i') }}</p>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="col-form-label"><strong>Address:</strong></label>
                                    <p class="form-control-plaintext">{{ $guest->address ?: 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="col-form-label"><strong>RFID Card Number:</strong></label>
                                    <p class="form-control-plaintext">{{ $guest->zk_device_card ?: 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Quick Actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('guests.edit', $guest->id) }}" class="btn btn-primary btn-block">
                                <i class="fa fa-pencil"></i> Edit Guest
                            </a>
                            <button type="button" class="btn btn-danger btn-block" onclick="deleteGuest()">
                                <i class="fa fa-trash"></i> Delete Guest
                            </button>
                        </div>
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
                <p>Are you sure you want to delete this guest? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="{{ route('guests.destroy', $guest->id) }}" method="POST" style="display: inline;">
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
<script>
    function deleteGuest() {
        $('#deleteModal').modal('show');
    }
</script>
@endsection
