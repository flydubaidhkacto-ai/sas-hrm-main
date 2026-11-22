@extends('layouts.master')

@section('title', "Check In by Location")

@push('style')
<style>
    @media (max-width: 768px) {
        .page-header {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }

        .circular-progress {
            width: 120px;
            height: 120px;
            font-size: 14px;
        }

        .progress-value img {
            width: 20px;
        }
    }

    .leaflet-marker-icon.green {
        background-color: green !important;
    }

    .leaflet-marker-icon.red {
        background-color: red !important;
    }

    .schedule-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .eligible-locations-list {
        background-color: rgba(255,255,255,0.1);
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
    }

    .location-badge {
        background-color: rgba(255,255,255,0.9);
        color: #495057;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        margin: 0.25rem;
        display: inline-block;
        font-size: 0.875rem;
    }
</style>
@endpush

@section('content')

{{-- Display Toastr Messages --}}
{!! Toastr::message() !!}
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h3 class="page-title">
                Auto Check-In by Location
            </h3>
            <div class="d-flex gap-2">
                <a class="btn btn-info btn-sm mr-1" href="{{ route('employees.weekly-schedules.show') }}">
                    <i class="fa fa-calendar"></i> My Schedule
                </a>
                <a class="btn btn-secondary btn-sm mr-1" onclick="location.reload();">
                    <i class="fa fa-recycle"></i> Refresh
                </a>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Today's Schedule Info -->
        @if ($has_schedule)
        <div class="schedule-info-card">
            <h4 class="mb-2">
                <i class="fa fa-calendar-check mr-2"></i>
                Today's Schedule - {{ $today_weekday }}
            </h4>
            <p class="mb-0">You have {{ $eligible_locations->count() }} eligible location(s) for today</p>
            <div class="eligible-locations-list">
                <h6 class="mb-2">Eligible Locations:</h6>
                @foreach($eligible_locations as $location)
                    <span class="location-badge">
                        <i class="fa fa-map-marker mr-1"></i>{{ $location->fullPath }}
                        @if($location->pivot && $location->pivot->distance_km)
                            ({{ $location->pivot->distance_km }}km)
                        @endif
                    </span>
                @endforeach
            </div>
        </div>
        @endif

        <div class="row">
            @if ($eligible_locations->count() > 0)
                <div class="col-md-6 text-center checkin-container">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Auto Check-In</h5>
                            @if ($has_schedule)
                                <small class="text-info">
                                    <i class="fa fa-info-circle"></i>
                                    System will automatically detect your eligible location
                                </small>
                            @else
                                <small class="text-warning">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    No schedule set - checking all assigned locations
                                </small>
                            @endif
                        </div>
                        <div class="card-body">
                            <h5 class="alert alert-success d-none" id="reload-message-in">
                                <img src="/images/check-in.png" style="width: 30px;"> Check In Successful
                            </h5>
                            <h5 class="alert alert-danger d-none" id="reload-message-out">
                                <img src="/images/check-out.png" style="width: 30px;"> Check Out Successful
                            </h5>
                            <h5 class="alert alert-danger d-none" id="reload-message-leave">
                                Active leave today, check-in not allowed.
                            </h5>

                            <div id="checkButton" class="circular-progress mx-auto mt-4" style="cursor: pointer;">
                                <span class="progress-value">
                                    Auto Check In
                                    <br>
                                    <img src="/images/check-in.png" style="width: 25px;">
                                </span>
                            </div>
                            <h4 class="mt-3" id="instructionText">
                                Press & Hold to Auto Check-In
                            </h4>
                            <div class="form-group text-left">
                                <label for="">Note:</label>
                                <textarea class="form-control note" name="note" rows="2" placeholder="Optional note for check-in..."></textarea>
                            </div>
                            <div class="spinner-border mt-2 d-none" role="status"></div>

                            @if ($last_checkin && $last_checkin->check_at->isToday())
                                <div class="alert alert-info mt-2">
                                    <i class="fa fa-info-circle"></i>
                                    Last check-in today at <strong>{{ $last_checkin->location_name }}</strong>
                                    on {{ $last_checkin->created_at->format('h:i A') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <h6><i class="fa fa-exclamation-triangle"></i> No Eligible Locations</h6>
                                <p class="mb-2">You have no locations scheduled for {{ $today_weekday }}.</p>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('employees.weekly-schedules.show') }}" class="btn btn-info btn-sm">
                                        <i class="fa fa-calendar"></i> View Schedule
                                    </a>
                                    @if (!$has_schedule)
                                        <span class="text-muted">or contact HR to set up your weekly schedule</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($eligible_locations->count() > 0)
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Eligible Locations Map</h5>
                            <small class="text-muted">Your position and today's eligible locations</small>
                        </div>
                        <div class="card-body p-0">
                            <div style="height: 350px;" id="map"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if ($eligible_locations->count() > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Location Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Location</th>
                                            <th>Hierarchy</th>
                                            <th>Distance from Home</th>
                                            <th>Radius</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($eligible_locations as $location)
                                            <tr>
                                                <td><strong>{{ $location->name }}</strong></td>
                                                <td><small class="text-muted">{{ $location->getFullHierarchy() }}</small></td>
                                                <td>
                                                    @if($location->pivot && $location->pivot->distance_km)
                                                        <span class="badge badge-info">{{ $location->pivot->distance_km }}km</span>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td><span class="badge badge-primary">{{ $location->radius }}m</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
<!-- /Page Wrapper -->
@endsection

@section('script')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    #map {
        height: 350px;
        width: 100%;
    }

    .status-badge.leaflet-bar {
        padding: 8px 10px;
        border-radius: 10px;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
    }
