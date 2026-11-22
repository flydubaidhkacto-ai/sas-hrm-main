@extends('layouts.master')
@section('title', "Location Management")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Location Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Locations</li>
                    </ul>
                </div>
                @if(role_can('locations') || role_can('locations.location') || user()->can_create_location)
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#addLocationModal">
                        <i class="fa fa-plus"></i> Add Location
                    </a>
                </div>
                @endif
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Location Tree Filter -->
        <div class="row filter-row mb-3">
            <div class="col-md-4">
                <label for="filterParent">Filter by Parent Location</label>
                <select id="filterParent" class="form-control">
                    <option value="">All Locations</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="filterType">Filter by Type</label>
                <select id="filterType" class="form-control">
                    <option value="">All Types</option>
                    {{-- <option value="country">Country</option>
                    <option value="division">Division</option>
                    <option value="city">City</option> --}}
                    <option value="area">Area</option>
                    {{-- <option value="zone">Zone</option> --}}
                    <option value="location">Location</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>&nbsp;</label>
                <button type="button" id="refreshBtn" class="btn btn-primary btn-block">
                    <i class="fa fa-refresh"></i> Refresh
                </button>
            </div>
        </div>
        <!-- /Location Tree Filter -->

        <!-- Messages -->
        {!! Toastr::message() !!}

        <!-- Location Tree View -->
        {{-- <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Location Hierarchy</h4>
                    </div>
                    <div class="card-body">
                        <div id="locationTree" class="tree-container">
                            <!-- Location tree will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
        <!-- /Location Tree View -->

        <!-- Location Table -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped custom-table" id="locationTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Type</th>
                                <th>Coordinates</th>
                                <th>Radius (m)</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($locations as $location)
                                @include('locations.partials.location-row', ['location' => $location, 'level' => 0])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /Location Table -->
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->

<!-- Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1" role="dialog" aria-labelledby="addLocationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLocationModalLabel">Add New Location</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                    onclick="$('#addLocationModal').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addLocationForm" action="{{ route('locations.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add_name">Location Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add_type">Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="add_type" name="type" required>
                                    <option value="">Select Type</option>
                                    {{-- <option value="country">Country</option>
                                    <option value="division">Division</option>
                                    <option value="city">City</option> --}}
                                    <option value="area">Area</option>
                                    {{-- <option value="zone">Zone</option> --}}
                                    <option value="location">Location</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="add_parent_id">Parent Location</label>
                                <select class="form-control" id="add_parent_id" name="parent_id">
                                    <option value="">Select Parent (Optional)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row phone" style="display: none;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="add_phone">Phone</label>
                                <textarea class="form-control" id="add_phone" name="phone"
                                    rows="1"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row description" style="display: none;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="add_description">Description</label>
                                <textarea class="form-control" id="add_description" name="description"
                                    rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="add_geo_settings" style="display: none;">
                        <div class="col-md-12">
                            <h6>Geo-Location Settings</h6>
                            <hr>
                        </div>
                    </div>

                    <div class="row" id="add_geo_inputs" style="display: none;">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="add_latitude">Latitude</label>
                                <input type="number" step="any" class="form-control" id="add_latitude" name="latitude"
                                    placeholder="e.g., 23.8103">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="add_longitude">Longitude</label>
                                <input type="number" step="any" class="form-control" id="add_longitude" name="longitude"
                                    placeholder="e.g., 90.4125">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="add_radius">Radius (meters)</label>
                                <input type="number" class="form-control" id="add_radius" name="radius"
                                    placeholder="e.g., 100" value="500">
                            </div>
                        </div>
                    </div>

                    <div class="row" id="add_map_controls" style="display: none;">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="add_address_search">Search by Address</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="add_address_search"
                                        placeholder="Enter address or place name">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" id="searchAddressBtn">
                                            <i class="fa fa-search"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-info btn-sm" id="getCurrentLocationBtn">
                                    <i class="fa fa-location-arrow"></i> Use Current Location
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Map Container -->
                    <div class="row mt-3" id="add_map_container" style="display: none;">
                        <div class="col-md-12">
                            <div id="addLocationMap" style="height: 400px;"></div>
                            <small class="text-muted">
                                Click on the map to set coordinates. Enter a radius to see the coverage area.
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        onclick="$('#addLocationModal').modal('hide');">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Location
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Location Modal -->
<div class="modal fade" id="editLocationModal" tabindex="-1" role="dialog" aria-labelledby="editLocationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLocationModalLabel">Edit Location</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                    onclick="$('#editLocationModal').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editLocationForm" action="{{ route('locations.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_name">Location Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_type">Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_type" name="type" required>
                                    <option value="">Select Type</option>
                                    {{-- <option value="country">Country</option>
                                    <option value="division">Division</option>
                                    <option value="city">City</option> --}}
                                    <option value="area">Area</option>
                                    {{-- <option value="zone">Zone</option> --}}
                                    <option value="location">Location</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="edit_parent_id">Parent Location</label>
                                <select class="form-control" id="edit_parent_id" name="parent_id">
                                    <option value="">Select Parent (Optional)</option>
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="row phone" style="display: none;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="add_phone">Phone</label>
                                <textarea class="form-control" id="edit_phone" name="phone"
                                    rows="1"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row description">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="edit_description">Description</label>
                                <textarea class="form-control" id="edit_description" name="description"
                                    rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="edit_geo_settings" style="display: none;">
                        <div class="col-md-12">
                            <h6>Geo-Location Settings</h6>
                            <hr>
                        </div>
                    </div>

                    <div class="row" id="edit_geo_inputs" style="display: none;">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_latitude">Latitude</label>
                                <input type="number" step="any" class="form-control" id="edit_latitude" name="latitude"
                                    placeholder="e.g., 23.8103">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_longitude">Longitude</label>
                                <input type="number" step="any" class="form-control" id="edit_longitude"
                                    name="longitude" placeholder="e.g., 90.4125">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_radius">Radius (meters)</label>
                                <input type="number" class="form-control" id="edit_radius" name="radius"
                                    placeholder="e.g., 100" value="500">
                            </div>
                        </div>
                    </div>

                    <div class="row" id="edit_map_controls" style="display: none;">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="edit_address_search">Search by Address</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="edit_address_search"
                                        placeholder="Enter address or place name">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary"
                                            id="editSearchAddressBtn">
                                            <i class="fa fa-search"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-info btn-sm" id="editGetCurrentLocationBtn">
                                    <i class="fa fa-location-arrow"></i> Use Current Location
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Map Container -->
                    <div class="row mt-3" id="edit_map_container" style="display: none;">
                        <div class="col-md-12">
                            <div id="editLocationMap" style="height: 400px;"></div>
                            <small class="text-muted">Click on the map to set coordinates. Enter a radius to see the
                                coverage area.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        onclick="$('#editLocationModal').modal('hide');">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Update Location
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteLocationModal" tabindex="-1" role="dialog" aria-labelledby="deleteLocationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteLocationModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="deleteLocationForm" action="{{ route('locations.destroy') }}" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" id="delete_id" name="id">
                <div class="modal-body">
                    <p>Are you sure you want to delete this location?</p>
                    <p><strong>Location:</strong> <span id="delete_location_name"></span></p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let addMap, editMap;
let addMarker, editMarker;
let currentLocationId = null;

$(document).ready(function() {
    // Load location hierarchy for parent dropdowns
    loadLocationHierarchy();

    // Initialize location tree
    // loadLocationTree();

    // Initialize modals
    initializeModals();

    // Handle type changes for geo-location visibility
    $('#add_type, #edit_type').change(function() {
        toggleGeoLocationFields($(this));
    });

    // Handle radius changes to update map preview
    $('#add_radius, #edit_radius').on('input', function() {
        updateRadiusPreview($(this));
    });

    // Handle coordinate changes to update map preview
    $('#add_latitude, #add_longitude, #edit_latitude, #edit_longitude').on('input', function() {
        updateCoordinatePreview($(this));
    });
});

function loadLocationHierarchy() {
    $.get('{{ route('locations.hierarchy') }}', function(data) {
        updateParentDropdowns(data);
    });
}

function updateParentDropdowns(locations, prefix = '') {
    const addSelect = $('#add_parent_id');
    const editSelect = $('#edit_parent_id');
    const filterSelect = $('#filterParent');

    locations.forEach(function(location) {
        if(location.type == 'location') return;
        const optionText = prefix + location.name;
        const option = `<option value="${location.id}">${optionText}</option>`;

        addSelect.append(option);
        editSelect.append(option);
        filterSelect.append(option);

        if (location.children && location.children.length > 0) {
            updateParentDropdowns(location.children, prefix + location.name + ' > ');
        }
    });
}

function initializeModals() {
    // Add Location Modal
    $('#addLocationModal').on('shown.bs.modal', function() {
        $('#add_type').val('').change()
        $('#add_name').focus();
    });

    // Edit Location Modal
    $('#editLocationModal').on('shown.bs.modal', function() {
        $('#edit_name').focus();
    });
}

// Type change handler
function toggleGeoLocationFields(typeSelect) {
    const isLocation = typeSelect.val() === 'location';
    const prefix = typeSelect.attr('id').includes('add') ? 'add' : 'edit';

    if (isLocation) {
        $(`#${prefix}_geo_settings`).show();
        $(`#${prefix}_geo_inputs`).show();
        $(`#${prefix}_map_controls`).show();
        $(`#${prefix}_map_container`).show();
        $('.description,.phone').show()
        // Initialize map if not already done
        if (prefix === 'add') {
            initializeAddMap();
        } else {
            initializeEditMap();
        }
    } else {
        $(`#${prefix}_geo_settings`).hide();
        $(`#${prefix}_geo_inputs`).hide();
        $(`#${prefix}_map_controls`).hide();
        $(`#${prefix}_map_container`).hide();
        $('.description,.phone').hide()

        // Clear coordinates when hiding
        $(`#${prefix}_latitude`).val('');
        $(`#${prefix}_longitude`).val('');
        $(`#${prefix}_radius`).val('');
    }
}

// Radius preview update
function updateRadiusPreview(radiusInput) {
    const prefix = radiusInput.attr('id').includes('add') ? 'add' : 'edit';
    const radius = parseInt(radiusInput.val());
    const lat = parseFloat($(`#${prefix}_latitude`).val());
    const lng = parseFloat($(`#${prefix}_longitude`).val());

    if (radius && lat && lng) {
        const map = prefix === 'add' ? addMap : editMap;
        if (map) {
            // Remove existing radius circle
            if (window[`${prefix}RadiusCircle`]) {
                map.removeLayer(window[`${prefix}RadiusCircle`]);
            }

            // Add new radius circle
            window[`${prefix}RadiusCircle`] = L.circle([lat, lng], {
                color: 'green',
                fillColor: 'green',
                fillOpacity: 0.2,
                radius: radius
            }).addTo(map);
        }
    }
}

// Coordinate preview update
function updateCoordinatePreview(coordinateInput) {
    const prefix = coordinateInput.attr('id').includes('add') ? 'add' : 'edit';
    const lat = parseFloat($(`#${prefix}_latitude`).val());
    const lng = parseFloat($(`#${prefix}_longitude`).val());

    if (lat && lng) {
        const map = prefix === 'add' ? addMap : editMap;
        if (map) {
            // Update marker position
            if (window[`${prefix}Marker`]) {
                window[`${prefix}Marker`].setLatLng([lat, lng]);
            } else {
                window[`${prefix}Marker`] = L.marker([lat, lng]).addTo(map);
            }

            // Center map on new coordinates
            map.setView([lat, lng], 15);

            // Update radius circle if radius exists
            const radius = parseInt($(`#${prefix}_radius`).val());
            if (radius) {
                updateRadiusPreview($(`#${prefix}_radius`));
            }
        }
    }
}

// Address search function
function searchAddress(address, prefix) {
    if (!address) return;

    // Using Nominatim API for geocoding
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                const result = data[0];
                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lon);

                // Update coordinate inputs
                $(`#${prefix}_latitude`).val(lat.toFixed(8));
                $(`#${prefix}_longitude`).val(lng.toFixed(8));

                // Update map
                updateCoordinatePreview($(`#${prefix}_latitude`));

                // Show success message
                toastr.success(`Found: ${result.display_name}`);
            } else {
                toastr.error('Address not found. Please try a different search term.');
            }
        })
        .catch(error => {
            console.error('Geocoding error:', error);
            toastr.error('Error searching for address. Please try again.');
        });
}

// Map functions
function initializeAddMap() {
    if (!addMap) {
        addMap = L.map('addLocationMap').setView([23.8103, 90.4125], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'ProHR'
        }).addTo(addMap);

        addMap.on('click', function(e) {
            setAddLocationCoordinates(e.latlng.lat, e.latlng.lng);
        });
    }

    setTimeout(() => addMap.invalidateSize(), 100);
}

