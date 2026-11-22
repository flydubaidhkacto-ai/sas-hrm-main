@php
$ui_setting_info = \App\Models\Setting::where('key', 'ui_setting')->first();

$ui_info = $ui_setting_info ? json_decode($ui_setting_info->value, true) : [];

$navbarColors = $ui_info['navbar_background_color'] ?? '';
// $text_color = $ui_info['text_color'] ?? '';
$sidebar_color_left = $ui_info['navbar_background_color_left'] ?? '';
$sidebar_color_right = $ui_info['navbar_background_color_right'] ?? '';
$gradientColors = "$sidebar_color_left, $sidebar_color_right";
@endphp

<style>
    /* :root {
        --gradient_color: {{ $gradientColors }};
    } */


    /* background-color: var(--{{ $sidebar_color_left }});
    color: var(--{{ $sidebar_color_left }});
    border: var(--{{ $sidebar_color_left }}); */


    .header {
        background: linear-gradient(to right, {
                {
                $gradientColors
            }
        });
    }

    /* Combined Glass Effect Section */
    #glass_effect_section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        /* Space out the weather and clock sections */
        padding: 8px 15px;
        /* Refined padding */
        height: 45px;
        /* Slightly smaller height */
        border-radius: 12px;
        /* More subtle rounded corners */
        backdrop-filter: blur(12px);
        /* Slightly stronger blur for the glass effect */
        background: rgba(255, 255, 255, 0.15);
        /* More subtle transparency */
        color: #fff;
        /* White text to stand out on glass effect */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        /* Slightly stronger shadow for more depth */
        transition: all 0.3s ease;
        /* Smooth transition for hover effect */
        margin: 0 auto;
        /* Centers the section horizontally */
    }

    /* Hover effect to enhance the glass effect */
    #glass_effect_section:hover {
        background: rgba(255, 255, 255, 0.25);
        /* Slightly stronger background on hover */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
        /* Stronger shadow on hover */
    }

    /* Weather Section Styling */
    #weather_section {
        display: flex;
        align-items: center;
    }

    /* Animation for Weather Icon */
    .weather-icon {
        width: 28px;
        /* Slightly smaller icon size */
        height: 28px;
        margin-right: 8px;
        opacity: 0;
        /* Start with the icon hidden */
        transform: scale(0.8);
        /* Start smaller */
        animation: fadeIn 2s forwards 2s;
        /* Fade in and scale up with a delay of 2s */
    }

    @keyframes fadeIn {
        0% {
            opacity: 0;
            transform: scale(0.8);
            /* Smaller at the start */
        }

        100% {
            opacity: 1;
            transform: scale(1);
            /* Full size at the end */
        }
    }

    .weather-temp {
        font-weight: bold;
        font-size: 16px;
        color: #fff;
    }

    .weather-location {
        font-size: 12px;
        color: #fff;
    }

    /* Real-time Clock Section Styling */
    #real_time_clock {
        display: flex;
        align-items: center;
        font-size: 14px;
        color: #fff;
        text-decoration: none;
        transition: color 0.3s ease;
        /* Smooth transition for clock text */
    }

    #real_time_clock:hover {
        color: #f1c40f;
        /* Gold color on hover for visual interest */
    }

    #clock_time {
        font-size: 16px;
        font-weight: bold;
        margin-left: 5px;
        color: #fff;
    }

    /* Add some margin to the nav items if needed */
    .nav-item {
        margin-right: 15px;
    }

    /* Media Query for Mobile View */
    @media (max-width: 768px) {

        /* Hide the glass effect section on mobile */
        #glass_effect_section {
            display: none !important;
            /* Hides the whole section on mobile screens */
        }
    }

    @media (max-width: 991px) {
        .log-cont {
            margin-left: 40px;
        }
    }

    .notification-count {
        position: absolute;
        top: 5px;
        right: 5px;
        font-size: 12px;
        padding: 3px 7px;
        border-radius: 50%;
    }

    .notification-dropdown {
        min-width: 300px;
        max-width: 400px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    .notification-dropdown .dropdown-header {
        padding: 10px 15px;
        font-weight: bold;
        background: #f8f9fa;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notification-item {
        padding: 10px 15px;
        border-bottom: 1px solid #e9ecef;
        transition: background 0.3s ease-in-out;
    }

    .notification-item:hover {
        background: #f8f9fa;
    }

    .unread {
        background: #e3f2fd;
    }

    .notification-item i {
        cursor: pointer;
        font-size: 16px;
    }

    .notification-item a {
        font-size: 14px;
    }

    .company-name-header {
        font-family: 'Ballantines Script', cursive;
        font-size: 30px;
        color: #fff;
        white-space: nowrap;
        letter-spacing: 0.3px;
    }

    @media (max-width: 991px) {
        .company-name-header {
            font-size: 18px;
        }

        #toggle_btn {
            display: none;
        }
    }
