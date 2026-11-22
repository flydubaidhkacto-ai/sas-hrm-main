@extends('layouts.master')
@section('title', "Inventory Barcode Scanner")
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
                        <i class="fa fa-qrcode"></i> Inventory Barcode Scanner
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{route('inventory.item.index')}}">Inventory Items</a></li>
                        <li class="breadcrumb-item active">Barcode Scanner</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="{{ route('inventory.item.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to Items
                    </a>
                </div>
            </div>
        </div>

        <!-- /Page Header -->
        @include('layouts.alerts')

        <div class="row">
            <!-- Scanner Section -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fa fa-camera"></i> Barcode Scanner
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Manual Input Option -->
                        <div class="form-group">
                            <label>Enter Barcode Manually:</label>
                            <div class="input-group">
                                <input type="text" id="manualBarcode" class="form-control" placeholder="Scan or type barcode here..." autofocus>
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" id="scanManualBtn">
                                        <i class="fa fa-search"></i> Scan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mb-3">
                            <strong>OR</strong>
                        </div>

                        <!-- Camera Scanner -->
                        <div class="text-center">
                            <button id="startCameraBtn" class="btn btn-success btn-lg">
                                <i class="fa fa-camera"></i> Start Camera Scanner
                            </button>
                            <button id="stopCameraBtn" class="btn btn-danger btn-lg" style="display: none;">
                                <i class="fa fa-stop"></i> Stop Scanner
                            </button>
                        </div>

                        <!-- Camera Video -->
                        <div id="cameraContainer" style="display: none;" class="mt-3">
                            <video id="scanner" style="width: 100%; max-height: 300px;"></video>
                            <canvas id="canvas" style="display: none;"></canvas>
                        </div>

                        <!-- Loading indicator -->
                        <div id="loadingIndicator" style="display: none;" class="text-center mt-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2">Scanning...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fa fa-info-circle"></i> Scan Results
                        </h4>
                    </div>
                    <div class="card-body">
                        <div id="scanResults">
                            <div class="text-center text-muted">
                                <i class="fa fa-qrcode fa-3x mb-3"></i>
                                <p>Scan a barcode to view inventory item details</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Item Details Section (Hidden initially) -->
        <div class="row" id="itemDetailsSection" style="display: none;">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fa fa-cube"></i> Inventory Item Details
                        </h4>
                    </div>
                    <div class="card-body" id="itemDetails">
                        <!-- Item details will be populated here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Item Requests Section (Hidden initially) -->
        <div class="row" id="itemRequestsSection" style="display: none;">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fa fa-list"></i> Item Requests History
                        </h4>
                    </div>
                    <div class="card-body">
                        <div id="itemRequests">
                            <!-- Item requests will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->