</style>
<script>
    let eligible_locations = {!! $eligible_locations !!};

    /**
     * Initialize the check-in map.
     */
    function initCheckinMap(userCoords, eligibleLocations) {
        // Initialize the map
        const map = L.map('map').setView([userCoords.lat, userCoords.lng], 15);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'ProHR'
        }).addTo(map);

        // Add marker for user's location
        const userMarker = L.marker([userCoords.lat, userCoords.lng], {
            icon: L.divIcon({
                className: 'user-marker',
                html: '<div style="background-color: #4285F4; width: 24px; height: 24px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); display: flex; justify-content: center; align-items: center; color: white; font-weight: bold;">U</div>',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            })
        }).addTo(map);

        userMarker.bindPopup("Your Current Location").openPopup();

        // Add circles for each eligible location
        let userIsWithinAnyLocation = false;
        eligibleLocations.forEach(location => {
            const circle = L.circle([location.latitude, location.longitude], {
                color: '#4CAF50',
                fillColor: '#4CAF50',
                fillOpacity: 0.3,
                radius: location.radius
            }).addTo(map);

            // Check if user is within this area
            const distance = map.distance(
                [userCoords.lat, userCoords.lng],
                [location.latitude, location.longitude]
            );

            if (distance <= location.radius) {
                userIsWithinAnyLocation = true;
                circle.setStyle({color: '#FF9800', fillColor: '#FF9800'});
                circle.bindPopup(`
                    <b>✓ You are in this area</b><br>
                    <strong>${location.name}</strong><br>
                    Radius: ${location.radius}m<br>
                    <span style="color: green;">Ready for check-in!</span>
                `);
            } else {
                circle.bindPopup(`
                    <b>${location.name}</b><br>
                    Radius: ${location.radius}m<br>
                    Distance: ${Math.round(distance)}m away
                `);
            }
        });

        // Add legend
        const legend = L.control({position: 'bottomright'});
        legend.onAdd = function() {
            const div = L.DomUtil.create('div', 'info legend');
            div.style.background = '#fff';
            div.style.padding = '8px';
            div.style.borderRadius = '5px';
            div.style.boxShadow = '0 2px 4px rgba(0,0,0,0.2)';
            div.innerHTML = `
                <div style="display: flex; align-items: center; margin: 5px 0;">
                    <div style="background: #4285F4; width: 15px; height: 15px; border-radius: 50%; border: 1px solid white; margin-right: 8px;"></div>
                    Your Location
                </div>
                <div style="display: flex; align-items: center; margin: 5px 0;">
                    <div style="background: #4CAF50; width: 15px; height: 15px; border-radius: 50%; opacity: 0.3; margin-right: 8px;"></div>
                    Eligible Areas
                </div>
                <div style="display: flex; align-items: center; margin: 5px 0;">
                    <div style="background: #FF9800; width: 15px; height: 15px; border-radius: 50%; opacity: 0.3; margin-right: 8px;"></div>
                    You're in this Area
                </div>
            `;
            return div;
        };
        legend.addTo(map);

        return { map, userIsWithinAnyLocation };
    }

    /**
     * Check if user is within a location's radius
     */
    function checkLocationDistance(userCoords, location) {
        const toRad = value => value * Math.PI / 180;
        const R = 6371; // Earth's radius in km
        const φ1 = toRad(userCoords.latitude);
        const φ2 = toRad(location.latitude);
        const Δφ = toRad(location.latitude - userCoords.latitude);
        const Δλ = toRad(location.longitude - userCoords.longitude);

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        const distance = R * c;

        const radiusKm = location.radius / 1000;
        return (distance <= radiusKm ? 0 : distance).toFixed(2);
    }

    // Check-in functionality
    const checkButton = document.getElementById('checkButton');
    const instructionText = document.getElementById('instructionText');
    const spinner = document.querySelector('.spinner-border');
    let progressValue = 0;
    let intervalId;

    checkButton?.addEventListener('pointerdown', handleCheck);

    function handleCheck() {
        intervalId = setInterval(() => {
            if (progressValue < 100) {
                progressValue++;
                updateProgressBar();
            } else {
                clearInterval(intervalId);
                progressValue = 0;
                sendDataToServer();
            }
        }, 10);

        checkButton.addEventListener('pointerup', resetProgress);
        checkButton.addEventListener('touchend', resetProgress);
    }

    function resetProgress() {
        clearInterval(intervalId);
        progressValue = 0;
        updateProgressBar();
    }

    function updateProgressBar() {
        const color = 'rgb(72, 255, 64)';
        checkButton.style.background = `conic-gradient(${color} ${progressValue * 3.6}deg, #ededed 0deg)`;
    }

    function sendDataToServer() {
        // Show the spinner
        spinner.classList.remove('d-none');

        const checkinMessage = document.getElementById('reload-message-in');
        checkinMessage.classList.add('d-none');

        // Get current location
        getCurrentLocation()
            .then(({latitude, longitude}) => {
                // Make AJAX request with user coordinates
                $.ajax({
                    url: "{{ route('locations.punch') }}",
                    type: "POST",
                    data: {
                        user_latitude: latitude,
                        user_longitude: longitude,
                        note: $('textarea.note').val() || ''
                    },
                    success: function(response) {
                        spinner.classList.add('d-none');

                        // Show success message
                        checkinMessage.classList.remove('d-none');
                        checkButton.classList.add('d-none');
                        instructionText.classList.add('d-none');
                        $('textarea.note').val('');

                        // Show success SweetAlert
                        Swal.fire({
                            icon: 'success',
                            title: 'Check-In Successful!',
                            text: `You have been checked in at ${response.location}`,
                            timer: 3000,
                            showConfirmButton: false
                        });
                    },
                    error: function(err) {
                        spinner.classList.add('d-none');

                        if (err.responseJSON) {
                            const response = err.responseJSON;

                            if (response.type === 'no_locations') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'No Eligible Locations',
                                    text: response.message,
                                    confirmButtonText: 'View Schedule',
                                    showCancelButton: true,
                                    cancelButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = "{{ route('employees.weekly-schedules.show') }}";
                                    }
                                });
                            } else if (response.type === 'outside_radius') {
                                let locationsList = response.eligible_locations.map(loc =>
                                    `• ${loc.name}: ${loc.distance_km}km away`
                                ).join('<br>');

                                Swal.fire({
                                    icon: 'info',
                                    title: 'Not Within Location Radius',
                                    html: `${response.message}<br><br><strong>Distances to eligible locations:</strong><br>${locationsList}`,
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Check-In Failed',
                                    text: response.message || 'An error occurred. Please try again.'
                                });
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Check-In Failed',
                                text: 'An error occurred. Please try again.'
                            });
                        }
                    }
                });
            })
            .catch((error) => {
                spinner.classList.add('d-none');
                Swal.fire({
                    icon: 'error',
                    title: 'Location Error',
                    text: `Error getting your location: ${error}`
                });
            });
    }

    function getCurrentLocation(useDemoData = false) {
        return new Promise((resolve, reject) => {
            if (useDemoData) {
                const demoData = {
                    latitude: 23.8103,
                    longitude: 90.4125
                };
                console.log("Returning demo data:", demoData);
                resolve(demoData);
            } else if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;
                        resolve({latitude, longitude});
                    },
                    (error) => {
                        reject('Please allow location permission! Error: ' + error.message);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 60000
                    }
                );
            } else {
                reject("Geolocation is not supported by this browser.");
            }
        });
    }

    $(document).ready(async function() {
        @if ($eligible_locations->count() > 0)
            try {
                // Get user location and initialize map
                let userLocation = await getCurrentLocation();
                const mapData = initCheckinMap({
                    lat: userLocation.latitude,
                    lng: userLocation.longitude
                }, eligible_locations.map((location) => {
                    return {
                        id: location.id,
                        name: location.name,
                        latitude: Number(location.latitude),
                        longitude: Number(location.longitude),
                        radius: Number(location.radius)
                    };
                }));

                // Update instruction text based on location status
                let withinAnyLocation = false;
                eligible_locations.forEach(location => {
                    const distance = checkLocationDistance(
                        {latitude: userLocation.latitude, longitude: userLocation.longitude},
                        {
                            latitude: parseFloat(location.latitude),
                            longitude: parseFloat(location.longitude),
                            radius: location.radius
                        }
                    );

                    if (distance == 0.00) {
                        withinAnyLocation = true;
                    }
                });

                if (withinAnyLocation) {
                    $("#instructionText").html('Ready to Check-In<br><small class="text-success">You are within an eligible location</small>');
                    $("#checkButton .progress-value").html('Check In<br><img src="/images/check-in.png" style="width: 25px;">');
                } else {
                    $("#instructionText").html('Move to Eligible Location<br><small class="text-warning">You are outside all eligible locations</small>');
                    $("#checkButton .progress-value").html('Not in Range<br><i class="fa fa-map-marker-alt" style="font-size: 25px;"></i>');
                }

            } catch (error) {
                console.error('Error initializing location:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Location Access Required',
                    text: 'Please allow location access to use the check-in feature.',
                    confirmButtonText: 'Retry'
                }).then(() => {
                    location.reload();
                });
            }
        @endif
    });
</script>
@endsection
