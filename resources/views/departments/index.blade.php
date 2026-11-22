@extends('layouts.master')
@section('title', "Super Department")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Super Department</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Super Department</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_department"><i
                            class="fa fa-plus"></i> Add Super Department</a>
                </div>
            </div>
        </div>

        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="row">
            <div class="col-md-12">
                <div>
                    <table class="table table-striped custom-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 30px;">#</th>
                                    <th>Department Name</th>
                                    <th style="width: 100px;">Order</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($departments as $key => $item )
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td hidden class="id">{{ $item->id }}</td>
                                    <td hidden class="order">{{ $item->order ?? 0 }}</td>
                                    <td class="department">
                                        {{-- <a href="{{ route('departments.departments', $item->id) }}"> --}}
                                            {{ $item->name }}
                                            {{-- </a> --}}
                                    </td>
                                    <td>
                                        <form action="{{ route('departments.super-department.update-order') }}" method="POST" style="display: flex; align-items: center; gap: 5px;">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $item->id }}">
                                            <input type="number" name="order" value="{{ $item->order ?? 0 }}" class="form-control super-department-order" style="width: 70px; height: 30px;" min="0">
                                            <button type="submit" class="btn btn-sm btn-primary" style="height: 30px; line-height: 1;"><i class="fa fa-save"></i></button>
                                        </form>
                                    </td>
                                    <td class="text-right">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="material-icons">more_vert</i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item edit_department" href="#" data-toggle="modal"
                                                data-target="#edit_department">
                                                <i class="fa fa-pencil m-r-5"></i> Edit
                                            </a>
                                            <a class="dropdown-item delete_department" href="#" data-toggle="modal"
                                                data-target="#delete_department">
                                                <i class="fa fa-trash-o m-r-5"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <table class="table" style="max-width: 600px; margin-left: 60px;">
                                        {{-- <tr>
                                            <th>
                                                #
                                            </th>
                                            <th>
                                                Name
                                            </th>
                                            <th>
                                                Action
                                            </th>
                                        </tr> --}}
                                        {{-- <tr>
                                            <th>
                                                #
                                            </th>
                                            <th>
                                                Name
                                            </th>
                                            <th>
                                                Order
                                            </th>
                                            <th>
                                                Action
                                            </th>
                                        </tr> --}}
                                        @forelse ($item->departments as $key => $dep)
                                        <tr _id="{{ $dep->id }}">
                                            <td style="width: 30px;">{{ $key + 1 }}</td>
                                            <td hidden class="id">{{ $dep->id }}</td>
                                            <td hidden class="order">{{ $dep->order ?? 0 }}</td>
                                            <td class="department">
                                                {{ $dep->name }}
                                            </td>
                                            <td>
                                                <form action="{{ route('departments.update-order') }}" method="POST" style="display: flex; align-items: center; gap: 5px;">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $dep->id }}">
                                                    <input type="number" name="order" value="{{ $dep->order ?? 0 }}" class="form-control sub-department-order" style="width: 70px; height: 30px;" min="0">
                                                    <button type="submit" class="btn btn-sm btn-primary" style="height: 30px; line-height: 1;"><i class="fa fa-save"></i></button>
                                                </form>
                                            </td>
                                            <td class="text-right">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle"
                                                        data-toggle="dropdown" aria-expanded="false">
                                                        <i class="material-icons">more_vert</i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item edit_sub_department" href="#"
                                                            data-toggle="modal" data-target="#edit_sub_department">
                                                            <i class="fa fa-pencil m-r-5"></i> Edit
                                                        </a>
                                                        <a class="dropdown-item delete_sub_department" href="#"
                                                            data-toggle="modal" data-target="#delete_sub_department">
                                                            <i class="fa fa-trash-o m-r-5"></i> Delete
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3">
                                                No Department added
                                            </td>
                                        </tr>
                                        @endforelse
                                    </table>
                                    <a _id="{{ $item->id }}" class="btn btn-info btn-sm add_sub_department" href="#"
                                        data-toggle="modal" data-target="#add_sub_department">
                                        <i class="fa fa-plus"></i> Create
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Pagination removed - using full collection sort by order --}}
            </div>
        </div>
    </div>
    <!-- /Page Content -->

    <!-- Add Department Modal -->
    <div id="add_department" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Super Department</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('departments.save') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Department Name <span class="text-danger">*</span></label>
                            <input class="form-control @error('name') is-invalid @enderror" type="text" id="name"
                                name="name">
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Order <span class="text-danger">*</span></label>
                            <input class="form-control" type="number" name="order" value="0" min="0">
                        </div>
                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Department Modal -->


    <!-- Edit Department Modal -->
    <div id="edit_department" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Department</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('departments.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" id="e_id" value="">
                        <div class="form-group">
                            <label>Department Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="department_edit" name="name" value="">
                        </div>
                        <div class="form-group">
                            <label>Order</label>
                            <input type="number" class="form-control" id="department_edit_order" name="order" value="0" min="0">
                        </div>
                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit Department Modal -->
    <!-- Delete Department Modal -->
    <div class="modal custom-modal fade" id="delete_department" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>Delete Department</h3>
                        <p>Are you sure want to delete?</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('departments.delete') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="id" class="e_id" value="">
                            <div class="row">
                                <div class="col-6">
                                    <button type="submit"
                                        class="btn btn-primary continue-btn submit-btn">Delete</button>
                                </div>
                                <div class="col-6">
                                    <a href="javascript:void(0);" data-dismiss="modal"
                                        class="btn btn-primary cancel-btn">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete Department Modal -->


    <!-- Add Department Modal -->
    <div id="add_sub_department" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Department</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('departments.department.save') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" id="id" value="">
                        <div class="form-group">
                            <label>Department Name <span class="text-danger">*</span></label>
                            <input class="form-control @error('name') is-invalid @enderror" type="text" id="name"
                                name="name">
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Order</label>
                            <input class="form-control" type="number" name="order" value="0" min="0">
                        </div>
                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Department Modal -->

    <!-- Edit Department Modal -->
    <div id="edit_sub_department" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Department</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('departments.department.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" id="e_id" value="">
                        <div class="form-group">
                            <label>Department Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="department_edit" name="name" value="">
                        </div>
                        <div class="form-group">
                            <label>Order</label>
                            <input type="number" class="form-control" id="department_edit_order_sub" name="order" value="0" min="0">
                        </div>
                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit Department Modal -->
    <!-- Delete Department Modal -->
    <div class="modal custom-modal fade" id="delete_sub_department" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>Delete Department</h3>
                        <p>Are you sure want to delete?</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('departments.department.delete') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="id" class="e_id" value="">
                            <div class="row">
                                <div class="col-6">
                                    <button type="submit"
                                        class="btn btn-primary continue-btn submit-btn">Delete</button>
                                </div>
                                <div class="col-6">
                                    <a href="javascript:void(0);" data-dismiss="modal"
                                        class="btn btn-primary cancel-btn">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete Department Modal -->

</div>
<!-- /Page Wrapper -->
@section('script')
<script>
    $(document).on('click', '.edit_department',function()
        {
            var _this = $(this).parents('tr');
            $('#edit_department #e_id').val(_this.find('.id').text().trim());
            $('#edit_department #department_edit').val(_this.find('.department').text().trim());
            $('#edit_department #department_edit_order').val(_this.find('.order').text().trim());
        });
        $(document).on('click', '.delete_department', function() {
            var _this = $(this).parents('tr');
            $('#delete_department .e_id').val(_this.find('.id').text().trim());
        })
        $(document).on('click', '.add_sub_department',function()
        {
            $('#add_sub_department #id').val($(this).attr('_id'));
        })
        $(document).on('click', '.edit_sub_department',function()
        {
            var _this = $(this).parent().parent().parent().parent();
            console.log(_this.find('.department'));
            $('#edit_sub_department #e_id').val(_this.attr('_id'));
            $('#edit_sub_department input[name=name]').val(_this.find('.department').text().trim());
            $('#edit_sub_department #department_edit_order_sub').val(_this.find('.order').text().trim());
        });
        $(document).on('click', '.delete_sub_department', function() {
            var _this = $(this).parent().parent().parent().parent();
            $('#delete_sub_department .e_id').val(_this.attr('_id'));
        })
</script>
@endsection
@endsection