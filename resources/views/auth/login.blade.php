@extends('layouts.app')
@section('content')
@php
$ui_setting_info = \App\Models\Setting::where('key', 'ui_setting')->first();

$ui_info = $ui_setting_info ? json_decode($ui_setting_info->value, true) : [];

$navbarColors = $ui_info['navbar_background_color'] ?? '';
// $text_color = $ui_info['text_color'] ?? '';
$sidebar_color_left = $ui_info['navbar_background_color_left'] ?? 'rgb(244, 59, 72) 0%';
$sidebar_color_right = $ui_info['navbar_background_color_right'] ?? 'rgb(69, 58, 148) 100%';
$gradientColors = "$sidebar_color_left, $sidebar_color_right";
@endphp
<style>
    .account-btn {
        background: linear-gradient(to right, {
                {
                $gradientColors
            }
        }) !important;
    }
</style>

<div class="main-wrapper">
    <div class="account-content">
        <div class="container">
            <!-- Account Logo -->
            <div class="account-logo">
                <a href="{{ route('home') }}"><img src="{{ ($company_info['logo'] ?? false) ? " /uploaded_files/" .
                        $company_info['logo'] : "https://app.prohrbd.com/assets/img/logo.jpg" }}" alt=""></a>
            </div>
            {{-- message --}}
            {{-- @dump(session()->all(), user()) --}}
            {!! Toastr::message() !!}
            <!-- /Account Logo -->
            <div class="account-box">
                <div class="account-wrapper">
                    <h3 class="account-title">Login</h3>
                    <p class="account-subtitle">Access to our dashboard</p>
                    <!-- Account Form -->
                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf
                        <div class="form-group">
                            <label>Email/Phone</label>
                            <input type="text" class="form-control @error('email') is-invalid @enderror" name="email"
                                value="{{ old('email') }}" placeholder="Enter Email/Phone">
                            @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col">
                                    <label>
                                        Password
                                    </label>
                                    <a href="#" class="float-right" data-toggle-password="true"
                                        data-target="[name=password]">
                                        Show
                                    </a>
                                </div>
                            </div>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                name="password" placeholder="Enter Password">
                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col">
                                    <label></label>
                                </div>
                                <div class="col-auto">
                                    <a class="text-muted" href="{{ route('forget-password') }}">
                                        Forgot password?
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="form-group text-center">
                            <button class="btn btn-primary account-btn" type="submit">Login</button>
                        </div>
                        @if(!\App\Models\User::count())
                        <div class="account-footer">
                            <p>Don't have an account yet? <a href="{{ route('register') }}">Register</a></p>
                        </div>
                        @endif
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