@extends('layouts.master')
@section('title', 'Edit Role')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">Edit Role</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('role.index') }}">Roles</a></li>
                            <li class="breadcrumb-item active">Update Role</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            @include('layouts.alerts')
            <form action="{{ route('role.update', $role) }}" method="post">
                @csrf
                @method('PUT')
                <label for="">Name:</label>
                <input type="text" class="form-control" required name="name" min="1" max="100"
                    value="{{ $role->name }}">
                <br>
                <b>
                    Select Permissions:
                </b>
                <span class="ml-2">
                    <a href="javascript:void(0)" class="btn btn-link"
                        onclick="if($(this).parent().parent().find('input').prop('checked')) {
                            $(this).parent().parent().find('input').prop('checked', false)
                        }else {
                            $(this).parent().parent().find('input').prop('checked', true)
                        }">
                        Select All
                    </a>
                </span>
                <div class="row w-100">
                    @foreach ($permissions as $id => $name)
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <input type="checkbox" name="permissions[]" value="{{ $id }}" id="in-{{ $id }}"
                                @if (in_array($id, $role->permissions)) checked @endif>
                            <label for="in-{{ $id }}" class="ml-2 mt-2">
                                {{ $name }}
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button class="mt-2 btn btn-sm btn-info" type="submit">
                    Update
                </button>
            </form>
        </div>
        <!-- /Page Content -->
    </div>
    <!-- /Page Wrapper -->
@endsection
@section('script')
    <script></script>
@endsection
