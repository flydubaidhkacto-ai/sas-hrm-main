@php
    $info = $company_info;
@endphp
<!---
Developer: Md Ariful Islam
Company: ArifDev & ProSoft
Version: {{ config('app.version') }}
Location: Dhaka 1216, Bangladesh
For Support:
https://prosoft-bd.com
--->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
    <meta name="description" content="HR & Payroll System for Employee Management">
    <meta name="keywords"
        content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern, accounts, invoice, html5, responsive, CRM, Projects">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') - Pro HR {{ config('app.version') }}</title>
    <!-- Favicon -->
    {{-- <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"> --}}
    <link rel="shortcut icon" type="image/x-icon"
        href="{{ $info['favicon'] ?? null ? '/uploaded_files/' . $info['favicon'] : '/favicon.ico' }}">
    <link rel="icon" type="image/x-icon"
        href="{{ $info['favicon'] ?? null ? '/uploaded_files/' . $info['favicon'] : '/favicon.ico' }}">


    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap.min.css') }}">
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/font-awesome.min.css') }}">
    <!-- Lineawesome CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/line-awesome.min.css') }}">
    <!-- Datatable CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/dataTables.bootstrap4.min.css') }}">


    <link rel="stylesheet" href="{{ asset('assets/plugins/dynamic-event-calendar-bootstrap/css/calendar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/dynamic-event-calendar-bootstrap/css/spinner.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/dynamic-event-calendar-bootstrap/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/dynamic-event-calendar-bootstrap/css/theme.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/notification.css') }}">

    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/select2.min.css') }}">
    <!-- Datetimepicker CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap-datetimepicker.min.css') }}">
    <!-- Chart CSS -->
    {{--
    <link rel="stylesheet" href="{{ URL::to('ssets/plugins/morris/morris.css') }}"> --}}
    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/style.css?v=' . config('app.version')) }}">

    <link rel="stylesheet" href="{{ asset('assets/plugins/multi-select-tag-main/dist/css/multi-select-tag.css') }}">

    {{-- message toastr --}}
    <link rel="stylesheet" href="{{ URL::to('assets/css/toastr.min.css') }}">
    <script src="{{ URL::to('assets/js/toastr_jquery.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/toastr.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('/assets/fontawesome/css/all.min.css') }}">

    @routes

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @endif

    <style>
        div {
            white-space: wrap;
        }

        #employee_active_time_section {
            font-family: 'Courier New', Courier, monospace;
            /* Digital clock style */
            font-size: 1.2rem;
            /* Font size suitable for a navbar */
            font-weight: bold;
            color: #ffffff;
            /* White text for visibility */
            background-color: #000000;
            /* Black background for a digital clock look */
            padding: 5px 12px;
            /* Padding for a balanced appearance */
            border-radius: 5px;
            /* Rounded corners for a modern touch */
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
            /* Subtle shadow for depth */
            letter-spacing: 1px;
            /* Space between characters for readability */
            text-align: center;
            /* Centers the time inside the span */
            display: inline-block;
            /* Ensures proper alignment */
        }
    </style>
    @stack('style')

</head>

<body onkeyup="try{ calculateDuration();}catch(e){}" onclick="try{calculateDuration();}catch(e){}">
    <style>
        .invalid-feedback {
            font-size: 14px;
        }
    </style>
    <!-- Main Wrapper -->
    <div class="main-wrapper">

        <!-- Loader -->
        {{-- <div id="loader-wrapper">
            <div id="loader">
                <div class="loader-ellips">
                    <span class="loader-ellips__dot"></span>
                    <span class="loader-ellips__dot"></span>
                    <span class="loader-ellips__dot"></span>
                    <span class="loader-ellips__dot"></span>
                </div>
            </div>
        </div> --}}
        <!-- /Loader -->

        <!-- Header -->
        @include('layouts.header')
        @include('sidebar.sidebar')
        <div style="padding-bottom: 50px">
            @include('layouts.license-warning')
            @yield('content')
        </div>
    </div>

    @include('layouts.modals')

    <!-- jQuery -->
    <script src="{{ URL::to('assets/js/jquery-3.5.1.min.js') }}"></script>
    <!-- Bootstrap Core JS -->
    <script src="{{ URL::to('assets/js/popper.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap.min.js') }}"></script>
    <!-- Chart JS -->
    {{-- <script src="{{ URL::to('assets/plugins/morris/morris.min.js') }}"></script>
    <script src="{{ URL::to('assets/plugins/raphael/raphael.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/chart.js') }}"></script>
    <script src="{{ URL::to('assets/js/Chart.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/line-chart.js') }}"></script> --}}
    <!-- Slimscroll JS -->
    {{-- <script src="{{ URL::to('assets/js/jquery.slimscroll.min.js') }}"></script> --}}
    <!-- Select2 JS -->
    <script src="{{ URL::to('assets/js/select2.min.js') }}"></script>
    <!-- Datetimepicker JS -->
    <script src="{{ URL::to('assets/js/moment.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <!-- Datatable JS -->
    {{-- <script src="{{ URL::to('assets/js/jquery.dataTables.min.js') }}"></script> --}}
    {{-- <script src="{{ URL::to('assets/js/dataTables.bootstrap4.min.js') }}"></script> --}}
    <!-- Multiselect JS -->
    <script src="{{ URL::to('assets/js/multiselect.min.js') }}"></script>


    <!-- Custom JS -->
    <script>
        window.environment = "{{ config('app.env') }}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });
    </script>
    <script src="{{ URL::to('assets/js/app.js?v=1.2') }}"></script>
    <!-- Sidebar Submenu Fix -->
    <script src="{{ URL::to('assets/js/sidebar-fix.js') }}"></script>
    @yield('script')
    @include('layouts.select2')
    <script>
        const $button = document.getElementById('button');
        const $bell = document.getElementById('notification');
        const count = document.getElementById('count') ? document.getElementById('count').text() : ''
        // $bell.setAttribute('data-count', count);
    </script>

    @stack('js')

    <script>
        @if (\Illuminate\Support\Facades\Route::has('attendence.check_employee_schedule'))
            function clock_section() {
                $.ajax({
                    url: "{{ route('attendence.check_employee_schedule') }}",
                    type: 'GET',
                    success: function(response) {
                        let totalSeconds = parseInt(response.active_time);

                        function formatTime(seconds) {
                            let hours = Math.floor(seconds / 3600);
                            let minutes = Math.floor((seconds % 3600) / 60);
                            let secs = seconds % 60;

                            return `${hours < 10 ? '0' + hours : hours}:${minutes < 10 ? '0' + minutes : minutes}:${secs < 10 ? '0' + secs : secs}`;
                        }
                        // document.getElementById('clock_text').textContent = formatTime(totalSeconds);
                        console.log(totalSeconds);
                        document.getElementsByClassName('clock_text_class').textContent = formatTime(
                            totalSeconds);

                        const currentTime = new Date();
                        const startTime = new Date(response.start_time);
                        const endTime = new Date(response.end_time);

                        let timer;

                        if (response.start_watch && currentTime >= startTime && currentTime <= endTime) {
                            clearInterval(timer);

                            timer = setInterval(function() {
                                totalSeconds++;

                                document.getElementById('clock_text').textContent = formatTime(
                                    totalSeconds);
                            }, 1000);
                        }
                    }
                });
            }
        @endif
        // weather section
        // This function will automatically be called when the page loads
        function fetchWeather() {
            // Check if the browser supports geolocation
            let cacheKey = 'weather_location';
            let cachedData = localStorage.getItem(cacheKey);
            if (cachedData) {
                const data = JSON.parse(cachedData);
                if (data.timestamp > Date.now() - 1000 * 60 * 10) {
                    return getWeatherForLocation(data.lat, data.lon);
                }
            }
            if(
                localStorage.getItem('weather_location')
            ) {
                let data = JSON.parse(localStorage.getItem('weather_location'))
                if (data.timestamp > Date.now() - 1000 * 60 * 10) {
                    return getWeatherForLocation(
                        data.data.lat, data.data.lon
                    )
                }
            }
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(success, error);
            } else {
                console.log("Geolocation is not supported by this browser.");
                // Fallback to demo location if geolocation is not supported
                getWeatherForLocation(23.6850, 90.3563); // Bangladesh demo location
            }
        }

        function success(position) {
            const lat = position.coords.latitude; // Get live latitude
            const lon = position.coords.longitude; // Get live longitude
            const cacheKey = `weather_location`;

            const cacheData = {
                data: {
                    lat: lat,
                    lon: lon
                },
                timestamp: Date.now()
            };
            localStorage.setItem(cacheKey, JSON.stringify(cacheData));
            // Call the getWeatherForLocation function with live coordinates
            getWeatherForLocation(lat, lon);
        }

        function error() {
            // console.log("Unable to retrieve location. Falling back to demo location.");
            // Fallback to demo location if geolocation fails
            getWeatherForLocation(23.6850, 90.3563); // Bangladesh demo location
        }

        function getWeatherForLocation(lat, lon) {
            function gotData (data) {
                if (data.error) {
                    console.log("Error:", data.error);
                } else {
                    const temp = Math.round(data.main.temp); // Get temperature
                    const icon = data.weather[0].icon; // Get weather icon
                    const iconUrl = `https://openweathermap.org/img/wn/${icon}.png`;

                    const cityName = data.name; // City name
                    const countryName = data.sys.country; // Country name

                    // Update the weather icon, temperature, and location dynamically
                    $('#weather_icon').attr('src', iconUrl);
                    $('#weather_temp').text(`${temp}°C`);
                    $('#weather_location').text(`${cityName}, ${countryName}`);
                }
            }
            // check cache
            const cacheKey = `weather_${lat}_${lon}`;
            const cachedData = localStorage.getItem(cacheKey);
            if (cachedData) {
                const data = JSON.parse(cachedData);
                if (data.timestamp > Date.now() - 1000 * 60 * 10) {
                    return gotData(data.data);
                }
            }

            // Generate the URL for the weather route using Laravel's named route
            const weatherUrl = '{{ route('weather') }}'; // Using Blade syntax to generate the route URL

            // Send the request to the Laravel backend using the lat and lon
            $.getJSON(weatherUrl, {
                lat: lat,
                lon: lon
            }, function(data) {
                // Log the response for debugging purposes
                // console.log("Weather Data:", data);
                const cacheData = {
                    data: data,
                    timestamp: Date.now()
                };
                localStorage.setItem(cacheKey, JSON.stringify(cacheData));
                gotData(data);
            }).fail(function() {
                console.log("Failed to fetch weather data.");
            });
        }

        $(document).ready(function() {
            // Realtime Clock
            $(function() {
                function updateClock() {
                    const now = new Date();
                    let hours = now.getHours();
                    const minutes = now.getMinutes().toString().padStart(2, '0');
                    const seconds = now.getSeconds().toString().padStart(2, '0');
                    const ampm = hours >= 12 ? 'PM' : 'AM';

                    hours = hours % 12 || 12; // Convert 24-hour to 12-hour format

                    $('#clock_time').text(`${hours}:${minutes}:${seconds} ${ampm}`);
                }

                updateClock(); // Initial call
                setInterval(updateClock, 1000);
            });



            fetchWeather(); // Fetch weather when the page loads

            // Set an interval to update weather every 10 minutes (600000 ms)
            setInterval(fetchWeather, 600000);
        });
    </script>
    <style>
        .footer-branding {
            position: fixed;
            bottom: 0;
            height: 42px;
            width: 100%;
            text-align: end;
            background: #fff;
            padding: 5px 20px;
            padding-top: 10px;
        }
    </style>
    <footer class="text-muted footer-branding mt-2 pb-2">
        Developed By <a href="https://prosoft-bd.com" target="_blank">ProSoft</a>
        <!-- and ArifDev.com (Md. Ariful Islam) -->
        @php
            echo 'v' . config('app.version');
        @endphp
    </footer>
</body>

</html>