function initializeEditMap() {
    if (!editMap) {
        editMap = L.map('editLocationMap').setView([23.8103, 90.4125], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'ProHR'
        }).addTo(editMap);

        editMap.on('click', function(e) {
            setEditLocationCoordinates(e.latlng.lat, e.latlng.lng);
        });
    }

    setTimeout(() => editMap.invalidateSize(), 100);
}

function setAddLocationCoordinates(lat, lng) {
    $('#add_latitude').val(lat.toFixed(8));
    $('#add_longitude').val(lng.toFixed(8));

    if (addMarker) {
        addMap.removeLayer(addMarker);
    }

    addMarker = L.marker([lat, lng]).addTo(addMap);
    addMap.setView([lat, lng], 15);
}

function setEditLocationCoordinates(lat, lng) {
    lat = Number(lat)
    lng = Number(lng)
    $('#edit_latitude').val(lat.toFixed(8));
    $('#edit_longitude').val(lng.toFixed(8));
    console.log(lat, lng, editMarker, editMap);

    if (editMarker) {
        editMap.removeLayer(editMarker);
    }
    // setTimeout(() => {
        editMarker = L.marker([lat, lng]).addTo(editMap);
        editMap.setView([lat, lng], 15);
    // }, 5000)
}

// Button event handlers
$('#searchAddressBtn').click(function() {
    const address = $('#add_address_search').val();
    if (address) {
        searchAddress(address, 'add');
    } else {
        toastr.warning('Please enter an address to search.');
    }
});

