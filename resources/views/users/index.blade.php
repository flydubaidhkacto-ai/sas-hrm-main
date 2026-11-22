@extends('layouts.master')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Users</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Users</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="{{ route('employees.active.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add
                        User</a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Search Filter -->
        <form action="{{ route('employees.active.list') }}" method="get" class="mb-3">
            <div class="row filter-row">
                <div class="col-sm-6 col-md-3">
                    <label for="">Name</label>
                    <select name="employee" id="" class="form-control employee-select2">
                        @if(request()->employee)
                        <option value="{{ request()->employee }}" selected>
                            {{ \App\Models\User::find(request()->employee)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="col-12 mt-2">
                    <button type="submit" class="btn btn-success btn-info"> Search </button>
                </div>
            </div>
        </form>
        <!-- Search Filter -->
        {{-- message --}}
        {!! Toastr::message() !!}

        <div class="row">
            <div class="col-md-12">
                <div class="exports">
                    @include('layouts.exports')
                </div>
                <div class="table-responsive">
                    <table class="table table-striped custom-table">
                        <thead>
                            <tr>
                                <th style="width: 20px; height: 20px">
                                    Code
                                </th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Sup. Department</th>
                                <th>Designation</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $item )
                            <tr>
                                <td style="width: 20px; height: 20px">
                                    {{$item->id}}
                                </td>
                                <td>
                                    <h2 class="table-avatar">
                                        <a href="{{ route('users.show', $item) }}">
                                            {{ $item->name }}
                                        </a>
                                    </h2>
                                </td>
                                <td>{{ $item->department?->name }}</td>
                                <td>{{ $item->department?->super_department?->name }}</td>
                                <td>{{ $item->designation?->name }}</td>
                                <td>{{ $item->phone_number }}</td>
                                <td>
                                    {{ $item->email }}
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('users.show', $item) }}" class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $item) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <button
                                        onclick="if(confirm('Are you sure you want to delete this user?')) $(this).closest('form').submit()"
                                        class="btn btn-sm btn-danger">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    @if($item->role !== 'employee')
                                    <form class="d-inline-block" {{-- action="{{ route('users.destroy', $item) }}" --}}
                                        method="post">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-2">
                        {{ $users->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->

</div>
<!-- /Page Wrapper -->
@endsection
@section('script')
<script>
</script>
@endsection