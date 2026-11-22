<div class="tab-pane fade" id="nav-geo-locations" role="tabpanel" aria-labelledby="nav-geo-locations-tab">
    <h3>Geo Locations</h3>
    <div class="row">
        <!-- Home Address Coordinates (Left Side) -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Home Address:</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="">
                            Latitude & Longitude
                        </label>
                        <div class="d-flex">
                            <div>
                                <input type="number" step="any" name="home_latitude" id="home_latitude"
                                    class="form-control" placeholder="Enter latitude"
                                    value="{{ old('home_latitude', $employee->home_latitude) }}">
                                @if ($errors->has('home_latitude'))
                                <span class="text-danger">{{ $errors->first('home_latitude') }}</span>
                                @endif
                            </div>
                            <div>
                                <input type="number" step="any" name="home_longitude" id="home_longitude"
                                    class="form-control" placeholder="Enter longitude"
                                    value="{{ old('home_longitude', $employee->home_longitude) }}">
                                @if ($errors->has('home_longitude'))
                                <span class="text-danger">{{ $errors->first('home_longitude') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm" onclick="getCurrentLocation()">
                            <i class="fa fa-map-marker"></i> Get Current Location
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="pickFromMap()">
                            <i class="fa fa-map"></i> Pick from Map
                        </button>
                    </div>
                    <div id="home-location-map" style="height: 250px; margin-top: 15px; display: none;"></div>
                </div>
            </div>
            <div class="mt-2">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Admin of Areas:</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-1">
                            <input type="checkbox" name="can_create_location" id="can_create_location" value="1"
                                style="transform: scale(1.3)"
                                @checked($employee->can_create_location)
                            >
                            <label for="can_create_location" class="ml-2">
                                <b>Can Create Location?</b>
                            </label>
                        </div>
                        <hr>
                        @foreach (\App\Models\Location::whereNull('parent_id')->get() as $item)

                            <div class="mb-1">
                                <input type="checkbox" name="locations_admin[]" id="locations_admin{{ $item->id }}" value="{{ $item->id }}"
                                    style="transform: scale(1.3)"
                                    @checked($employee->locations_admin->contains($item->id))
                                >
                                <label for="locations_admin{{ $item->id }}" class="ml-2">
                                    <b>{{ $item->fullPath }}</b>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Locations (Right Side) -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Assigned Locations</h5>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openLocationModal()">
                        <i class="fa fa-plus"></i> Add Location
                    </button>
                </div>
                <div class="card-body">
                    <div id="assigned-locations-list" class="mt-2">
                        @if($employee->assigned_locations && $employee->assigned_locations->count()
                        > 0)
                        @foreach($employee->assigned_locations as $location)
                        <div class="assigned-location-item mb-3 p-3 border rounded"
                            data-location-id="{{ $location->id }}" data-lat="{{ $location->latitude }}"
                            data-lon="{{ $location->longitude }}" data-type="{{ $location->type }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $location->name }}</h6>
                                    <small class="text-muted">
                                        {{ $location->getFullHierarchy() }}
                                    </small>
                                    <div class="mt-2">
                                        <label class="mb-1">Distance (KM):</label>
                                        <input type="number" step="0.01" name="location_distances[{{ $location->id }}]"
                                            class="form-control form-control-sm"
                                            style="width: 100px; display: inline-block;"
                                            value="{{ old('location_distances.' . $location->id, $location->pivot->distance_km) }}">
                                        <input type="hidden" name="assigned_location_ids[]" value="{{ $location->id }}">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-danger btn-sm"
                                    onclick="removeAssignedLocation({{ $location->id }})">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                        @else
                        <p class="text-muted text-center">No locations assigned yet</p>
                        @endif
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary btn-sm" onclick="getAllDistance()">
                            <i class="fa fa-redo"></i>
                            Calculate Distance
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="submit-section">
                <button class="btn btn-primary submit-btn">Submit</button>
            </div>
        </div>
    </div>
</div>
<script>
    // Global variables for geo-location functionality
let homeLocationMap;
let homeLocationMarker;
let assignedLocationIds = @json($employee->assigned_locations->pluck('id')->toArray() ?? []);

// Add tab change event listeners to fix map display issues
$('button[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    if ($(e.target).attr('data-target') === '#nav-geo-locations') {
        // Delay to ensure the tab is fully visible before invalidating map size
        setTimeout(function() {
            if (homeLocationMap) {
                homeLocationMap.invalidateSize();

                // Recenter map if coordinates exist
                const lat = $('#home_latitude').val();
                const lng = $('#home_longitude').val();
                if (lat && lng) {
                    homeLocationMap.setView([parseFloat(lat), parseFloat(lng)], 15);
                }
            }
        }, 100);
    }
});

// Initialize map when needed
function initializeHomeLocationMap() {
    if (!homeLocationMap) {
        homeLocationMap = L.map('home-location-map').setView([23.8103, 90.4125], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'ProHR'
        }).addTo(homeLocationMap);

        homeLocationMap.on('click', function(e) {
            setHomeLocationCoordinates(e.latlng.lat, e.latlng.lng);
        });
    }

    setTimeout(() => homeLocationMap.invalidateSize(), 100);
}

function setHomeLocationCoordinates(lat, lng) {
    $('#home_latitude').val(lat.toFixed(8));
    $('#home_longitude').val(lng.toFixed(8));

    if (homeLocationMarker) {
        homeLocationMap.removeLayer(homeLocationMarker);
    }

    homeLocationMarker = L.marker([lat, lng], {draggable: true}).addTo(homeLocationMap);
    homeLocationMap.setView([lat, lng], 15);

    // Add drag event to marker
    homeLocationMarker.on('dragend', function(event) {
        const pos = event.target.getLatLng();
        $('#home_latitude').val(pos.lat.toFixed(8));
        $('#home_longitude').val(pos.lng.toFixed(8));
    });
}

// Pick location from map for home address
function pickFromMap() {
    $('#home-location-map').show();

    setTimeout(function() {
        initializeHomeLocationMap();
        getAllDistance();
        toastr.info('Click on the map to set home address location');
    }, 100);
}

// Get current location for home address
function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            $('#home_latitude').val(lat);
            $('#home_longitude').val(lng);

            showHomeLocationMap(lat, lng);
            toastr.success('Location detected successfully!');
            getAllDistance();
        }, function(error) {
            toastr.error('Error getting location: ' + error.message);
        });
    } else {
        toastr.error('Geolocation is not supported by this browser.');
    }
}

// Show home location on map
function showHomeLocationMap(lat, lng) {
    $('#home-location-map').show();

    // Small delay to ensure container is visible
    setTimeout(function() {
        if (homeLocationMap) {
            homeLocationMap.remove();
        }

        homeLocationMap = L.map('home-location-map').setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'ProHR'
        }).addTo(homeLocationMap);

        // Force map to calculate proper size
        homeLocationMap.invalidateSize();

        if (homeLocationMarker) {
            homeLocationMarker.remove();
        }

        homeLocationMarker = L.marker([lat, lng], {draggable: true})
            .addTo(homeLocationMap)
            .bindPopup('Home Address Location')
            .openPopup();

        homeLocationMarker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            $('#home_latitude').val(pos.lat);
            $('#home_longitude').val(pos.lng);
        });
    }, 100);
}

// Open location selection modal
function openLocationModal() {
    $('#locationSelectionModal').modal('show');
    loadLocationsForModal();
}

// Load locations for modal
function loadLocationsForModal() {
    $('#locations-loading').show();
    $('#locations-container').empty();

    $.ajax({
        url: '{{ route("locations.hierarchy") }}',
        method: 'GET',
        success: function(response) {
            $('#locations-loading').hide();
            populateLocationModal(response);
        },
        error: function() {
            $('#locations-loading').hide();
            toastr.error('Error loading locations');
        }
    });
}

// Populate location modal with hierarchy
function populateLocationModal(locations) {
    let html = '';

    function renderLocationTree(items, level = 0) {
        let result = '';
        items.forEach(function(location) {
            const indent = '&nbsp;'.repeat(level * 6);
            const isAssigned = assignedLocationIds.includes(location.id);
            const disabled = isAssigned ? 'disabled' : '';
            const badgeClass = isAssigned ? 'badge-secondary' : 'badge-primary';
            console.log(level);

            result += `
                <div class="location-item p-2 border-bottom"
                >
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            ${indent}<strong>${location.name}</strong>
                            <span class="badge ${badgeClass} ml-2">${location.type}</span>
                            ${isAssigned ? '<span class="badge badge-success ml-1">Assigned</span>' : ''}
                        </div>
                        ${location.type === 'location' ? `
                        <button type="button" class="btn btn-sm btn-outline-primary ${disabled}"
                                onclick="selectLocation(${location.id}, '${location.name}', '${location.full_path || location.name}', '${location.type}', '${location.latitude}', '${location.longitude}')"
                                ${disabled}>
                            ${isAssigned ? 'Already Added' : 'Select'}
                        </button>` : `
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="selectAllLocation(this)" data="${encodeURIComponent(JSON.stringify(location))}"
                                ${disabled}> Select All
                        </button>`}
                    </div>
                </div>
            `;

            if (location.children && location.children.length > 0) {
                result += renderLocationTree(location.children, level + 1);
            }
        });
        return result;
    }

    html = renderLocationTree(locations);
    $('#locations-container').html(html);
}