$('#editSearchAddressBtn').click(function() {
    const address = $('#edit_address_search').val();
    if (address) {
        searchAddress(address, 'edit');
    } else {
        toastr.warning('Please enter an address to search.');
    }
});

// Allow searching by pressing Enter
$('#add_address_search').keypress(function(e) {
    if (e.which === 13) {
        $('#searchAddressBtn').click();
    }
});

$('#edit_address_search').keypress(function(e) {
    if (e.which === 13) {
        $('#editSearchAddressBtn').click();
    }
});

$('#getCurrentLocationBtn').click(function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            setAddLocationCoordinates(position.coords.latitude, position.coords.longitude);
            if($('#add_radius').val()) {
                updateRadiusPreview($('#add_radius'))
            }
        }, function(error) {
            toastr.error('Failed to get current location: ' + error.message);
        });
    } else {
        toastr.error('Geolocation is not supported by this browser.');
    }
});

$('#editGetCurrentLocationBtn').click(function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            setEditLocationCoordinates(position.coords.latitude, position.coords.longitude);
            if($('#edit_radius').val()) {
                updateRadiusPreview($('#edit_radius'))
            }
        }, function(error) {
            toastr.error('Failed to get current location: ' + error.message);
        });
    } else {
        toastr.error('Geolocation is not supported by this browser.');
    }
});

