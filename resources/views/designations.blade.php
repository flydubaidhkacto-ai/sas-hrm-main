@extends('layouts.master')
@section('title', "Designations")
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
                    <h3 class="page-title">Designations</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Designations</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_designation"><i
                            class="fa fa-plus"></i> Add Designation</a>
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
                                <th>Designation </th>
                                <th style="width: 100px;">Order</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($designations as $key => $designation)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td hidden class="id">{{ $designation->id }}</td>
                                <td class="designation">{{ $designation->name }}</td>
                                <td>
                                    <form action="{{ route('employees.designations.update-order') }}" method="POST" style="display: flex; align-items: center; gap: 5px;">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $designation->id }}">
                                        <input type="number" name="order" value="{{ $designation->order ?? 0 }}" class="form-control order-value" style="width: 70px; height: 30px;" min="0">
                                        <button type="submit" class="btn btn-sm btn-primary" style="height: 30px; line-height: 1;"><i class="fa fa-save"></i></button>
                                    </form>
                                </td>
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
                    {{ $designations->withQueryString()->links() }}
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
                    <h5 class="modal-title">Add Designation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('employees.designations.save') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Designation Name <span class="text-danger">*</span></label>
                            <input name="name" class="form-control" type="text" required max="150" min="3">
                        </div>
                        <div class="form-group">
                            <label>Order <span class="text-danger">*</span></label>
                            <input name="order" class="form-control" type="number" value="0" min="0">
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
                    <h5 class="modal-title">Edit Designation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('employees.designations.update') }}">
                        @csrf
                        <input type="hidden" name="d_id">
                        <div class="form-group">
                            <label>Designation Name <span class="text-danger">*</span></label>
                            <input name="name" class="form-control designation" required min="3" max="150" type="text">
                        </div>
                        <div class="form-group">
                            <label>Order <span class="text-danger">*</span></label>
                            <input name="order" class="form-control designation-order" type="number" min="0">
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
                        <h3>Delete Designation</h3>
                        <p>Are you sure want to delete?</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('employees.designations.delete') }}" method="POST">
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
            $('#edit_designation .designation-order').val(_this.find('.order-value').text().trim());
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