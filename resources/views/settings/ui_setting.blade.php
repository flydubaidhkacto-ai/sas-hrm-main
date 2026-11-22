
@extends('layouts.master')
@section('title', "Settings: UI")
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            {!! Toastr::message() !!}
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="row">
                            <div class="col-sm-12">
                                <h3 class="page-title">UI Setting</h3>
                            </div>
                        </div>
                    </div>
                    <!-- /Page Header -->
                    @include('layouts.alerts')
                    <form action="{{ route('ui.setting.update') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-2"></div>
                            <div class="col-md-8">
                                <div class="">
                                    <div class="form-group">
                                        <label>Sidebar Background Color: <span class="text-danger"></span></label>
                                        <input type="color" name="sidebar_background_color" class="form-control" value="{{ $info['sidebar_background_color'] ?? old('sidebar_background_color') ?? '' }}">
                                    </div>
                                </div>
        
                                <div class="">
                                    <div class="form-group">
                                        <label>Navbar Background Color: <span class="text-danger"></span></label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="color" name="navbar_background_color_left" class="form-control" value="{{ $info['navbar_background_color_left'] ?? old('navbar_background_color_left') ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="color" name="navbar_background_color_right" class="form-control" value="{{ $info['navbar_background_color_right'] ?? old('navbar_background_color_right') ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
        
                                {{-- <div class="">
                                    <div class="form-group">
                                        <label>Text Color: <span class="text-danger"></span></label>
                                        <input type="color" name="text_color" class="form-control" value="{{ $info['text_color'] ?? old('text_color') ?? '' }}">
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                        
                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Save</button>
                            <a type="submit" href="{{ route('ui.setting.delete') }}" class="btn btn-danger submit-btn">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
