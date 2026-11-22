@extends('layouts.master')
@section('title', "Emp. Expense Category")
@section('content')
<style>
    .designation {
        color: unset;
        font-size: unset;
    }
</style>
<!-- Page Wrapper -->
<div class="page-wrapper">

    <!-- Page Content -->
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Emp. Expense Category</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Emp. Expense Category</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="{{ route('emp-expenses.category.create') }}" class="btn add-btn"><i class="fa fa-plus"></i>
                        Add Expense Category</a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped custom-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 30px;">#</th>
                                <th>Name </th>
                                <th>Status </th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($empExpenseCategories as $key => $empExpenseCategory)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>{{ $empExpenseCategory->name }}</td>
                                <td>
                                    @if($empExpenseCategory->status === 1)
                                    <span class="badge badge-success">Active</span>
                                    @else
                                    <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('emp-expenses.category.edit', $empExpenseCategory->id) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="fa fa-pencil m-r-5"></i> Edit
                                    </a>
                                    <form action="{{ route('emp-expenses.category.destroy', $empExpenseCategory->id) }}"
                                        method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this category?')">
                                            <i class="fa fa-trash m-r-5"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    {{ $empExpenseCategories->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection