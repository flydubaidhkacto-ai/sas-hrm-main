@extends('layouts.master')
@section('title', 'Role')
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
                        <h3 class="page-title">Roles</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Roles</li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a href="{{ route('role.create') }}" class="btn add-btn">
                            <i class="fa fa-plus"></i> Add Role
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            @include('layouts.alerts')
            <table class="table">
                <tr>
                    <th>
                        Name
                    </th>
                    <th>
                        Permissions
                    </th>
                    <th>
                        Action
                    </th>
                </tr>
                @forelse ($roles as $item)
                    <tr>
                        <td>
                            <div style="width: 200px">
                                {{ $item->name }}
                                ({{ $item->users()->count() }})
                            </div>
                        </td>
                        <td>
                            @if (count($item->permissions ?: []) === count(\App\Models\Role::$permissions))
                                <i class="text-info">
                                    All Permissions
                                </i>
                            @else
                            <div class="row">
                                @foreach ($item->permissions as $permission)
                                    <div class="col-md-3 col-sm-6">
                                        - {{ \App\Models\Role::$permissions[$permission] ?? '' }}
                                    </div>
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td>
                            <div style="width: 100px">
                            <a href="{{ route('role.edit', $item) }}" class="btn btn-sm btn-info">
                                <i class="fa fa-edit"></i>
                                Edit
                            </a>
                            <form action="{{ route('role.destroy', $item) }}" method="post" class="d-inline-block">
                                <button class="btn btn-sm btn-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                                @csrf
                                @method('delete')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            No role Found!
                        </td>
                    </tr>
                @endforelse
            </table>
        </div>
        <!-- /Page Content -->
    </div>
    <!-- /Page Wrapper -->
@endsection
@section('script')
    <script></script>
@endsection
