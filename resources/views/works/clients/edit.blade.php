@extends('layouts.master')
@section('title', "Edit Client")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Edit Client: {{ $client->name }}
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
                        <li class="breadcrumb-item active">
                            Edit
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        {!! Toastr::message() !!}
        <form id="client-edit-form" action="{{ route('clients.update', $client->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-12">
                    <label>Client Information</label>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Name <sup class="text-danger">*</sup>:</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" required value="{{ old('name', $client->name) }}">
                                @error('name') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Phone <sup class="text-danger">*</sup>:</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" required value="{{ old('phone', $client->phone) }}">
                                @error('phone') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Email <sup class="text-danger">*</sup>:</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" required value="{{ old('email', $client->email) }}">
                                @error('email') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <label>Company Details</label>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Company Name:</label>
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror" name="company_name" value="{{ old('company_name', $client->company_name) }}">
                                @error('company_name') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Company Email:</label>
                                <input type="email" class="form-control @error('company_email') is-invalid @enderror" name="company_email" value="{{ old('company_email', $client->company_email) }}">
                                @error('company_email') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Company Phone:</label>
                                <input type="text" class="form-control @error('company_phone') is-invalid @enderror" name="company_phone" value="{{ old('company_phone', $client->company_phone) }}">
                                @error('company_phone') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Company Website:</label>
                                <input type="text" class="form-control @error('company_website') is-invalid @enderror" name="company_website" value="{{ old('company_website', $client->company_website) }}">
                                @error('company_website') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Company Address:</label>
                                <textarea class="form-control @error('company_address') is-invalid @enderror" name="company_address">{{ old('company_address', $client->company_address) }}</textarea>
                                @error('company_address') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Shipping Address:</label>
                                <textarea class="form-control @error('shipping_address') is-invalid @enderror" name="shipping_address">{{ old('shipping_address', $client->shipping_address) }}</textarea>
                                @error('shipping_address') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label>Assigned By:</label>
                        <select name="assigned_by" id="assigned_by" class="form-control select2">
                            <option value="">Select User</option>
                            @foreach (\App\Models\User::active_employees()->get() as $user)
                                    <option value="{{ $user->id }}" {{ $client->assigned_by == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label for="logo">Company Logo:</label> <br>
                        <img src="{{ ($client['company_logo'] ?? false) ? '/uploaded_files/' . $client['company_logo'] : '' }}" class="w-25">
                        <input type="file" name="company_logo" id="company_logo" accept="image/*" class="form-control-file"
                        onchange="$(this).parent().find('img')[0].src = window.URL.createObjectURL(this.files[0])">
                        @error('company_logo') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                    </div>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        Update Client
                    </button>
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
