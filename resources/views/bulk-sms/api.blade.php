@extends('layouts.master')
@section('title')
    SMS API
@endsection
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                {!! Toastr::message() !!}
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">SMS Api</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item active">Set SMS API</li>
                        </ul>
                    </div>

                </div>
            </div>
            @include('layouts.alerts')
            <div>
                {{-- bootstrap tabs --}}
                <nav class="nav nav-tabs">
                    <a href="#default-api" data-toggle="tab" data-target="#default-api" type="button" role="tab"
                        class="nav-link @if(!$use_custom_api) active @endif">ProHR API</a>
                    <a href="#custom-api" data-toggle="tab" data-target="#custom-api" type="button" role="tab"
                        class="nav-link @if($use_custom_api) active @endif">Custom API</a>
                    <a href="#test-sms" data-toggle="tab" data-target="#test-sms" type="button" role="tab"
                        class="nav-link">
                        Send Test SMS
                    </a>
                </nav>
                <div class="tab-content">
                    <div id="default-api" class="tab-pane fade @if(!$use_custom_api) show active @endif">
                        <div style="display: flex; align-items: center;" class="">
                            <b>
                                Use ProHR SMS API:
                            </b>
                            &nbsp;
                            &nbsp;
                            {{-- place toggle here --}}
                            <form action="{{ route('sms.api.save') }}"
                                method="POST"
                                style="display: inline-block; height: 30px;"
                                class="text-left"
                            >
                                @csrf
                                <input type="hidden" name="use_custom_api" value="1">
                                <label class="switch">
                                    <input type="checkbox" name="val"
                                        onchange="this.form.submit()"
                                        @if(!$use_custom_api) checked @endif
                                    >
                                    <span class="slider round"></span>
                                </label>
                            </form>
                        </div>
                        <div class="mt-3">
                            <p>
                                <b>
                                    Note:
                                </b>
                                If you are using ProHR API, you don't need to set custom API.
                            </p>
                        </div>
                    </div>
                    <div id="custom-api" class="tab-pane fade @if($use_custom_api) show active @endif">
                        @if (!$use_custom_api)
                            <div class="alert alert-warning">
                                Please disable ProHR API to use custom API.
                            </div>
                        @endif
                        <div class="row">
                            <form action="{{ route('sms.api.save') }}" method="POST" class="col-md-6">
                                @csrf
                                <b>
                                    Set your SMS API here.
                                </b>
                                <div>
                                    <label for="">Request URL:</label>
                                    <input type="text" name="url" value="{{ $api['url'] ?? '' }}" class="form-control">
                                </div>
                                <div class="mt-3">
                                    <label for="">Request Type:</label>
                                    <select name="type" id="" class="form-control">
                                        <option @if($api['type'] === 'GET') selected @endif value="GET">GET</option>
                                        <option @if($api['type'] === 'POST') selected @endif value="POST">POST</option>
                                    </select>
                                </div>
                                <div class="mt-3">
                                    <label for="">Header Items:</label>
                                    <div class="items">
                                        @foreach ($api['headers'] ?? [] as $key => $item)
                                        <div class="d-flex mt-2">
                                            <input type="text" name="header_keys[]" value="{{ $key }}" class="form-control" placeholder="Key">
                                            <input type="text" name="header_values[]" value="{{ $item }}" class="form-control" placeholder="Value">
                                            <button class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                        @endforeach
                                    </div>
                                    <button onclick="headerItem(this)" type="button" class="btn btn-primary btn-sm mt-2">
                                        <i class="fa fa-plus"></i>
                                        Add item
                                    </button>
                                </div>
                                <div class="mt-3">
                                    <label for="">Request Body in JSON:</label>
                                    <div class="items">
                                        @foreach ($api['body'] ?? [] as $key => $item)
                                        <div class="d-flex mt-2">
                                            <input type="text" name="body_keys[]" value="{{ $key }}" class="form-control" placeholder="Key">
                                            <input type="text" name="body_values[]" value="{{ $item }}" class="form-control" placeholder="Value">
                                            <button class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                        @endforeach
                                    </div>
                                    <button onclick="bodyItem(this)" type="button" class="btn btn-primary btn-sm mt-2">
                                        <i class="fa fa-plus"></i>
                                        Add item
                                    </button>
                                </div>
                                <button class="mt-3 btn btn-info" type="submit">
                                    <i class="fa fa-check"></i>
                                    Save API Settings
                                </button>
                            </form>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">Variables</div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            [Phone] = Target Phone Number. (Example: 01711111111),<br>
                                            [Message] = Message. (Example: Hello World.)
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="test-sms" class="tab-pane fade">
                        <div class="row">
                            <div class="card card-body" style="max-width: 340px">
                                <h3>
                                    Test SMS:
                                </h3>
                                <form class="" action="">
                                    <input type="text" style="width: 300px" class="form-control" name="send_phone" placeholder="017xxxxxxx" required min="11" max="11">
                                    <button class="btn btn-info btn-sm ml-2 mt-2">
                                        Send test
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            // $(document).;
        });
        function headerItem(btn) {
            $(btn).prev().append(`
            <div class="d-flex mt-2">
                <input type="text" name="header_keys[]" class="form-control" placeholder="Key">
                <input type="text" name="header_values[]" class="form-control" placeholder="Value">
                <button class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            `)
        }
        function bodyItem(btn) {
            $(btn).prev().append(`
            <div class="d-flex mt-2">
                <input type="text" name="body_keys[]" class="form-control" placeholder="Key">
                <input type="text" name="body_values[]" class="form-control" placeholder="Value">
                <button class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            `)
        }
        function preview() {

        }
    </script>
@endsection

