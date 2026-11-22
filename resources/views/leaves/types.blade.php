@extends('layouts.master')
@section('title', 'Leave Types')
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
                        Leave Types
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Leave Types</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_type">
                        <i class="fa fa-plus"></i>
                        Add Type
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
                                <th>Name</th>
                                <th>Is Paid</th>
                                <th>Affects Balance</th>
                                <th>Is For All User</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($types as $key => $item)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td hidden class="id">{{ $item->id }}</td>
                                <td hidden class="bg_color" val="{{ $item->bg_color }}"
                                    style="background-color: {{ $item->bg_color }};"></td>
                                <td class="type">
                                    <span class="badge"
                                        style="background-color: {{ $item->bg_color }}; font-size:15px;">{{ $item->name
                                        }}</span>
                                </td>
                                <td class="is_paid" val="{{ $item->is_paid ? 1 : '0' }}">
                                    {{ $item->is_paid ? 'Yes' : 'No' }}
                                </td>
                                <td class="affects_leave_balance" val="{{ $item->affects_leave_balance ? 1 : '0' }}">
                                    {{ $item->affects_leave_balance ? 'Yes' : 'No' }}
                                </td>
                                <td class="is_for_all_user" val="{{ $item->is_for_all_user ? 1 : '0' }}">
                                    {{ $item->is_for_all_user ? 'Yes' : 'No' }}
                                </td>
                                <td class="text-right">
                                    @if ($item->is_editable)
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="material-icons">more_vert</i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item edit_type" href="#" data-toggle="modal"
                                                data-target="#edit_type">
                                                <i class="fa fa-pencil m-r-5"></i> Edit
                                            </a>
                                            <a class="dropdown-item delete_type" href="#" data-toggle="modal"
                                                data-target="#delete_type">
                                                <i class="fa fa-trash-o m-r-5"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                    @else
                                        --
                                    @endif
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
                    <h5 class="modal-title">Add Type</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('leaves.types.save') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input class="form-control @error('name') is-invalid @enderror" type="text" id="name"
                                name="name">
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="">Is Paid?</label>
                            <select class="form-control paid" name="paid">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Affects Leave Balance?</label>
                            <small class="text-muted d-block">If No, this leave type (e.g. Work From Home) will not deduct from leave balance, will not be counted as leave days, and will not affect leave totals.</small>
                            <select class="form-control affects_leave_balance" name="affects_leave_balance">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Is For All User?</label>
                            <select class="form-control is_for_all_user" name="is_for_all_user">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="bg_color">Background Color</label>
                            <div class="color-picker">
                                @foreach (config('services.leave_type_color') as $type => $color)
                                <div class="color-box {{ $loop->first ? 'selected' : '' }}" data-color="{{ $color }}"
                                    style="background-color: {{ $color }};" title="{{ $type }}">
                                </div>
                                @endforeach
                            </div>
                            <input type="hidden" id="bg_color" name="bg_color"
                                value="{{ collect(config('services.leave_type_color'))->first() }}">
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
    <div id="edit_type" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Type</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('leaves.types.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" id="e_id" value="">
                        <div class="form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="type_edit" name="name" value="">
                        </div>
                        <div class="form-group">
                            <label for="">Is Paid?</label>
                            <select class="form-control paid" name="paid">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Affects Leave Balance?</label>
                            <small class="text-muted d-block">If No, this leave type will not deduct from leave balance and will not be counted as leave days.</small>
                            <select class="form-control affects_leave_balance_edit" name="affects_leave_balance">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Is For All User?</label>
                            <select class="form-control is_for_all_user" name="is_for_all_user">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="e_bg_color">Background Color</label>
                            <div class="color-picker">
                                @foreach (config('services.leave_type_color') as $type => $color)
                                <div class="color-box" data-color="{{ $color }}" style="background-color: {{ $color }};"
                                    title="{{ $type }}">
                                </div>
                                @endforeach
                            </div>
                            <input type="hidden" id="e_bg_color" name="bg_color" value="">
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
    <div class="modal custom-modal fade" id="delete_type" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>Delete Type</h3>
                        <p>Are you sure want to delete?</p>
                    </div>
                    <div class="modal-btn delete-action">
                        <form action="{{ route('leaves.types.delete') }}" method="POST">
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
    $(document).on('click', '.edit_type', function() {
            var _this = $(this).parents('tr');

            $('#e_id').val(_this.find('.id').text().trim());
            $('#type_edit').val(_this.find('.type').text().trim());
            $('#edit_type select.paid').prop('value', _this.find('.is_paid').attr('val').trim());
            $('#edit_type select.affects_leave_balance_edit').prop('value', _this.find('.affects_leave_balance').attr('val').trim());
            $('#edit_type select.is_for_all_user').prop('value', _this.find('.is_for_all_user').attr('val').trim());


            let bgColor = _this.find('.bg_color').attr('val').trim();
            $('#e_bg_color').val(bgColor);

            $('.color-box').removeClass('selected');

            $('.color-box').each(function() {
                if ($(this).attr('data-color') === bgColor) {
                    $(this).addClass('selected');
                }
            });
        });


        $(document).on('click', '.color-box', function() {
            $('.color-box').removeClass('selected');
            $(this).addClass('selected');
            $('#e_bg_color').val($(this).attr('data-color'));
        });

        $(document).on('click', '.delete_type', function() {
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
                $("#bg_color").val(selectedColor);
            });
        });
</script>
@endsection
@endsection
