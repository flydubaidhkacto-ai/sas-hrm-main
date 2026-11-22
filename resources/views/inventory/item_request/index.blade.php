@extends('layouts.master')
@section('title', "Inventory Item Request")
@section('content')

<style>
    #spinner {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1050;
        /* Ensure it's above other elements */
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
                        Inventory Item Request
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active">
                            Inventory Item Request
                        </li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                </div>
            </div>
        </div>

        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="row">
            <div class="col-md-12">
                <div>
                    <table class="main-table table table-striped custom-table mb-0">
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
                                    Items
                                </th>
                                <th>
                                    Description
                                </th>
                                <th>
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inventory_request as $item)
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
                                    {{ $item->employee->name }}
                                </td>
                                <td>
                                    <table class="table table-striped">
                                        <tbody>
                                            <tr>
                                                <th>Name</th>
                                                <th>Code</th>
                                                <th>Qty</th>
                                            </tr>
                                            @foreach($item->items as $inventoryItem)
                                                <tr>
                                                    <td>{{ $inventoryItem->item?->name }}</td>
                                                    <td>{{ $inventoryItem->item?->code }}</td>
                                                    <td>{{ $inventoryItem->quantity }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                                <td>
                                    {{ $item->description }}
                                </td>
                                <td>
                                    {{-- <a href="{{ route('inventory_item_request.edit', $item->id) }}"
                                        class="btn btn-info btn-sm"><i class="fa fa-edit"></i> </a> --}}
                                    @if(role_can('inventory'))
                                    <a href="#" onclick="event.preventDefault();
                                                if(confirm('Are you sure?')) {
                                                    $(this).next('form').submit();
                                                }" class="delete-link btn btn-danger btn-sm"><i
                                            class="fa fa-trash"></i>
                                    </a>

                                    <form method="POST"
                                        action="{{ route('inventory_item_request.delete', [$item->id, 'list']) }}"
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
                <h5 class="modal-title">Inventory Item Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="modal_body_edit" class="modal-body">
            </div>
        </div>
    </div>
</div>
<!-- /Edit Modal -->

<div id="spinner" style="display: none; text-align: center; margin: 20px;">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>

<!-- Error Modal -->
<div id="error_modal" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Inventory Item Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="error_modal_body" class="modal-body">
            </div>
        </div>
    </div>
</div>

@section('script')
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('.main-table', {});
</script>
<script>
    // $(document).on('click', '.edit_show', function() {
        //     var view_id = $(this).data('id');
        //     // alert(view_id);

        //     $.ajax({
        //         type: 'GET',
        //         url: 'inventory-item-req/edit/' + view_id,
        //         success: function(data) {
        //             // alert(data);
        //             $('#modal_body_edit').html(data);
        //         }
        //     });
        // });

        $(document).on('click', '.edit_show', function() {
            var view_id = $(this).data('id');
            $('#spinner').show();

            $.ajax({
                type: 'GET',
                url: '/inventory-item-req/edit/' + view_id,
                success: function(data) {
                    $('#modal_body_edit').html(data);
                    $('#spinner').hide();
                },
                error: function(xhr, status, error) {
                    $('#spinner').hide();

                    $('#error_modal_body').html(`
                        <p><strong>Error Status:</strong> ${xhr.status}</p>
                        <p><strong>Message:</strong> ${xhr.responseText}</p>
                    `);
                    $('#error_modal').modal('show');
                }
            });
        });


</script>
@endsection
@endsection
