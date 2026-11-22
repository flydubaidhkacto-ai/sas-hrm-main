
@extends('layouts.master')
@section('title', "Vehicle Services Management")
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
                            Vehicle Services
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                            <li class="breadcrumb-item active">
                                Vehicle Services
                            </li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a href="{{ route('vehicle_service.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add New</a>
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
                @include('vehicle_service.list_table', [
                        'data' => $data
                    ])
                </div>
            </div>
        </div>
        <!-- /Page Content -->
    </div>
    <!-- /Page Wrapper -->

    <!-- Vehicle Details Modal -->
<div class="modal fade" id="vehicle_service_show_modal" tabindex="-1" role="dialog" aria-labelledby="vehicle_service_show_modalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vehicle_service_show_modalLabel">Vehicle Service Details</h5>
                <button type="button" id="closeModalButton" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p style="font-size: 16px"><strong>Vehicle Name:</strong> <span id="vehicle_service_name"></span></p>
                <p style="font-size: 16px"><strong>Service Cost:</strong> <span id="vehicle_service_cost"></span></p>
                <p style="font-size: 16px"><strong>Service Reason:</strong> <span id="vehicle_service_reason"></span></p>
                <p style="font-size: 16px"><strong>Changed Parts:</strong> <span id="vehicle_service_changed_parts"></span></p>
                <p style="font-size: 16px"><strong>Repairman Name:</strong> <span id="vehicle_service_repairman_name"></span></p>
                <p style="font-size: 16px"><strong>Repairman Location:</strong> <span id="vehicle_service_repairman_location"></span></p>
                <p style="font-size: 16px"><strong>Emp. ID:</strong> <span id="vehicle_service_employee_id"></span></p>
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

        $(document).on('click', '.view_vehicle_service', function() {
        const name = $(this).data('name');
        const cost = $(this).data('cost');
        const reason = $(this).data('reason');
        const changed_parts = $(this).data('changed_parts');
        const repairman_name = $(this).data('repairman_name');
        const repairman_location = $(this).data('repairman_location');
        const responsible_person_id = $(this).data('responsible_person_id');

        $('#vehicle_service_name').text(name);
        $('#vehicle_service_cost').text(cost);
        $('#vehicle_service_reason').text(reason);
        $('#vehicle_service_changed_parts').text(changed_parts);
        $('#vehicle_service_repairman_name').text(repairman_name || 'N/A');
        $('#vehicle_service_repairman_location').text(repairman_location || 'N/A');
        $('#vehicle_service_employee_id').text(responsible_person_id);

        $('#vehicle_service_show_modal').modal('show');
    });

    document.getElementById('closeModalButton').addEventListener('click', function() {
        $('#vehicle_service_show_modal').modal('hide');
    });
    </script>
    @endsection
@endsection
