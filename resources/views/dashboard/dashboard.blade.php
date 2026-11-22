@extends('layouts.master')

@push('style')
{{--
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" /> --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />


<style>
    a.card {
        color: inherit;
    }

    a.card:hover {
        background: rgba(207, 207, 207, 0.815);
    }

    #close-notice {
        background-color: transparent;
        border: none;
        font-size: 1.2rem;
        color: #666;
        cursor: pointer;
    }

    #close-notice:hover {
        color: #ff4d4d;
    }

    .fc-day-grid-event {
        width: 100% !important;
        /* Make the event span the full width */
        display: block !important;
        /* Ensure it covers the full area */
        padding: 5px 0 !important;
        /* Add some height */
        border-radius: 5px !important;
        /* Optional: Rounded corners */
    }

    /* color section under calender */
    .legend-container {
        display: flex;
        align-items: center;
        gap: 20px;
        /* This ensures consistent spacing */
        flex-wrap: wrap;
        /* Prevents overflow */
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        /* Space between the circle and text */
    }

    .legend-circle {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }
</style>
@endpush

@section('content')
@if ($home_page_refresh)
<script>
    let refreshTimeout;
        function startAutoRefresh() {
            refreshTimeout = setTimeout(() => {
                window.location.reload()
            }, 10 * 1000);
        }

        // Start auto refresh if tab is visible
        if (document.visibilityState === 'visible') {
            startAutoRefresh();
        }

        // Handle tab visibility changes
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                startAutoRefresh();
            } else {
                clearTimeout(refreshTimeout);
            }
        });
</script>
@endif


@php
if (!function_exists('filter_role__tmp')) {
function filter_role__tmp($users)
{
if (role_can('dashboard')) {
return $users;
}
if (role_can('dashboard.self.super_department')) {
return $users->whereHas('department', function ($q) {
$q->where('super_department_id', user()->department?->super_department_id);
});
}
return $users->where('id', user()->id);
}
}
$total_employees = filter_role__tmp(\App\Models\User::employees())->count();
$total_active_employees = filter_role__tmp(\App\Models\User::active_employees())->count();
$total_absent_employees = filter_role__tmp(\App\Models\User::active_employees())
->whereHas('attendance', function ($query) {
$query->whereDate('date', today());
$query->where('got_late_check_in', true);
})
->latest()
->limit(20)
->get();
// ->count();

$today = Carbon(today());
@endphp

