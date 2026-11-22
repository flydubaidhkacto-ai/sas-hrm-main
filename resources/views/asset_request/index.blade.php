@extends('layouts.master')
@section('title', 'Asset Request')
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
                        Asset Request
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">
                            Asset Request
                        </li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    @if (role_can('asset-request'))
                    <a href="{{ route('asset_request.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add
                        New</a>
                    @endif
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
                                <th>S.L</th>
                                <th>
                                    Action
                                </th>
                                <th>
                                    Emp. ID
                                </th>
                                <th>
                                    Emp. Name
                                </th>
                                <th>
                                    Designation
                                </th>
                                <th>
                                    Department
                                </th>
                                <th>
                                    Asset Name
                                </th>
                                <th>
                                    Reason
                                </th>
                                <th>
                                    Start Date
                                </th>
                                <th>
                                    End Date
                                </th>
                                <th>
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($asset_request as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if ($item->status == 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                    @elseif ($item->status == 'approved')
                                    <span class="badge badge-info">Approved</span>
                                    @elseif ($item->status == 'rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                    @endif
                                    <br>
                                    <button href="" class="btn btn-primary btn-sm edit_show" data-toggle="modal"
                                        data-target="#edit_request" data-id="{{ $item->id }}">
                                        Open
                                    </button>

                                </td>
                                <td>
                                    {{ $item->employee_id }}
                                </td>
                                <td>
                                    <a href="{{ route('employees.active.show', $item->employee_id) }}">
                                        <span class="text-dark">
                                            {{ $item->employee->name }}
                                        </span>
                                    </a>
                                </td>
                                <td>
                                    {{ $item->employee?->designation?->name }}
                                </td>
                                <td>
                                    {{ $item->employee?->department?->name }}
                                </td>
                                <td>
                                    <a href="{{ route('assets-management.show', $item->asset_id) }}">
                                        {{ $item->asset?->name }}
                                    </a>
                                </td>
                                <td>
                                    {{ $item->reason }}
                                </td>
                                <td>
                                    {{ Carbon\Carbon::parse($item->start_dt)->format('d M, Y') }}
                                </td>
                                <td>
                                    {{ Carbon\Carbon::parse($item->end_dt)->format('d M, Y') }}
                                </td>
                                <td>
                                    {{-- <a href="{{ route('asset_request.edit', $item->id) }}"
                                        class="btn btn-info btn-sm"><i class="fa fa-edit"></i> </a> --}}
                                    @if (role_can('asset-manage') || $item->employee_id == user()->id)
                                        <a href="#" onclick="event.preventDefault();
                                                    if(confirm('Are you sure?')) {
                                                        $(this).next('form').submit();
                                                    }" class="delete-link btn btn-danger btn-sm"><i
                                                class="fa fa-trash"></i>
                                        </a>

                                        <form method="POST" action="{{ route('asset_request.delete', $item->id) }}"
                                            class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->
<!-- Edit Modal -->
<div id="edit_request" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asset Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="modal_body_edit" class="modal-body"></div>
        </div>
    </div>
</div>
<!-- /Edit Modal -->
@endsection

@section('script')
{{-- data table cdn --}}
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('table', {

        });
</script>
<script>
    $(document).on('click', '.edit_show', function() {
            var view_id = $(this).data('id');
            // alert(view_id);

            $.ajax({
                type: 'GET',
                url: 'asset-req/edit/' + view_id,
                success: function(data) {
                    // alert(data);
                    $('#modal_body_edit').html(data);
                }
            });
        });
</script>
@endsection