@section('script')
<script src="https://unpkg.com/@zxing/library@latest"></script>
<script>
$(document).ready(function() {
    let codeReader = null;
    let selectedDeviceId = null;

    // Manual barcode input
    $('#manualBarcode').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            scanBarcode();
        }
    });

    $('#scanManualBtn').on('click', function() {
        scanBarcode();
    });

    // Start camera scanner
    $('#startCameraBtn').on('click', function() {
        startScanner();
    });

    // Stop camera scanner
    $('#stopCameraBtn').on('click', function() {
        stopScanner();
    });

    function scanBarcode() {
        const barcode = $('#manualBarcode').val().trim();
        if (barcode) {
            processBarcode(barcode);
        } else {
            toastr.error('Please enter a barcode');
        }
    }

    function startScanner() {
        $('#loadingIndicator').show();

        codeReader = new ZXing.BrowserMultiFormatReader();

        codeReader.listVideoInputDevices()
            .then((videoInputDevices) => {
                if (videoInputDevices.length > 0) {
                    selectedDeviceId = videoInputDevices[0].deviceId;
                    if (videoInputDevices.length >= 1) {
                        // Prefer back camera if available
                        const backCamera = videoInputDevices.find(device =>
                            device.label.toLowerCase().includes('back') ||
                            device.label.toLowerCase().includes('rear')
                        );
                        if (backCamera) {
                            selectedDeviceId = backCamera.deviceId;
                        }
                    }

                    $('#cameraContainer').show();
                    $('#startCameraBtn').hide();
                    $('#stopCameraBtn').show();
                    $('#loadingIndicator').hide();

                    codeReader.decodeFromVideoDevice(selectedDeviceId, 'scanner', (result, err) => {
                        if (result) {
                            processBarcode(result.text);
                            stopScanner();
                        }
                        if (err && !(err instanceof ZXing.NotFoundException)) {
                            console.error(err);
                        }
                    });
                } else {
                    $('#loadingIndicator').hide();
                    toastr.error('No camera devices found');
                }
            })
            .catch((err) => {
                $('#loadingIndicator').hide();
                console.error(err);
                toastr.error('Error accessing camera: ' + err.message);
            });
    }

    function stopScanner() {
        if (codeReader) {
            codeReader.reset();
            codeReader = null;
        }
        $('#cameraContainer').hide();
        $('#startCameraBtn').show();
        $('#stopCameraBtn').hide();
        $('#loadingIndicator').hide();
    }

    function processBarcode(barcode) {
        $('#loadingIndicator').show();
        $('#manualBarcode').val(barcode);

        $.ajax({
            url: '{{ route("inventory_barcode_scan") }}',
            method: 'POST',
            data: {
                barcode: barcode,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#loadingIndicator').hide();

                if (response.success) {
                    displayItemDetails(response.item);
                    displayItemRequests(response.requests);
                    toastr.success('Inventory item found successfully!');
                } else {
                    clearResults();
                    toastr.error(response.message || 'Inventory item not found');
                }
            },
            error: function(xhr) {
                $('#loadingIndicator').hide();
                clearResults();

                if (xhr.status === 404) {
                    toastr.error('Inventory item not found with this barcode');
                } else {
                    toastr.error('Error scanning barcode');
                }
            }
        });
    }

    function displayItemDetails(item) {
        const itemHtml = `
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr><th>Name:</th><td>${item.name}</td></tr>
                        <tr><th>Code/Barcode:</th><td><strong>${item.code}</strong></td></tr>
                        <tr><th>Category:</th><td>${item.rel_to_inv_category ? item.rel_to_inv_category.name : 'N/A'}</td></tr>
                        <tr><th>Unit:</th><td>${item.rel_to_inv_unit ? item.rel_to_inv_unit.name : 'N/A'}</td></tr>
                        <tr><th>Current Quantity:</th><td><span class="badge ${item.current_quantity > item.alert_quantity ? 'badge-success' : 'badge-warning'}">${item.current_quantity || 0}</span></td></tr>
                        <tr><th>Alert Quantity:</th><td>${item.alert_quantity}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr><th>Total Cost:</th><td>${item.total_cost || 'N/A'}</td></tr>
                        <tr><th>Status:</th><td>${item.status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>'}</td></tr>
                        <tr><th>Created:</th><td>${new Date(item.created_at).toLocaleDateString()}</td></tr>
                        <tr><th>Updated:</th><td>${new Date(item.updated_at).toLocaleDateString()}</td></tr>
                    </table>
                    ${item.current_quantity <= item.alert_quantity ?
                        '<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> <strong>Low Stock Alert!</strong> Current quantity is at or below alert level.</div>' :
                        ''}
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="btn-group">
                        <a href="/inventory/item/${item.id}/edit" class="btn btn-info">
                            <i class="fa fa-edit"></i> Edit Item
                        </a>
                        <a href="/inventory-item-request/create" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Create Request
                        </a>
                    </div>
                </div>
            </div>
        `;

        $('#itemDetails').html(itemHtml);
        $('#itemDetailsSection').show();
        $('#scanResults').html('<div class="alert alert-success"><i class="fa fa-check"></i> Inventory item found and details loaded below.</div>');
    }

    function displayItemRequests(requests) {
        if (requests.length === 0) {
            $('#itemRequests').html('<div class="alert alert-info">No requests found for this inventory item.</div>');
        } else {
            let requestsHtml = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee</th>
                                <th>Quantity</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Received Status</th>
                                <th>Requested Date</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            requests.forEach((request, index) => {
                const statusClass = request.status === 'approved' ? 'success' :
                                   request.status === 'rejected' ? 'danger' : 'warning';

                const receivedStatusClass = request.received_status === 'received' ? 'success' :
                                           request.received_status === 'not_received' ? 'danger' : 'secondary';

                // Get the quantity for this specific item
                const itemQuantity = request.items && request.items.length > 0 ? request.items[0].quantity : 'N/A';

                requestsHtml += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${request.employee ? request.employee.name : 'N/A'}</td>
                        <td><span class="badge badge-info">${itemQuantity}</span></td>
                        <td>${request.description || 'N/A'}</td>
                        <td><span class="badge badge-${statusClass}">${request.status}</span></td>
                        <td><span class="badge badge-${receivedStatusClass}">${request.received_status || 'Pending'}</span></td>
                        <td>${new Date(request.created_at).toLocaleDateString()}</td>
                    </tr>
                `;
            });

            requestsHtml += `
                        </tbody>
                    </table>
                </div>
            `;

            $('#itemRequests').html(requestsHtml);
        }
        $('#itemRequestsSection').show();
    }

    function clearResults() {
        $('#itemDetailsSection').hide();
        $('#itemRequestsSection').hide();
        $('#scanResults').html(`
            <div class="text-center text-muted">
                <i class="fa fa-qrcode fa-3x mb-3"></i>
                <p>Scan a barcode to view inventory item details</p>
            </div>
        `);
    }
});
</script>
@endsection

@endsection