<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-md-9">
                    <h3 class="page-title">
                        Welcome {{ user()->name }}!
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ul>
                </div>
                <div class="col-md-3 col-sm-6">
                    <form action="" method="get" class="text-right">
                        @csrf
                        <b>Auto refresh this page?</b>
                        <select name="home_page_refresh" id="" onchange="$(this).next().next().removeClass('d-none')">
                            <option @if ($home_page_refresh) selected @endif value="1">Yes</option>
                            <option @if (!$home_page_refresh) selected @endif value="2">No</option>
                        </select>
                        <br>
                        <button class="btn btn-sm btn-info d-none">
                            Save
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        @php
        $user = user();
        @endphp
        @if ($user->birth_date && \Carbon\Carbon::parse($user->birth_date)->format('m-d') == date('m-d'))
        <div class="col-md-12 col-sm-12 col-lg-12 col-xl-6">
            <div class="card dash-widget">
                <div class="card-body"> <span class="dash-widget-icon"><i class="fas fa-birthday-cake"></i></span>
                    <div class="dash-widget-info">
                        <span>Dear <b>{{ $user->name }}</b>, We want to congratulate you on your <button id="demo"
                                style="display: none">Birthday</button> birthday. Our
                            blessings and best wishes will accompany you for the year ahead. We are happy to work by
                            your side and hope you know you have our full trust and support.</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if (cache()->get('no_need_to_show_sms_error_warning') === null && role_can('bulk-sms'))
        @php
        $last = \App\Models\SmsHistory::latest()->first();
        @endphp
        @if (
        $last &&
        (str_contains($last->status, 'failed') ||
        str_contains($last->status, '5201') ||
        str_contains($last->status, 'status code 404') ||
        str_contains($last->status, 'status code 403') ||
        str_contains($last->status, 'status code 400') ||
        str_contains($last->status, 'status code 500')))
        <div class="">
            <div class="alert alert-warning">
                <div class="">
                    <div class="">
                        <div class="">
                            <h4>SMS API Not working correctly</h4>
                            <p>
                                SMS Api is responsible for sending bulk sms and employee notification messages.
                                The API might be misconfigured or not working correctly.
                                <br>
                                <br>
                                <a href="{{ route('sms.api') }}" class="btn btn-info btn-sm">
                                    <i class="fa fa-arrow-right"></i> Go to SMS API Settings
                                </a>
                                <a href="{{ route('home', ['no_need_to_show_sms_error_warning' => 1]) }}"
                                    class="btn-link text-danger ml-2">
                                    Don't show this warning again!
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif



        @if (App\Models\ZkDevice::count() === 0 && cache()->get('no-need-of-zkteco') === null && role_can('api_key'))
        <div class="">
            <div class="card dash-widget">
                <div class="row">
                    <div class="col-sm-2 d-none d-sm-block">
                        <img src="/images/devices.png" alt="" class="w-100">
                    </div>
                    <div class="col-sm-9">
                        <div class="p-3">
                            <h1>Connect your ZkTeco Attendance Terminal</h1>
                            <p>
                                ZkTeco Terminals are used to track attendance and entry of the office or production
                                house employees.
                                <br>
                                You can connect your terminal directly with our software. We will automatically sync
                                your employee data with our server.
                                <br>
                                <br>
                                <a href="#" class="btn btn-info btn-sm">
                                    <i class="fa fa-play"></i> Watch How to connect
                                </a>
                                <a href="{{ route('zkteco') }}" class="btn btn-secondary btn-sm">
                                    <i class="fa fa-arrow-right"></i> Go to ZkTeco Settings
                                </a>
                                <br>
                                <br>
                                <a href="{{ route('zkteco', ['no_need_of_zkteco' => 1]) }}"
                                    class="btn-link text-danger">
                                    I don't need this feature
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if (has_module('base_module'))
        @include('dashboard.parts.birthday-card')
        @include('dashboard.parts.admin-cards')
        @endif

        <div class="row">
            <div class="col-md-6">
                @if (role_can('dashboard') || role_can('dashboard.self') || role_can('dashboard.self.super_department'))
                <div class="">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">
                                Latest Machine punches:
                            </h4>
                            @php
                            $punches = \App\Models\InOutRecord::with([
                            'employee.designation',
                            'employee.department.super_department',
                            ]);
                            if (!role_can('dashboard')) {
                            if (role_can('dashboard.self.super_department')) {
                            $punches->whereHas('employee', function ($q1) {
                            $q1->whereHas('department', function ($q) {
                            $q->where(
                            'super_department_id',
                            user()->department?->super_department_id,
                            );
                            });
                            });
                            } else {
                            $punches->where('user_id', user()->id);
                            }
                            }
                            $punches = $punches->latest()->limit(10)->paginate(15);
                            @endphp
                            @include('reports.machine-log-table', [
                            'data' => $punches,
                            ])
                            <div class="load-more mt-2 text-center">
                                <a class="text-dark" href="{{ route('reports.machine-log') }}">See Machine
                                    logs</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="col-md-6">
                <div class="row">
                    {{-- Callendar --}}

                    <div class="card dash-widget">
                        <h4 class="card-header">
                            {{-- Holidays: --}}
                            Official Calendar
                        </h4>
                        <div class="card-body">
                            <div id="calendar" class="mx-auto"></div>

                            <!-- Legend -->
                            <div class="mt-3 legend-container">
                                <div class="legend-item">
                                    <span class="legend-circle" style="background-color: #FF0000;"></span>
                                    <span>Holiday</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-circle" style="background-color: #ff5733;"></span>
                                    <span>Roster</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-circle" style="background-color: #3357ff;"></span>
                                    <span>Shift Schedule</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-circle" style="background-color: #28a745;"></span>
                                    <span>General Working Day</span>
                                </div>
                            </div>


                        </div>

                    </div>

                </div>
            </div>
        </div>



    </div>
</div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script type="module" src="{{ asset('assets/plugins/dynamic-event-calendar-bootstrap/js/main.js') }}"></script>
<script src="{{ asset('assets/js/confetti.min.js') }}"></script>
@endpush

@section('script')
<script>
    $(document).ready(function() {
            var calendarUrl = "{{ route('calendar') }}";
            var currentDate = new Date();
            var month = currentDate.getMonth() + 1;
            var year = currentDate.getFullYear();

            $('#calendar').fullCalendar({
                events: calendarUrl + "?month=" + month + "&year=" + year,
                displayEventTime: false,
                editable: false,
                selectable: false,
                eventRender: function(event, element) {
                    if (event.color) {
                        element.css('background-color', event.color);
                        element.css('border-color', event.color);
                    }
                    element.find('.fc-title').remove(); // Remove event title (text inside date)
                }
            });
        });
</script>
@endsection