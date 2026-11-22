@extends('layouts.master')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Change Password</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Change Password</li>
                        </ul>
                    </div>
                </div>
            </div>
            {!! Toastr::message() !!}
            @if(config('app.is_demo'))
                <div class="alert alert-danger">
                    This feature is disabled in demo version.
                </div>
            @endif
			<!-- /Page Header -->
            <form action="" method="POST" style="max-width: 500px">
                @csrf
                <div class="form-group">
                    <div class="row">
                        <div class="col">
                            <label>
                                Current Password
                            </label>
                            <a href="#" class="float-right" data-toggle-password="true" data-target="[name=current_password]">
                                Show
                            </a>
                        </div>
                    </div>
                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" placeholder="Enter current password">
                    @error('current_password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col">
                            <label>
                                New Password
                            </label>
                            <a href="#" class="float-right" data-toggle-password="true" data-target="[name=new_password]">
                                Show
                            </a>
                        </div>
                    </div>
                    <input type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" placeholder="Enter new password">
                    @error('new_password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col">
                            <label>
                                Confirm New Password
                            </label>
                            <a href="#" class="float-right" data-toggle-password="true" data-target="[name=confirm_new_password]">
                                Show
                            </a>
                        </div>
                    </div>
                    <input type="password" class="form-control @error('confirm_new_password') is-invalid @enderror" name="confirm_new_password" placeholder="Enter new password again!">
                    @error('confirm_new_password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <button class="btn btn-primary" type="submit">
                        Change password
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
