@extends('layouts.master')
@section('title', 'BackUp List')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="page-title">
                            BackUp
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('backup.index') }}">BackUp</a></li>
                            <li class="breadcrumb-item active">BackUp List</li>
                        </ul>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('backup.create') }}" class="btn btn-primary">Create Backup</a>
                    </div>
                </div>
            </div>
            @include('layouts.alerts')
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table">
                            <thead>
                                <tr>

                                    <th>Backup Files</th>
                                    <th class="text-right no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($files as $key => $file)
                                    <tr>
                                        <td>{{ $file }}</td>

                                        <td class="text-right">
                                            <div class="dropdown dropdown-action">
                                                <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown"
                                                    aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                                <div class="dropdown-menu dropdown-menu-right">

                                                    <a class="dropdown-item"
                                                        href="{{ route('backup.download', $file) }}">
                                                        <i class="fa fa-download m-r-5"></i> Download
                                                    </a>
                                                    <form action="{{ route('backup.destroy', $file) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="dropdown-item"
                                                            onclick="return confirm('Are you sure you want to delete this item?')">
                                                            <i class="fa fa-trash m-r-5"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
