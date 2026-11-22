@extends('layouts.master')
@section('title', "Bonus Category")
@section('content')
<style>
    .designation {
        color: unset;
        font-size: unset;
    }
</style>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Bonus Category</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Bonus Category</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_designation"><i
                            class="fa fa-plus"></i> Add Category</a>
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
                                <th>Category </th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $key => $category)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td hidden class="id">{{ $category->id }}</td>
                                <td class="designation">{{ $category->name }}</td>
                                <td class="text-right">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown"
                                            aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item edit_designation" href="#" data-toggle="modal"
                                                data-target="#edit_designation"><i class="fa fa-pencil m-r-5"></i>
                                                Edit</a>
                                            <a class="dropdown-item delete_designation" href="#" data-toggle="modal"
                                                data-target="#delete_designation"><i class="fa fa-trash-o m-r-5"></i>
                                                Delete</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    {{ $categories->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->

    <!-- Add Designation Modal -->
    <div id="add_designation" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('bonuses.category.save') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Category Name <span class="text-danger">*</span></label>
                            <input name="name" class="form-control" type="text" required max="150" min="3">
                        </div>
                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Designation Modal -->

    <!-- Edit Designation Modal -->
    <div id="edit_designation" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('bonuses.category.update') }}">
                        @csrf
                        <input type="hidden" name="d_id">
                        <div class="form-group">
                            <label>Category Name <span class="text-danger">*</span></label>
                            <input name="name" class="form-control designation" required min="3" max="150" type="text">
                        </div>
                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit Designation Modal -->

    <!-- Delete Designation Modal -->
    <div class="modal custom-modal fade" id="delete_designation" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>Delete Category</h3>
                        <p>Are you sure want to delete?</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('bonuses.category.delete') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="id" class="d_id" value="">
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
    <!-- /Delete Designation Modal -->

</div>
<!-- /Page Wrapper -->

<!-- /Page Wrapper -->
@section('script')
{{-- update js --}}
<script>
    $(document).on('click','.edit_designation',function()
        {
            var _this = $(this).parents('tr');
            $('#edit_designation [name=d_id]').val(_this.find('.id').text().trim());
            $('#edit_designation .designation').val(_this.find('.designation').text());
        });
</script>
{{-- delete model --}}
<script>
    $(document).on('click','.delete_designation',function()
        {
            var _this = $(this).parents('tr');
            console.log(_this.find('.id').text().trim());
            $('#delete_designation .d_id').val(_this.find('.id').text().trim());
        });
</script>
@endsection
@endsection