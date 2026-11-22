@extends('layouts.master')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-7">
                        <h3 class="page-title">
                            Performance Category
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Performance Category</li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a class="btn add-btn" onclick="this.nextElementSibling.classList.toggle('d-none');this.remove()" href="#">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                        <form action="{{ route('performance.categories.create') }}" method="post" class="d-none">
                            @csrf
                            <div>
                                <input type="text" name="name" id="" class="form-control" placeholder="Name of the key" required>
                            </div>
                            <div class="mt-1">
                                <input type="number" name="max_value" step="any" class="form-control" placeholder="Max value (default: 10)">
                            </div>
                            <button class="btn btn-info btn-sm mt-1">
                                <i class="fa fa-plus"></i> Create
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @include('layouts.alerts')
            <div class="mt-2">

                {!! Toastr::message() !!}
                @include('layouts.alerts')
                <div class="mt-2">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>S.L</th>
                                    <th>Name</th>
                                    <th>Max Value</th>
                                    <th>Active</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                    <tr>
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            {{ $item->name }}
                                        </td>
                                        <td>
                                            {{ $item->max_value }}
                                        </td>
                                        <td>
                                            @if($item->is_active)
                                                <span class="badge bg-inverse-success">Active</span>
                                            @else
                                                <span class="badge bg-inverse-danger">Inactive</span>
                                            @endif
                                            <br>
                                            <form action="{{ route('performance.categories.status', $item->id) }}" method="POST">
                                                @csrf @method('put')
                                                <button type="submit" class="btn btn-sm {{ $item->is_active ? 'text-danger' : 'btn-success' }}">{{ $item->is_active ? 'Deactivate' : 'Activate' }}</button>
                                            </form>
                                        </td>
                                        <td>
                                            @if($item->from_system)
                                                <span class="badge bg-inverse-dark">System managed</span>
                                            @else
                                                <form action="{{ route('performance.categories.delete', $item->id) }}" method="post"
                                                    id="delete-form-{{ $item->id }}">
                                                    @csrf @method('delete')
                                                </form>
                                                <button  onclick="if(confirm('Are you sure?')) { document.getElementById('delete-form-{{ $item->id }}').submit()}"
                                                    class="btn btn-danger btn-sm">Delete</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <th colspan="6" class="text-center">
                                            No Data Found
                                        </th>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
