@extends('layouts.master')
@section('title', 'Create Approval Process')
@section('content')
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Create Approval Process</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('approval-processes.index') }}">Approval
                                    Processes</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ul>
                    </div>
                </div>
            </div>
            {!! Toastr::message() !!}
            @include('layouts.alerts')

            <form action="{{ route('approval-processes.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="name" class="form-label">Process Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="name" class="form-label">Type</label>
                        <select name="type[]" id="type" class="form-control select2" multiple required>
                            @foreach (\App\Models\ApprovalProcess::$types as $type)
                                <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="for_users" class="form-label">Assign to Users</label>
                    <select id="for_users" name="for_users[]" class="form-control select2" multiple>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Create</button>
            </form>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            // $('select[multiple]').select2({})
            $('.select2').select2({
                placeholder: "Select",
                allowClear: true
            });
        });
    </script>
@endsection
