@extends('layouts.master')
@section('title', 'Live Location Tracking Report')
@push('style')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<style>
    #map {
        width: 100%;
        height: 500px;
        border-radius: 8px;
        border: 2px solid #e0e0e0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .form-group label {
        font-weight: bold;
        font-size: 16px;
    }

    .content-container {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .content-container .row {
        margin-bottom: 20px;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid #ccc;
    }

    select.form-control {
        font-size: 14px;
    }

    /* .btn {
            border-radius: 25px;
            background-color: #007bff;
            color: white;
        } */
    /*
        .btn:hover {
            background-color: #0056b3;
        } */

    .filter-section {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .filter-section label {
        font-size: 14px;
        color: #555;
    }

    .filter-section select,
    .filter-section input {
        width: 100%;
        padding: 10px;
        margin-top: 8px;
    }

    .page-wrapper {
        background-color: #f1f1f1;
        padding-top: 30px;
    }
</style>
@endpush
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid mt-4">
        <x-page-header :title="'Live Location Tracking Report'"
            :breadcrumbs="[['name' => 'Live Location Tracking Report', 'active' => true]]" />
        <div class="filter-section">
            <form id="filter_form">
                <div class="row">

                    @if (!role_can('reports') && role_can('reports.self.super_department'))
                    <div class="col-md-4">
                        <label for="">Super Department</label>
                        <input type="text" value="{{ user()->department?->super_department?->name }}"
                            class="form-control" readonly>
                        <input type="hidden" name="super_department"
                            value="{{ user()->department?->super_department_id }}" class="form-control">
                        <script>
                            window.selected_super_department = '{{ user()->department?->super_department_id }}';
                        </script>
                    </div>
                    @else
                    <div class="col-md-4">
                        <label for="">Super Department</label>
                        <select name="super_department" id="super_department"
                            class="form-control super-department-select2">
                            @if (request()->super_department)
                            <option value="{{ request()->super_department }}" selected>
                                {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    @endif

                    <div class="mb-2 col-sm-6 col-md-3">
                        <label for="">Department</label>
                        <select name="department" id="department" class="form-control department-select2">
                            @if (request()->department)
                            <option value="{{ request()->department }}" selected>
                                {{ \App\Models\Department::find(request()->department)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>

                    <div class="mb-2 col-sm-6 col-md-3">
                        <label for="">Designation</label>
                        <select name="designation" id="designation" class="form-control designation-select2">
                            @if (request()->designation)
                            <option value="{{ request()->designation }}" selected>
                                {{ \App\Models\Designation::find(request()->designation)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="">Employee</label>
                        <select name="user_id" id="user_filter" class="form-control employee-select2">
                            @if (request()->employee)
                            <option value="{{ request()->employee }}" selected>
                                {{ \App\Models\User::find(request()->employee)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="start_date" class="mb-0">Start Date:</label>
                        <input type="datetime-local" class="form-control" id="start_date" name="start_date">
                    </div>

                    <div class="col-md-4">
                        <label for="end_date" class="mb-0">End Date:</label>
                        <input type="datetime-local" class="form-control" id="end_date" name="end_date">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="mb-0">
                            Markers
                        </label>
                        <select name="" class="show-all-markers form-control" id="">
                            <option value="start-end" selected>Start & End</option>
                            <option value="all">All Data Point</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="mb-2">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="d-none fa fa-spinner fa-spin"></i>
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </form>

        </div>

        <div id="map-container">
            <div id="loader" style="display: none; text-align: center;">
                <img src="https://i.gifer.com/ZZ5H.gif" alt="Loading...">
            </div>
            <div id="map"></div>
        </div>

    </div>
</div>
@endsection

@push('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    let map, polyline, markers = [];

        // document.addEventListener("DOMContentLoaded", function() {
            map = L.map('map').setView([23.8223482, 90.3654215], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            // Set the default cursor style to 'default' (no pointer)
            map.getContainer().style.cursor = 'default'; // Removes pointer style from map

            // Function to show loader
            function showLoader() {
                // document.getElementById('loader').style.display = 'block';
                $('[type=submit]').attr('disabled', true)
                $('[type=submit] i').removeClass('d-none')
            }
            // Function to hide loader
            function hideLoader() {
                // document.getElementById('loader').style.display = 'none';
                $('[type=submit]').attr('disabled', false)
                $('[type=submit] i').addClass('d-none')
            }

            // Function to check if required form fields are filled
            function areRequiredFieldsFilled() {
                let userId = document.getElementById('user_filter').value;
                let startDate = document.getElementById('start_date').value;
                let endDate = document.getElementById('end_date').value;

                // Check if Employee, Start Date, and End Date are selected
                if (!userId || !startDate || !endDate) {
                    toastr.error("Please select an Employee and provide Start Date and End Date.");
                    return false;
                }

                // Check if the date range is more than 2 days
                let start = new Date(startDate);
                let end = new Date(endDate);
                let diffTime = Math.abs(end - start);
                let diffDays = diffTime / (1000 * 60 * 60 * 24); // Convert to days

                if (diffDays > 2) {
                    toastr.error("You can only select a date range of up to 2 days.");
                    return false;
                }

                return true;
            }

            function updateMap() {
                // Check if required fields are filled
                if (!areRequiredFieldsFilled()) {
                    clearMap();
                    return;
                }

                // Show loader while fetching data
                showLoader();

                // Get the form data
                let formData = new FormData(document.getElementById('filter_form'));
                let queryParams = new URLSearchParams(formData).toString();

                fetch(`{{ route('reports.live-location') }}?${queryParams}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Hide loader when data is received
                        hideLoader();
                        updateMarkersAndPolyline(data);
                    })
                    .catch(error => {
                        // Hide loader in case of an error
                        hideLoader();
                        console.error('Error fetching data:', error);
                    });
            }

            // Function to clear the map (when no filters are selected)
            function clearMap() {
                if (polyline) {
                    map.removeLayer(polyline);
                }
                markers.forEach(marker => map.removeLayer(marker));
                markers = [];
            }

            function updateMarkersAndPolyline(data) {
                // Clear previous markers and polyline
                clearMap();

                // Create new polyline points
                let latLngs = [];
                let startMarkerAdded = false;
                let endMarkerAdded = false;

                // Loop through the data and place markers
                data.forEach((item, index) => {
                    let latLng = [item.latitude, item.longitude];
                    latLngs.push(latLng);

                    // Add start marker with popup (first item)
                    if (index === 0 && !startMarkerAdded) {
                        addMarkerWithPopup(latLng, item, 'start'); // Add start marker with popup
                        startMarkerAdded = true;
                    }

                    // Add end marker with popup (last item)
                    if (index === data.length - 1 && !endMarkerAdded) {
                        addMarkerWithPopup(latLng, item, 'end'); // Add end marker with popup
                        endMarkerAdded = true;
                    }

                    // For all records (including intermediate), add markers with popup and hover effect
                    if (index !== 0 && index !== data.length - 1 && $('.show-all-markers').val() == 'all') {
                        addMarkerWithPopup(latLng, item); // Add marker with popup for intermediate points
                    }
                });

                // Add polyline connecting the points
                if (latLngs.length > 1) {
                    polyline = L.polyline(latLngs, {
                        color: 'blue'
                    }).addTo(map);
                    map.fitBounds(polyline.getBounds());
                }
            }

            // Function to add a marker with a popup for all markers (start, end, and intermediate)
            function addMarkerWithPopup(latLng, item, type) {
                let marker = L.marker(latLng).addTo(map);

                // Create details to show in the popup
                let details = `
                    <strong>Latitude:</strong> ${item.latitude}<br>
                    <strong>Longitude:</strong> ${item.longitude}<br>
                    <strong>Timestamp:</strong> ${item.date_time || 'N/A'}<br>
                    <!-- Add any other relevant details here -->
                    `;

                // Bind the popup to the marker
                marker.bindPopup(details);

                // Change cursor style when hovering over the marker
                marker.on('mouseover', function() {
                    map.getContainer().style.cursor = 'pointer'; // Change cursor to pointer on hover
                });

                marker.on('mouseout', function() {
                    map.getContainer().style.cursor = 'default'; // Reset cursor when not hovering
                });
                markers.push(marker)
            }

            // Function to add the default Leaflet marker (no custom icon)
            function addDefaultMarker(latLng, type) {
                L.marker(latLng).addTo(map); // Use the default Leaflet marker icon
            }


            document.getElementById('filter_form')
                .addEventListener('submit', function(event) {
                event.preventDefault();

                updateMap();
            });
        // });
</script>
@endpush