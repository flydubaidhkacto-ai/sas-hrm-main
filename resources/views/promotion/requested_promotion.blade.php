@extends('layouts.master')
@section('title', "Requested Promotion")
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                {!! Toastr::message() !!}
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Promotion
                        </h3>
                        <ul class="breadcrumb">

                            <li class="breadcrumb-item active">Requested Promotion</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="table-responsive">

                <table class="table-striped custom-table datatable table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Employee Name</th>
                            <th>Promoted Designation</th>
                            <th>If change Department</th>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($promotions as $key => $items)
                            <tr class="holiday-upcoming">
                                <td>{{ ++$key }}</td>
                                <td>{{ $items->employee->name }}</td>
                                <td>{{ $items->newDesignation ? $items->newDesignation->name : '-' }}</td>
                                <td>{{ $items->newDepartment ? $items->newDepartment->name : '-' }}</td>
                                <td>
                                    @if ($items->second_status == 2 || $items->status == 2)
                                        Rejected
                                    @elseif($items->second_status == null)
                                        Pending
                                    @elseif($items->second_status == 1)
                                        Approved
                                    @endif

                                </td>

                                <td class="text-right">
                                    @if ($items->status == null)
                                        <a href="#"class="btn btn-danger btn-sm active" data-id="{{ $items->id }}"
                                            data-toggle="modal" data-target="#edit_promotion" role="button"
                                            aria-pressed="true"><i class="fa fa-ban" aria-hidden="true"></i>Delete</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal custom-modal fade" id="edit_promotion" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>Delete Holiday</h3>
                        <p>Are you sure want to delete?</p>
                    </div>
                    <form class="modal-btn delete-action" action="{{ route('promotion.delete') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $items->id }}" class="d_id">
                        <div class="row">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary continue-btn w-100">Delete</button>
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
@endsection
