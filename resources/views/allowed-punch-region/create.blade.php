
@extends('layouts.master')
@section('title', "Allowed Punch Region")
@section('content')
    <style>
        #map {
            width: 100%;
            height: 350px;
        }
    </style>
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            {!! Toastr::message() !!}
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Allowed Punch Region
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">
                                {{$model ? 'Edit' : 'Create'}}
                                 Region
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div>
                {!! \Brian2694\Toastr\Facades\Toastr::message() !!}
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    {{ implode('', $errors->all('<div class="alert alert-danger">:message</div>')) }}
                @endif

                <div class="card mt-2">
                    <div class="card-body">
                        <form action="{{ route('allowed-regions.store') }}" method="POST">
                            @csrf
                            @if($model)
                                <input type="hidden" name="id" value="{{ $model->id }}">
                            @endif
                            <div class="row">
                                <div class="mb-2 col-md-12">
                                    <label for="name">Name:</label>
                                    <input class="form-control" type="text" id="name" name="name" value="{{$model ? $model->name : ""}}" required min="2">
                                </div>
                                <div class="mb-2 col-md-4">
                                    <label for="latitude">Latitude:</label>
                                    <input class="form-control" type="text" id="latitude" name="latitude" value="{{$model ? $model->latitude : "23.8223482"}}" readonly>
                                </div>
                                <div class="mb-2 col-md-4">
                                    <label for="longitude" >Longitude:</label>
                                    <input class="form-control" type="text" id="longitude" name="longitude" value="{{$model ? $model->longitude : "90.3654215"}}" readonly>
                                </div>
                                <div class="mb-2 col-md-4">
                                    <label for="radius">Radius (meters):</label>
                                    <input class="form-control" type="number" id="radius" name="radius_in_meters" value="{{ $model ? $model->radius_in_meters : 100}}" min="1" required>
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-info">
                                    <i class="fa fa-check"></i>
                                    {{$model ? 'Update' : 'Save'}}
                                </button>
                            </div>
                        </form>
                        <div class="mt-2">
                            <h5>Select from map:</h5>
                            <div class="d-flex">
                                {{--                            <label for="location">Search Location:</label>--}}
                                <input type="text" id="location_search" style="width: 100%" placeholder="Enter location name" />
                                <button type="button" class="btn btn-sm btn-info" style="" id="searchLocation">Search</button>
                            </div>
                        </div>
                        <div id="map"></div>
                        <div>
                            <button onclick="setMyLocation()" class="btn btn-link">Set my Location</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        let marker, circle, map
        document.addEventListener("DOMContentLoaded", function () {
            const defaultLat = parseFloat(document.getElementById('latitude').value);
            const defaultLng = parseFloat(document.getElementById('longitude').value);
            const defaultRadius = parseInt(document.getElementById('radius').value);

            map = L.map('map').setView([defaultLat, defaultLng], 14);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
            }).addTo(map);

            // Add a draggable marker
            marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

            // Add a circle to represent the radius
            circle = L.circle([defaultLat, defaultLng], {
                radius: defaultRadius,
                color: '#0048ff',
                fillColor: '#0048ff',
                fillOpacity: 0.2,
            }).addTo(map);

            // Update latitude, longitude, and circle position when marker is dragged
            marker.on('dragend', function (e) {
                const latLng = marker.getLatLng();
                document.getElementById('latitude').value = latLng.lat.toFixed(6);
                document.getElementById('longitude').value = latLng.lng.toFixed(6);
                circle.setLatLng(latLng);
            });

            // Update circle radius when input changes
            document.getElementById('radius').addEventListener('input', function () {
                const newRadius = parseInt(this.value);
                circle.setRadius(newRadius);
            });

            // Update marker position when map is clicked
            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
                document.getElementById('longitude').value = e.latlng.lng.toFixed(6);
                circle.setLatLng(e.latlng);
            });

            // Initialize Select2
            $('#location_search').select2({
                placeholder: 'Search for a location',
                minimumInputLength: 2, // Start searching after typing 2 characters
                ajax: {
                    url: function (params) {
                        return `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(params.term)}&format=json&addressdetails=1&limit=5`;
                    },
                    dataType: 'json',
                    delay: 250, // Add delay to reduce API requests
                    processResults: function (data) {
                        // Format the response for Select2
                        return {
                            results: data.map(function (item) {
                                return {
                                    id: `${item.lat},${item.lon}`, // Combine lat and lon as the ID
                                    text: item.display_name // Display the full address in the dropdown
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            // Handle selection from the dropdown
            $('#location_search').on('select2:select', function (e) {
                const selectedData = e.params.data.id.split(',');
                const latitude = parseFloat(selectedData[0]);
                const longitude = parseFloat(selectedData[1]);

                setLocationtoMap(latitude, longitude)
            });
        });

        function setLocationtoMap(latitude, longitude)
        {
            // Update marker, circle, and input fields
            const newLatLng = [latitude, longitude];
            marker.setLatLng(newLatLng);
            circle.setLatLng(newLatLng);
            map.setView(newLatLng, 14);

            document.getElementById('latitude').value = latitude.toFixed(6);
            document.getElementById('longitude').value = longitude.toFixed(6);
        }

        function setMyLocation()
        {
            // Check if the browser supports geolocation
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }

            // Get the current location
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;

                    setLocationtoMap(latitude, longitude)
                },
                function (error) {
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            alert('Permission to access location was denied.');
                            break;
                        case error.POSITION_UNAVAILABLE:
                            alert('Location information is unavailable.');
                            break;
                        case error.TIMEOUT:
                            alert('The request to get your location timed out.');
                            break;
                        default:
                            alert('An unknown error occurred.');
                            break;
                    }
                }
            );

        }
    </script>
@endsection