</style>


<div class="header">
    <a id="toggle_btn" href="javascript:void(0);">
        <span class="bar-icon">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </a>
    <!-- Logo -->
    <div class="header-left">
        <div class="log-cont" style="width: auto; height: 100%; display: flex; align-items: center; gap: 8px;">
            <a href="{{ route('home') }}" class="logo">
                <img src="{{ $info['logo'] ?? false ? '/uploaded_files/' . $info['logo'] : '/assets/images/photo_defaults.jpg' }}"
                    alt="ProHR">
            </a>
            <span class="company-name-header">Sky Aviation Services Ltd.</span>
        </div>
    </div>
    <!-- /Logo -->

    <!-- Header Title -->
    {{-- <div class="page-title-box">
        <h3>Hi, {{ user()->name }}</h3>
    </div> --}}
    <!-- /Header Title -->
    <a id="mobile_btn" class="mobile_btn" href="#sidebar"><i class="fa fa-bars"></i></a>
    <!-- Header Menu -->
    <ul class="nav user-menu">
        <li class="nav-item d-none">
            @if (user()->role == 'employee' || user()->role == 'student')
            <span class="timer" id="employee_active_time_section">00:00:00</span>
            @endif
            {{-- <button id="button" class="notification-btn">Send notification</button> --}}
        </li>
        <li class="nav-item mt-2">
            <!-- Combined Glass Effect Section for Weather and Clock -->
            <div id="glass_effect_section" class="d-flex align-items-center">
                <!-- Weather Section with Icon and Temperature -->
                <div id="weather_section" class="d-flex align-items-center">
                    <img id="weather_icon" src="" alt="Weather" class="weather-icon">
                    <div id="weather_info">
                        <span id="weather_temp" class="weather-temp">--°C</span>
                        <span id="weather_location" class="weather-location">Location</span>
                    </div>
                </div>

                <!-- Real-time Clock Section -->
                <a class="nav-link" href="javascript:void(0);" id="real_time_clock">
                    <i class="fa fa-clock mr-1"></i>
                    <span id="clock_time">00:00:00</span>
                </a>
            </div>
        </li>



        @php
        $now = Carbon\Carbon::now();

        $notification = App\Models\Notification::where(function ($query) {
        $query->whereNull('user_id')->orwhere('user_id', user()->id);
        })
        ->WhereDoesntHave('notification_read_users', function ($qr) {
        $qr->where('display_user_id', user()->id);
        })
        ->where('time_of_notify', '<', $now) ->get();

            // dump($noti);

            $notification = $notification->reverse();

            $count = App\Models\Notification::where(function ($query) {
            $query->whereNull('user_id')->orwhere('user_id', user()->id);
            })
            ->whereDoesntHave('notification_read_users', function ($qr) {
            $qr->where('user_id', user()->id);
            })
            ->where('time_of_notify', '<=', $now) ->get();

                @endphp
                {{-- <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">

                        <div id="notification" class="notification @if ($count->count() !== 0) show-count data-count"
                            data-count='{{ $count->count() }}' @endif">
                        </div>

                    </a>
                    <div class="dropdown-menu notification-message" aria-labelledby="navbarDropdownMenuLink">
                        @if ($notification->count() > 0 && $count->count() !== 0)
                        <a class="dropdown-item mark-unread" href="/notification/markallread/{{ user()->id }}">
                            Mark all as read
                        </a>
                        @endif

                        @foreach ($notification as $item)
                        @php
                        $flag = $count ? $count->find($item->id) : null;
                        @endphp

                        <div class="dropdown-item @if ($flag ? $flag->id == $item->id : false) unread @endif">
                            <div class="d-flex">
                                <a class="d-inline-block color flex-grow-1" href="/notification/view/{{ $item->id }}">
                                    {{ $item->title }}
                                </a>

                                <i class="fa fa-times-circle-o flex-item float-right ml-3 mt-1"
                                    onclick="location = '/notification/vanish/{{ $item->id }}/{{ user()->id }}'"></i>
                            </div>
                        </div>
                        @endforeach
                        @if ($notification->count() == 0)
                        <div class="dropdown-item">
                            <i class="fa fa-check-circle"></i>
                            You are all caught up!
                        </div>
                        @endif
                    </div>
                </li> --}}

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="navbarDropdownMenuLink"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-bell"></i>
                        @if ($count->count() !== 0)
                        <span class="badge bg-danger notification-count">{{ $count->count() }}</span>
                        @endif
                    </a>

                    @php
                    $unreadNotifications = $notification->reject(function ($item) {
                    return DB::table('notification_read_users')
                    ->where('notification_id', $item->id)
                    ->where('user_id', user()->id)
                    ->exists();
                    });
                    @endphp

                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown"
                        aria-labelledby="navbarDropdownMenuLink" style="max-height: 300px; overflow-y: auto;">
                        <li class="dropdown-header fixed-header">
                            <span>Notifications</span>
                            @if ($notification->count() > 0 && $count->count() !== 0)
                            <a class="mark-unread text-primary" href="/notification/markallread/{{ user()->id }}">Read
                                All</a>
                            @endif
                        </li>

                        @if ($unreadNotifications->count() > 0)
                        @foreach ($unreadNotifications as $item)
                        @php
                        $flag = $count ? $count->find($item->id) : null;
                        @endphp
                        <li
                            class="dropdown-item notification-item @if ($flag ? $flag->id == $item->id : false) unread @endif">
                            <div class="d-flex">
                                <a class="d-inline-block color flex-grow-1"
                                    href="/notification/view/{{ $item->id }}"
                                >
                                    {{ $item->title }}
                                </a>
                                <i class="fa fa-times-circle flex-item float-right ml-3 mt-1"
                                    onclick="location = '/notification/vanish/{{ $item->id }}/{{ user()->id }}'"></i>
                            </div>
                        </li>
                        @endforeach
                        @else
                        @if (!user()->is_super_admin() && !$notification->count() == 0)
                        <li class="dropdown-item text-center">
                            <i class="fa fa-check-circle text-success"></i> You are all caught up!
                        </li>
                        @endif
                        @endif

                        @if ($notification->count() == 0)
                        <li class="dropdown-item text-center">
                            <i class="fa fa-check-circle text-success"></i> You are all caught up!
                        </li>
                        @endif
                        @if (!user()->is_super_admin())
                        <li class="dropdown-item text-center fix">
                            <a href="/my_notification"
                                class="btn btn-primary btn-sm w-100 mt-2">See All</a>
                        </li>
                        @endif
                    </ul>
                </li>

                <style>
                    .has-arrow.main-drop .dropdown-menu {
                        padding-top: 8px;
                        padding-bottom: 8px;
                    }
                </style>
                <li class="nav-item dropdown has-arrow main-drop">
                    <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                        <span class="user-img d-none">
                            {{-- <img src="{{ URL::to('/assets/images/' . Auth::user()->avatar) }}"
                                alt="{{ Auth::user()->name }}"> --}}
                            <span class="status online"></span>
                        </span>
                        <span>{{ user()->name }}</span>
                    </a>
                    <div class="dropdown-menu">
                        @if (!user()->is_super_admin())
                        <a class="dropdown-item" href="{{ route('employees.active.profile', user()->id) }}">
                            <i class="fa fa-user"></i>
                            My Profile
                        </a>
                        @endif
                        <a class="dropdown-item" href="{{ route('password.change') }}">
                            <i class="fa fa-lock"></i>
                            Change Password
                        </a>
                        <a class="dropdown-item" href="{{ route('device-sessions.index') }}">
                            <i class="fa fa-devices"></i>
                            Device Sessions
                        </a>
                        <a class="dropdown-item" href="{{ route('logout') }}">
                            <i class="fa fa-sign-out"></i>
                            Logout
                        </a>
                        @if(config('app.customer') !== 'sas')
                        <hr class="mt-1 mb-1">
                        <a class="dropdown-item" href="/mobile-app">
                            <i class="fa fa-mobile"></i>
                            Mobile App
                        </a>
                        @endif
                    </div>
                </li>
    </ul>
    <!-- /Header Menu -->

    <!-- Mobile Menu -->
    <div class="dropdown mobile-user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-ellipsis-v"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            @if (!user()->is_super_admin())
            <a class="dropdown-item" href="{{ route('employees.active.profile', user()->id) }}">My Profile</a>
            @endif
            <a class="dropdown-item" href="{{ route('password.change') }}">Change Password</a>
            <a class="dropdown-item" href="{{ route('device-sessions.index') }}">Device Sessions</a>
            <a class="dropdown-item" href="{{ route('logout') }}">Logout</a>
        </div>
    </div>
</div>