// Select a location from modal
function selectLocation(locationId, locationName, locationHierarchy, type, latitude, longitude) {
    console.log(locationId, locationName, locationHierarchy);
    const distanceInput = `
        <div class="assigned-location-item mb-3 p-3 border rounded" data-location-id="${locationId}" data-type="${type}" data-lat="${latitude}" data-lon="${longitude}">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="mb-1">${locationName}</h6>
                    <small class="text-muted">${locationHierarchy}</small>
                    <div class="mt-2">
                        <label class="mb-1">Distance (KM):</label>
                        <input type="number" step="0.01" name="location_distances[${locationId}]"
                                class="form-control form-control-sm" style="width: 100px; display: inline-block;"
                                value="0.00" required>
                        <input type="hidden" name="assigned_location_ids[]" value="${locationId}">
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeAssignedLocation(${locationId})">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
    `;

    // Add to assigned locations list
    if ($('#assigned-locations-list p.text-muted').length) {
        $('#assigned-locations-list').html('');
    }
    $('#assigned-locations-list').append(distanceInput);

    // Add to assigned location IDs array
    assignedLocationIds.push(locationId);

    // Close modal
    $('#locationSelectionModal').modal('hide');
    toastr.success('Location assigned successfully!');
    getAllDistance();
}
function selectAllLocation(element) {
    childrens = JSON.parse(decodeURIComponent(element.getAttribute('data')));

    function runThroughAll (childs) {
        childs.forEach(function(child) {
            if(child.type === 'location') {
                selectLocation(child.id, child.name, child.full_path, child.type, child.latitude, child.longitude);
            }
            if(child.children && child.children.length > 0) {
                runThroughAll(child.children);
            }
        });
    }
    runThroughAll(childrens.children);
}
// Remove assigned location
function removeAssignedLocation(locationId) {
    $(`[data-location-id="${locationId}"]`).remove();
    assignedLocationIds = assignedLocationIds.filter(id => id !== locationId);

    if ($('#assigned-locations-list').children().length === 0) {
        $('#assigned-locations-list').html('<p class="text-muted text-center">No locations assigned yet</p>');
    }

    toastr.info('Location removed from assignment.');
}

let getBikeDistanceKm_cache = {}
async function getBikeDistanceKm(lat1, lon1, lat2, lon2) {
    const url = `https://router.project-osrm.org/route/v1/bicycle/${lon1},${lat1};${lon2},${lat2}?overview=false`;
    let key = `${lat1}_${lon1}_${lat2}_${lon2}`;

    if(lat1 === lat2 && lon1 === lon2) return 0;
    if(!lat1 || !lon1 || !lat2 || !lon2) return 1;

    if(getBikeDistanceKm_cache[key]) return getBikeDistanceKm_cache[key];
    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

        const data = await response.json();
        if (data.routes && data.routes.length > 0) {
            const distance = data.routes[0].distance / 1000;
            getBikeDistanceKm_cache[key] = distance;
            return distance;
        } else {
            return null;
        }
    } catch (error) {
        toastr.error(error);
        return null;
    }
}
function getAllDistance() {
    // Example usage
    $('[data-location-id][data-type="location"]').each(async function() {
        let lat = parseFloat($(this).data('lat'));
        let lon = parseFloat($(this).data('lon'));
        let home_lat = parseFloat($('#home_latitude').val());
        let home_lng = parseFloat($('#home_longitude').val());

        $(this).find('label').html('Calculating Distance...')
        try{
            let distance = await getBikeDistanceKm(home_lat, home_lng, lat, lon)
            if (distance !== null) {
                $(this).find('input[type="number"]').val(distance.toFixed(2));
            } else {
                toastr.error("No route found.");
            }
        } catch(error) {
            toastr.error(error);
        }finally {
            $(this).find('label').html('Distance (KM):')
        }
    });
}


// Search functionality for location modal
$('#location-search').on('keyup', function() {
    const searchTerm = $(this).val().toLowerCase();
    $('.location-item').each(function() {
        const locationName = $(this).find('strong').text().toLowerCase();
        if (locationName.includes(searchTerm)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

// Initialize coordinates map if values exist
$(document).ready(function() {
    const lat = $('#home_latitude').val();
    const lng = $('#home_longitude').val();

    if (lat && lng) {
        // Only show map if the geo-locations tab is active
        if ($('#nav-geo-locations').hasClass('active')) {
            showHomeLocationMap(parseFloat(lat), parseFloat(lng));
        }
    }
});
</script>
