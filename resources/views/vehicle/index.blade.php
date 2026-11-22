
@extends('layouts.master')
@section('title', "Vehicle Management")
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
                            Vehicle
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                            <li class="breadcrumb-item active">
                                Vehicle
                            </li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a href="{{ route('vehicle.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add New</a>
                    </div>
                </div>
            </div>

            <!-- /Page Header -->
            {!! Toastr::message() !!}
            @include('layouts.alerts')

            <div class="exports">
                <div style="text-align: end" class="mb-2">
                    <a href="{{ add_query_to_current_route(['export' => 'xlsx']) }}" 
                        class="btn btn-sm btn-info" target="_blank">
                        <i class="fa fa-file-excel"></i>
                        Excel
                    </a>
                    <a href="{{ add_query_to_current_route(['export' => 'pdf']) }}" class="btn btn-sm btn-info"
                        target="_blank">
                        <i class="fa fa-file-pdf"></i>
                        PDF
                    </a>

                    <a href="{{ add_query_to_current_route(['export' => 'print']) }}" class="btn btn-sm btn-info"
                        target="_blank">
                        <i class="fa fa-print"></i>
                        Print
                    </a>
                </div>
            </div>
            <div class="mt-2">
                <div class="table-responsive">
                @include('vehicle.list_table', [
                        'data' => $data
                    ])
                </div>
            </div>
        </div>
        <!-- /Page Content -->
    </div>
    <!-- /Page Wrapper -->

    <!-- Vehicle Details Modal -->
<div class="modal fade" id="vehicle_show_modal" tabindex="-1" role="dialog" aria-labelledby="vehicle_show_modalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vehicle_show_modalLabel">Vehicle Details</h5>
                <button type="button" id="closeModalButton" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Name:</strong> <span id="vehicle_name"></span></p>
                <p><strong>Price:</strong> <span id="vehicle_price"></span></p>
                <p><strong>Details:</strong> <span id="vehicle_details"></span></p>
                <p><strong>Seller:</strong> <span id="vehicle_seller"></span></p>
                <p><strong>Sold:</strong> <span id="vehicle_sold"></span></p>
                <p><strong>New Owner Details:</strong> <span id="new_owner_details"></span></p>
                <p><strong>Active Location:</strong> <span id="vehicle_location"></span></p>
            </div>
        </div>
    </div>
</div>

    <link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        let table = new DataTable('table', {
            pageLength: 50
        });;
    </script>
    @section('script')
    <script>
        // $(document).on('click', '.edit_category',function()
        // {
        //     var _this = $(this).parents('tr');
        //     $('#edit_category #e_id').val(_this.find('.id').text().trim());
        //     $('#edit_category #category_edit').val(_this.find('.asset_category_name').text().trim());
        // });

        $(document).on('click', '.view_vehicle', function() {
        const name = $(this).data('name');
        const price = $(this).data('price');
        const sold = $(this).data('sold');
        const seller = $(this).data('seller');
        const details = $(this).data('details');
        const new_owner_details = $(this).data('new_owner_details');
        const location = $(this).data('location');

        $('#vehicle_name').text(name);
        $('#vehicle_price').text(price);
        $('#vehicle_sold').text(sold);
        $('#vehicle_seller').text(seller || 'N/A');
        $('#vehicle_details').text(details || 'N/A');
        $('#new_owner_details').text(new_owner_details || 'N/A');
        $('#vehicle_location').text(location || 'N/A');

        $('#vehicle_show_modal').modal('show');
    });

    document.getElementById('closeModalButton').addEventListener('click', function() {
        $('#vehicle_show_modal').modal('hide');
    });
    </script>
    @endsection
@endsection
