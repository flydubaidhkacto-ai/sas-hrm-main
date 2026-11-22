@extends('layouts.master')
@section('title', "Barcode Scanner")
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
                        <i class="fa fa-qrcode"></i> Asset Barcode Scanner
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{route('assets-management.index')}}">Assets</a></li>
                        <li class="breadcrumb-item active">Barcode Scanner</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="{{ route('assets-management.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to Assets
                    </a>
                </div>
            </div>
        </div>

        <!-- /Page Header -->
        {!! Toastr::message() !!}
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
                                <p>Scan a barcode to view asset details</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asset Details Section (Hidden initially) -->
        <div class="row" id="assetDetailsSection" style="display: none;">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fa fa-cube"></i> Asset Details
                        </h4>
                    </div>
                    <div class="card-body" id="assetDetails">
                        <!-- Asset details will be populated here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Asset Requests Section (Hidden initially) -->
        <div class="row" id="assetRequestsSection" style="display: none;">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fa fa-list"></i> Asset Requests History
                        </h4>
                    </div>
                    <div class="card-body">
                        <div id="assetRequests">
                            <!-- Asset requests will be populated here -->
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
            url: '{{ route("asset_barcode_scan") }}',
            method: 'POST',
            data: {
                barcode: barcode,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#loadingIndicator').hide();

                if (response.success) {
                    displayAssetDetails(response.asset);
                    displayAssetRequests(response.requests);
                    toastr.success('Asset found successfully!');
                } else {
                    clearResults();
                    toastr.error(response.message || 'Asset not found');
                }
            },
            error: function(xhr) {
                $('#loadingIndicator').hide();
                clearResults();

                if (xhr.status === 404) {
                    toastr.error('Asset not found with this barcode');
                } else {
                    toastr.error('Error scanning barcode');
                }
            }
        });
    }

    function displayAssetDetails(asset) {
        const statusBadge = asset.status == 1 ?
            '<span class="badge badge-success">Active</span>' :
            '<span class="badge badge-danger">Inactive</span>';

        const assetHtml = `
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr><th>Name:</th><td>${asset.name}</td></tr>
                        <tr><th>Tag/Barcode:</th><td><strong>${asset.tag}</strong></td></tr>
                        <tr><th>Category:</th><td>${asset.asset_category ? asset.asset_category.name : 'N/A'}</td></tr>
                        <tr><th>Status:</th><td>${statusBadge}</td></tr>
                        <tr><th>Serial Number:</th><td>${asset.serial_number || 'N/A'}</td></tr>
                        <tr><th>Model:</th><td>${asset.model || 'N/A'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr><th>Manufacturer:</th><td>${asset.manufacturer || 'N/A'}</td></tr>
                        <tr><th>Supplier:</th><td>${asset.supplier || 'N/A'}</td></tr>
                        <tr><th>Location:</th><td>${asset.location || 'N/A'}</td></tr>
                        <tr><th>Current User:</th><td>${asset.user || 'N/A'}</td></tr>
                        <tr><th>Purchase Value:</th><td>${asset.purchase_value || 'N/A'}</td></tr>
                        <tr><th>Present Value:</th><td>${asset.present_value || 'N/A'}</td></tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="btn-group">
                        <a href="/assets-management/${asset.id}" class="btn btn-primary">
                            <i class="fa fa-eye"></i> View Details
                        </a>
                        <a href="/assets-management/${asset.id}/edit" class="btn btn-info">
                            <i class="fa fa-edit"></i> Edit Asset
                        </a>
                    </div>
                </div>
            </div>
        `;

        $('#assetDetails').html(assetHtml);
        $('#assetDetailsSection').show();
        $('#scanResults').html('<div class="alert alert-success"><i class="fa fa-check"></i> Asset found and details loaded below.</div>');
    }

    function displayAssetRequests(requests) {
        if (requests.length === 0) {
            $('#assetRequests').html('<div class="alert alert-info">No requests found for this asset.</div>');
        } else {
            let requestsHtml = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Requested Date</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            requests.forEach((request, index) => {
                const statusClass = request.status === 'approved' ? 'success' :
                                   request.status === 'rejected' ? 'danger' : 'warning';

                requestsHtml += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${request.employee ? request.employee.name : 'N/A'}</td>
                        <td>${request.reason}</td>
                        <td><span class="badge badge-${statusClass}">${request.status}</span></td>
                        <td>${request.start_dt ? new Date(request.start_dt).toLocaleDateString() : 'N/A'}</td>
                        <td>${request.end_dt ? new Date(request.end_dt).toLocaleDateString() : 'N/A'}</td>
                        <td>${new Date(request.created_at).toLocaleDateString()}</td>
                    </tr>
                `;
            });

            requestsHtml += `
                        </tbody>
                    </table>
                </div>
            `;

            $('#assetRequests').html(requestsHtml);
        }
        $('#assetRequestsSection').show();
    }

    function clearResults() {
        $('#assetDetailsSection').hide();
        $('#assetRequestsSection').hide();
        $('#scanResults').html(`
            <div class="text-center text-muted">
                <i class="fa fa-qrcode fa-3x mb-3"></i>
                <p>Scan a barcode to view asset details</p>
            </div>
        `);
    }
});
</script>
@endsection

@endsection
