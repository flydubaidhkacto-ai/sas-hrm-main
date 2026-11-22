@extends('layouts.app')
@section('content')
<div class="main-wrapper">
    <div class="account-content">
        <div class="container">
            <!-- Account Logo -->
            <div class="account-logo">
                <a href="{{ route('home') }}"><img src="{{ ($info['logo'] ?? false) ? " /uploaded_files/" .
                        $info['logo'] : "https://app.prohrbd.com/assets/img/logo.jpg" }}" alt=""></a>
            </div>
            <!-- /Account Logo -->
            <div class="account-box">
                <div class="account-wrapper">
                    <h3 class="account-title">Register</h3>
                    <p class="account-subtitle">Access to our dashboard</p>

                    <!-- Account Form -->
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                                value="{{ old('name') }}" placeholder="Enter Your Name">
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                                value="{{ old('email') }}" placeholder="Enter Your Email">
                            @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        {{-- insert defaults --}}
                        <input type="hidden" class="image" name="image" value="photo_defaults.jpg">

                        <div class="form-group">
                            <label>
                                Password
                                <a href="#" class="float-right" data-toggle-password="true"
                                    data-target="[name=password]">
                                    Show
                                </a>
                            </label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                name="password" placeholder="Enter Password">
                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>
                                <strong>Repeat Password</strong>
                                <a href="#" class="float-right" data-toggle-password="true"
                                    data-target="[name=password]">
                                    Show
                                </a>
                            </label>
                            <input type="password" class="form-control" name="password_confirmation"
                                placeholder="Choose Repeat Password">
                        </div>
                        <div class="form-group text-center">
                            <button class="btn btn-primary account-btn" type="submit">Register</button>
                        </div>
                        <div class="account-footer">
                            <p>Already have an account? <a href="{{ route('login') }}">Login</a></p>
                        </div>
                    </form>
                    <!-- /Account Form -->
                </div>
            </div>
            <div class="text-center mt-5">
                Developed by <a href="https://www.sasflydubai.com/" target="_blank">SAS HRM System</a>
                {{-- show version --}}
                @php
                echo "v" . config('app.version');
                @endphp
            </div>
        </div>
    </div>
</div>
@endsection