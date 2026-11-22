@extends('layouts.master')
@section('title', 'Task Status')
@section('content')

<style>
    .color-picker {
        display: flex;
        gap: 10px;
        margin-top: 5px;
    }

    .color-box {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
        font-weight: bold;
    }

    .color-box:hover {
        border: 2px solid black;
    }

    .selected {
        border: 3px solid black;
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
                    <h3 class="page-title">
                        Task Status
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Task Status</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_type">
                        <i class="fa fa-plus"></i>
                        Add Status
                    </a>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        <div class="row">
            <div class="col-md-12">
                <div>
                    <table class="table table-striped custom-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 30px;">#</th>
                                <th>Status Name</th>
                                {{-- <th>Status Color</th> --}}
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($statuses as $key => $item)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td hidden class="id">{{ $item->id }}</td>
                                <td hidden class="name">{{ $item->name }}</td>
                                <td hidden class="color">{{ $item->color }}</td>
                                <td class="type">
                                    <span class="badge"
                                        style="background-color: {{ $item->color }}; font-size:15px; color:white;">{{
                                        $item->name }}</span>
                                </td>
                                <td class="text-right">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="material-icons">more_vert</i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item edit_status" href="#" data-toggle="modal"
                                                data-target="#edit_status">
                                                <i class="fa fa-pencil m-r-5"></i> Edit
                                            </a>
                                            <a class="dropdown-item delete_status" href="#" data-toggle="modal"
                                                data-target="#delete_status">
                                                <i class="fa fa-trash-o m-r-5"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- <div class="mt-2">
                    {{ $types->withQueryString()->links() }}
                </div> --}}
            </div>
        </div>
    </div>
    <!-- /Page Content -->

    <!-- Add type Modal -->
    <div id="add_type" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Status</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('task-status.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Status Name<sup class="text-danger">*</sup>:</label>
                            <input class="form-control @error('name') is-invalid @enderror" type="text" id="name"
                                name="name">
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Status Color <sup class="text-danger">*</sup>:</label>
                            <input type="color" class="form-control @error('color') is-invalid @enderror" name="color"
                                required value="{{ old('color') }}">
                            @error('color')
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

    <!-- /Add type Modal -->

    <!-- Edit type Modal -->
    <div id="edit_status" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Status</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('task-status.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" id="e_id" value="">
                        <div class="form-group">
                            <label>Status Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="e_name"
                                name="name" value="">
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Status Color <sup class="text-danger">*</sup>:</label>
                            <input type="color" id="e_color" class="form-control @error('color') is-invalid @enderror"
                                name="color" required value="">
                            @error('color')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit type Modal -->
    <!-- Delete type Modal -->
    <div class="modal custom-modal fade" id="delete_status" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>Delete Status</h3>
                        <p>Are you sure want to delete?</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('task-status.destroy') }}" method="POST">
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
    <!-- /Delete type Modal -->
</div>

<!-- /Page Wrapper -->
@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).on('click', '.edit_status', function() {
            var _this = $(this).parents('tr');

            $('#e_id').val(_this.find('.id').text().trim());
            $('#e_name').val(_this.find('.name').text().trim());
            $('#e_color').val(_this.find('.color').text().trim());
        });

        $(document).on('click', '.delete_status', function() {
            var _this = $(this).parents('tr');
            $('.e_id').val(_this.find('.id').text().trim());
        })
</script>

<script>
    $(document).ready(function() {
            $(".color-box").click(function() {
                $(".color-box").removeClass("selected");
                $(this).addClass("selected");
                let selectedColor = $(this).data("color");
                $("#e_color").val(selectedColor);
            });
        });
</script>
@endsection
@endsection