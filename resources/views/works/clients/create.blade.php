@extends('layouts.master')
@section('title', 'Create Client')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Create Client
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
                            <li class="breadcrumb-item active">
                                Create
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            @include('layouts.alerts')
            {!! Toastr::message() !!}
            <form id="employee-form" action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <label>Client Information</label>

                        <div class="row">
                            <div class="col-md-4 mx-auto">
                                <div class="form-group">
                                    <label>Name <sup class="text-danger">*</sup>:</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        name="name" required value="{{ old('name') }}">
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Phone <sup class="text-danger">*</sup>:</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                        id="phone" name="phone" required value="{{ old('phone') }}">
                                    @error('phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Email <sup class="text-danger">*</sup>:</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" required value="{{ old('email') }}">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Country <sup class="text-danger"></sup>:</label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror"
                                        id="country" name="country" value="{{ old('country') }}">
                                    @error('country')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Client Address:</label>
                                    <textarea class="form-control @error('client_address') is-invalid @enderror" id="client_address" name="client_address">{{ old('client_address') }}</textarea>
                                    @error('client_address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label>Company Details</label>

                        <div class="row">
                            <div class="col-md-3 mx-auto">
                                <div class="form-group">
                                    <label>Company Name:</label>
                                    <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                        id="company_name" name="company_name" value="{{ old('company_name') }}">
                                    @error('company_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Company Email:</label>
                                    <input type="email" class="form-control @error('company_email') is-invalid @enderror"
                                        id="company_email" name="company_email" value="{{ old('company_email') }}">
                                    @error('company_email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Company Phone:</label>
                                    <input type="text" class="form-control @error('company_phone') is-invalid @enderror"
                                        id="company_phone" name="company_phone" value="{{ old('company_phone') }}">
                                    @error('company_phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Company Website:</label>
                                    <input type="text"
                                        class="form-control @error('company_website') is-invalid @enderror"
                                        id="company_website" name="company_website" value="{{ old('company_website') }}">
                                    @error('company_website')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Company Address:</label>
                                    <textarea class="form-control @error('company_address') is-invalid @enderror" id="company_address"
                                        name="company_address">{{ old('company_address') }}</textarea>
                                    @error('company_address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Shipping Address:</label>
                                    <textarea class="form-control @error('shipping_address') is-invalid @enderror" id="shipping_address"
                                        name="shipping_address">{{ old('shipping_address') }}</textarea>
                                    @error('shipping_address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
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
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="logo">Company Logo:</label> <br>
                            <img src="{{ $client['company_logo'] ?? false ? '/uploaded_files/' . $client['company_logo'] : '' }}"
                                class="w-25">
                            <input type="file" name="company_logo" id="company_logo" accept="image/*"
                                class="form-control-file"
                                onchange="$(this).parent().find('img')[0].src = window.URL.createObjectURL(this.files[0])">
                            @error('company_logo')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            Create Client
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endSection
