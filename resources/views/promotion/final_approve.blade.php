@extends('layouts.master')

@section('title', "Final Approve")
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
                            <li class="breadcrumb-item active">Pending Finale Promotion</li>
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
                            <th>Preliminary approved by</th>
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
                                <td>{{ $items->first_approve ? $items->first_approve->name : '-' }}</td>
                                <td>
                                    @if ($items->second_status == 2)
                                        Rejected
                                    @elseif($items->second_status == null)
                                        Pending
                                    @elseif($items->second_status == 1)
                                        Approved
                                    @endif
                                </td>
                                <td class="text-right">


                                    @if ($items->second_status == null)
                                        <a href="#"class="btn btn-info btn-sm active" data-id="{{ $items->id }}"
                                            data-toggle="modal" data-target="#accept_promotion" role="button"
                                            aria-pressed="true"><i class="fa fa-check-square-o m-r-5"></i> Approve</a>

                                        <a href="#"class="btn btn-danger btn-sm active" data-id="{{ $items->id }}"
                                            data-toggle="modal" data-target="#reject_promotion" role="button"
                                            aria-pressed="true"><i class="fa fa-ban" aria-hidden="true"></i> Reject</a>
                                    @endif


                                </td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal custom-modal fade" id="accept_promotion" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>Approve</h3>
                        <p>Are you sure want to Promote?</p>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <a href="{{ route('finale.promotion.approve', [$items->id]) }}"><button type="submit"
                                    class="btn btn-info continue-btn w-100">Accept</button></a>
                        </div>
                        <div class="col-6">
                            <a href="javascript:void(0);" data-dismiss="modal" class="btn btn-info cancel-btn">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal custom-modal fade" id="reject_promotion" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>Reject</h3>
                        <p>Are you sure want to reject?</p>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <a href="{{ route('finale.promotion.reject', [$items->id]) }}"><button type="submit"
                                    class="btn btn-info continue-btn w-100">Reject</button></a>
                        </div>
                        <div class="col-6">
                            <a href="javascript:void(0);" data-dismiss="modal" class="btn btn-info cancel-btn">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
