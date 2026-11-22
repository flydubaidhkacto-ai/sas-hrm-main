@extends('layouts.master')
@section('title', 'Create Role')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">

            <!-- Page Header -->
            <x-page-header :title="'Roles'" :breadcrumbs="[
                ['name' => 'Roles', 'route' => route('role.index')],
                ['name' => 'Role Create', 'active' => true],
            ]" />


            @include('layouts.alerts')
            <form action="{{ route('role.store') }}" method="post">
                @csrf
                <label for="">Name:</label>
                <input type="text" class="form-control" required name="name" min="1" max="100">
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
                            <input type="checkbox" name="permissions[]" value="{{ $id }}" id="in-{{ $id }}">
                            <label for="in-{{ $id }}" class="ml-2 mt-2">
                                {{ $name }}
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button class="mt-2 btn btn-sm btn-primary" type="submit">
                    Submit
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
