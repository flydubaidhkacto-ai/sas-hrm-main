@extends('layouts.master')
@section('title', "Classes")
@section('content')
<style>
    .class {
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
                    <h3 class="page-title">Classes</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Classes</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_class"><i
                            class="fa fa-plus"></i> Add Class</a>
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
                                <th>Class Name</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($classes as $key => $class)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td hidden class="id">{{ $class->id }}</td>
                                <td class="class">{{ $class->name }}</td>
                                <td class="text-right">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown"
                                            aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item edit_class" href="#" data-toggle="modal"
                                                data-target="#edit_class"><i class="fa fa-pencil m-r-5"></i>
                                                Edit</a>
                                            <a class="dropdown-item delete_class" href="#" data-toggle="modal"
                                                data-target="#delete_class"><i class="fa fa-trash-o m-r-5"></i>
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
                    {{ $classes->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->

    <!-- Add Class Modal -->
    <div id="add_class" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Class</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('classes.save') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Class Name <span class="text-danger">*</span></label>
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
    <!-- /Add Class Modal -->

    <!-- Edit Class Modal -->
    <div id="edit_class" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Class</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('classes.update') }}">
                        @csrf
                        <input type="hidden" name="c_id">
                        <div class="form-group">
                            <label>Class Name <span class="text-danger">*</span></label>
                            <input name="name" class="form-control class" required min="3" max="150" type="text">
                        </div>
                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit Class Modal -->

    <!-- Delete Class Modal -->
    <div class="modal custom-modal fade" id="delete_class" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>Delete Class</h3>
                        <p>Are you sure want to delete?</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('classes.delete') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="id" class="c_id" value="">
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
    <!-- /Delete Class Modal -->

</div>
<!-- /Page Wrapper -->

<!-- /Page Wrapper -->
@section('script')
{{-- update js --}}
<script>
    $(document).on('click','.edit_class',function()
        {
            var _this = $(this).parents('tr');
            $('#edit_class [name=c_id]').val(_this.find('.id').text().trim());
            $('#edit_class .class').val(_this.find('.class').text());
        });
</script>
{{-- delete model --}}
<script>
    $(document).on('click','.delete_class',function()
        {
            var _this = $(this).parents('tr');
            console.log(_this.find('.id').text().trim());
            $('#delete_class .c_id').val(_this.find('.id').text().trim());
        });
</script>
@endsection
@endsection
