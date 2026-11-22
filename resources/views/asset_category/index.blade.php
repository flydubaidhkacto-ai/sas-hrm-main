@extends('layouts.master')
@section('title', "Asset Category")
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">
                            Asset Category
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                            <li class="breadcrumb-item active">
                                Asset Category
                            </li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        {{-- <a href="{{ route('grade_system.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add New</a> --}}
                        <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_modal"><i class="fa fa-plus"></i> Add New</a>
                    </div>
                </div>
            </div>

            <!-- /Page Header -->
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <div class="">
                <div class="">
                    <div>
                        <table class="table table-striped custom-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 30px;">#</th>
                                    <th>Name</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $key => $item )
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td hidden class="id">{{ $item->id }}</td>
                                    <td class="asset_category_name">
                                        {{ $item->name }}
                                    </td>
                                    <td class="text-right">
                                        <div class="dropdown dropdown-action">
                                            <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                <i class="material-icons">more_vert</i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">

                                                <a class="dropdown-item edit_category" href="#" data-toggle="modal" data-target="#edit_category">
                                                    <i class="fa fa-pencil m-r-5"></i> Edit
                                                </a>

                                                <a class="dropdown-item"
                                                    onclick="
                                                    if(confirm('Are you sure?'))
                                                    {
                                                        $(this).next().submit()
                                                    }
                                                    "
                                                >
                                                    <i class="fa fa-trash-o m-r-5"></i> Delete
                                                </a>
                                                <form method="POST" action="{{ route('asset_category.delete', $item) }}" class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="add_modal" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Asset Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('asset_category.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Asset Category Name <span class="text-danger">*</span></label>
                            <input class="form-control @error('name') is-invalid @enderror" type="text" id="name" name="name">
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Modal -->

     <!-- Edit Modal -->
    <div id="edit_category" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Asset Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('asset_category.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" id="e_id" value="">
                        <div class="form-group">
                            <label>Department Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="category_edit" name="name" value="">
                        </div>
                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit Modal -->
@endsection
@section('script')
<script>
    $(document).on('click', '.edit_category',function()
    {
        var _this = $(this).parents('tr');
        $('#edit_category #e_id').val(_this.find('.id').text().trim());
        $('#edit_category #category_edit').val(_this.find('.asset_category_name').text().trim());
    });
</script>
@endsection