// CRUD functions
function editLocation(id) {
    $.get(`{{ route('locations.show', '') }}/${id}`, function(location) {
        $('#edit_id').val(location.id);
        $('#edit_name').val(location.name);
        $('#edit_type').val(location.type);
        $('#edit_parent_id').val(location.parent_id);
        $('#edit_description').val(location.description);
        $('#edit_phone').val(location.phone);
        $('#edit_latitude').val(location.latitude);
        $('#edit_longitude').val(location.longitude);
        $('#edit_radius').val(location.radius);

        // Show/hide geo-location fields based on type
        toggleGeoLocationFields($('#edit_type'));

        // Show existing location on map if coordinates exist and type is location
        if (location.type === 'location' && location.latitude && location.longitude) {
            setTimeout(() => {
                initializeEditMap();
                setEditLocationCoordinates(location.latitude, location.longitude);
                if ($('#edit_radius').val()) {
                    updateRadiusPreview($('#edit_radius'));
                }
            }, 1_000);
        }

        $('#editLocationModal').modal('show');
    });
}

function deleteLocation(id, name) {
    $('#delete_id').val(id);
    $('#delete_location_name').text(name);
    $('#deleteLocationModal').modal('show');
}

// Additional functions for location management
function viewOnMap(id) {
    $.get(`{{ route('locations.coordinates', '') }}/${id}`, function(data) {
        // Create a new modal for map view
        const mapModal = `
            <div class="modal fade" id="viewMapModal" tabindex="-1" role="dialog" aria-labelledby="viewMapModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewMapModalLabel">Location: ${data.name}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div id="viewLocationMap" style="height: 400px;"></div>
                            <div class="mt-2">
                                <strong>Coordinates:</strong> ${data.latitude}, ${data.longitude}<br>
                                ${data.radius ? '<strong>Radius:</strong> ' + data.radius + ' meters' : ''}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        $('#viewMapModal').remove();

        // Add new modal to body
        $('body').append(mapModal);

        // Show modal
        $('#viewMapModal').modal('show');

        // Initialize map after modal is shown
        $('#viewMapModal').on('shown.bs.modal', function() {
            const viewMap = L.map('viewLocationMap').setView([data.latitude, data.longitude], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: 'ProHR'
            }).addTo(viewMap);

            // Add marker
            const marker = L.marker([data.latitude, data.longitude]).addTo(viewMap);

            // Add radius circle if radius exists
            if (data.radius) {
                L.circle([data.latitude, data.longitude], {
                    color: 'green',
                    fillColor: 'green',
                    fillOpacity: 0.1,
                    radius: data.radius
                }).addTo(viewMap);
            }

            setTimeout(() => viewMap.invalidateSize(), 100);
        });

        // Clean up modal when closed
        $('#viewMapModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });

    }).fail(function() {
        alert('Failed to load location coordinates');
    });
}

function addChild(parentId, parentName) {
    // Reset the add form
    $('#addLocationForm')[0].reset();

    // Set the parent
    $('#add_parent_id').val(parentId);

    // Update modal title
    $('#addLocationModalLabel').text(`Add Point Location to: ${parentName}`);

    // Show the modal
    $('#addLocationModal').modal('show');
}

// Refresh button
$('#refreshBtn').click(function() {
    location.reload();
});

// Tree toggle functionality
$(document).on('click', '.tree-toggle', function() {
    $(this).parent().parent().next('ul').toggle();
    $(this).toggleClass('fa-minus-square fa-plus-square');
});

// Filter functionality
$('#filterParent, #filterType').change(function() {
    const parentFilter = $('#filterParent').val();
    const typeFilter = $('#filterType').val();

    $('.location-row').each(function() {
        const row = $(this);
        let showRow = true;

        // Apply parent filter
        if (parentFilter) {
            // This would need more complex logic to check hierarchy
            // For now, simple implementation
            showRow = showRow && (row.find('td:eq(2)').text().includes($('#filterParent option:selected').text()) ||
                                 row.find('td:eq(3)').text().includes($('#filterParent option:selected').text()));
        }

        // Apply type filter
        if (typeFilter) {
            showRow = showRow && row.find('.badge-info').text().toLowerCase().includes(typeFilter);
        }

        if (showRow) {
            row.show();
        } else {
            row.hide();
        }
    });
});
</script>

<style>
    .tree-container {
        max-height: 500px;
        overflow-y: auto;
    }

    .tree-list {
        list-style: none;
        padding-left: 0;
    }

    .tree-list ul {
        list-style: none;
        padding-left: 20px;
    }

    .tree-item {
        margin: 5px 0;
    }

    .tree-node {
        display: flex;
        align-items: center;
        padding: 8px;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        background: #f8f9fa;
    }

    .tree-icon {
        margin-right: 8px;
        width: 20px;
        text-align: center;
    }

    .tree-label {
        flex-grow: 1;
        margin-right: 10px;
        font-weight: 500;
    }

    .tree-type {
        margin-right: 10px;
    }

    .tree-coordinates {
        margin-right: 10px;
    }

    .tree-actions {
        display: flex;
        gap: 5px;
    }

    .tree-toggle {
        cursor: pointer;
        color: #6c757d;
    }

    .tree-toggle:hover {
        color: #495057;
    }

    .tree-leaf {
        color: #28a745;
        font-size: 8px;
    }

    #addLocationMap,
    #editLocationMap {
        border: 1px solid #ddd;
        border-radius: 4px;
    }
</style>
@endsection
