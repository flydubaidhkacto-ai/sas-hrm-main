@extends('layouts.master')

@section('title', user()->is_student() ? 'Outside Punch' : 'Outside Duty')

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
                {{ user()->is_student() ? 'Outside Punch' : 'Outside Duty' }}
            </h3>
            <div class="d-flex gap-2">
                <a class="btn btn-info btn-sm mr-1" id="refresh_page_check_section" onclick="location.reload();">
                    <i class="fa fa-recycle"></i> Refresh
                </a>
                <a class="btn btn-info btn-sm" id="clock_display">
                    <i class="fa fa-clock mr-1"></i>
                    <span id="clock_text">00:00:00</span>
                </a>
            </div>
        </div>
        <!-- /Page Header -->

        @php
            $user = user();
            $today = Carbon\Carbon::today();
            if(\App\Models\Setting::block_auto_inout_state()) {
                $last = \App\Models\InOutRecord::where('user_id', $user->id)
                            ->whereDate('date_time', $today)
                            ->orderBy('date_time', 'desc')
                            ->first();
                $isCheckedIn = $last && $last->status == 0;
            } else {
                $count = App\Models\InOutRecord::where('user_id', $user->id)
                            ->whereDate('date_time', $today)
                            ->count();
                $isCheckedIn = $count % 2 !== 0;
            }
        @endphp

        <div class="row justify-content-center">
            <div class="col-12 col-md-8 text-center">
                <h3>{{ $user->name }}</h3>
                <h4>{{ $user->designation?->name ?? '' }}</h4>
                <h5>{{ $user->department?->name ?? '' }}</h5>
                <h6>{{ $user?->department?->super_department?->name ?? '' }}</h6>



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
                        {{ $isCheckedIn ? 'Check Out' : 'Check In' }}
                        <br>
                        <img src="/images/{{ $isCheckedIn ? 'check-out' : 'check-in' }}.png" style="width: 25px;">
                    </span>
                </div>
                <h4 class="mt-3" id="instructionText">
                    {{ $isCheckedIn ? 'Tap to exit' : 'Tap to enter' }}
                </h4>
                <div class="spinner-border mt-2 d-none" role="status"></div>

                @if ($restricted_areas->count())
                <div class="mt-4">
                    <h4>Restricted Check In/Out Area</h4>
                    <div style="height: 250px; margin-top: 20px" id="map"></div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- /Page Wrapper -->
@endsection

@section('script')
<script>
    // const progressBar = document.getElementById('progressBar');
        const checkButton = document.getElementById('checkButton');
        const instructionText = document.getElementById('instructionText');
        const spinner = document.querySelector('.spinner-border');

        let isCheckedIn = {{ $isCheckedIn ? 'true' : 'false' }};
        let progressValue = 0;
        let intervalId;

        checkButton.addEventListener('pointerdown', handleCheck);

        function handleCheck() {
            intervalId = setInterval(() => {
                if (progressValue < 100) {
                    progressValue++;
                    // progressBar.value = progressValue;
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
            // progressBar.value = 0;
            updateProgressBar();
        }

        function updateProgressBar() {
            const color = isCheckedIn ? 'rgb(255, 64, 64)' : 'rgb(72, 255, 64)';
            checkButton.style.background = `conic-gradient(${color} ${progressValue * 3.6}deg, #ededed 0deg)`;
        }

        function sendDataToServer() {
            // Show the spinner
            spinner.classList.remove('d-none');

            const checkoutMessage = document.getElementById('reload-message-out');
            const checkinMessage = document.getElementById('reload-message-in');

            // Hide appropriate message based on `isCheckedIn`
            if (isCheckedIn) {
                // Hide check-in message if showing
                checkinMessage.classList.add('d-none');
            } else {
                // Hide check-out message if showing
                checkoutMessage.classList.add('d-none');
            }

            // Define URL based on `isCheckedIn` state
            const url = isCheckedIn ?
                "{{ route('attendence.outside.out') }}" :
                "{{ route('attendence.outside.entry') }}";

            // Fetch location
            getCurrentLocation()
                .then(({
                    latitude,
                    longitude
                }) => {
                    // Make AJAX request with actual location data
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {
                            latitude,
                            longitude
                        }, // Use fetched location data
                        success: function(response) {
                            // Hide spinner
                            spinner.classList.add('d-none');

                            if (response && response.leave === true) {
                                // Show leave message
                                document.getElementById('reload-message-leave').classList.remove('d-none');
                            } else {
                                // Show appropriate message based on `isCheckedIn`
                                const message = isCheckedIn ? 'reload-message-out' : 'reload-message-in';
                                document.getElementById(message).classList.remove('d-none');

                            }
                            // Toggle the check state
                            toggleCheckState();
                            clock_section();
                            if (!isCheckedIn) {
                                location.reload();

                            }
                            // window.working_time_counter();
                        },
                        error: function(err) {
                            // Hide spinner on error
                            spinner.classList.add('d-none');
                            // alert("An error occurred. Please try again.");
                            alert(
                                err.responseJSON && err.responseJSON.message ?
                                err.responseJSON.message : "An error occurred. Please try again."
                            )
                        }
                    });
                })
                .catch((error) => {
                    // Handle geolocation errors
                    spinner.classList.add('d-none');
                    alert(`Error fetching location: ${error}`);
                });
        }

        function toggleCheckState() {
            isCheckedIn = !isCheckedIn;
            checkButton.querySelector('.progress-value').innerHTML = isCheckedIn ?
                'Check Out<br><img src="/images/check-out.png" style="width: 30px; height: auto;">' :
                'Check In<br><img src="/images/check-in.png" style="width: 30px; height: auto;">';
            instructionText.textContent = isCheckedIn ? 'Please tap to exit' : 'Please tap to enter';
            // progressBar.value = 0;
        }


        function getCurrentLocation(useDemoData = false) {

            @if (config('app.env') == 'local')
                useDemoData = true;
            @endif


            return new Promise((resolve, reject) => {
                if (useDemoData) {
                    // Return demo data for testing purposes
                    const demoData = {
                        latitude: 1.8103,
                        longitude: 1.4125
                    }; // Example: Coordinates of Dhaka, Bangladesh
                    console.log("Returning demo data:", demoData);
                    resolve(demoData);
                } else if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const latitude = position.coords.latitude;
                            const longitude = position.coords.longitude;
                            resolve({
                                latitude,
                                longitude
                            }); // Return both lat and long
                        },
                        (error) => {
                            reject('Error occurred: ' + error.message); // Handle geolocation error
                        }
                    );
                } else {
                    reject("Geolocation is not supported by this browser.");
                }
            });
        }


        $(document).ready(function() {
            /**
             * Clock section
             */
            clock_section();
        });
</script>
@endsection
