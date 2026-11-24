@php
    $ui_setting_info = \App\Models\Setting::where('key', 'ui_setting')->first();
    if ($ui_setting_info) {
    $ui_info = json_decode($ui_setting_info->value, true);
    }

    if (!function_exists('hexToRgb')) {
    function hexToRgb($hex)
    {
    $hex = str_replace('#', '', $hex);

    if (strlen($hex) == 3) {
    $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    return "$r, $g, $b";
    }
    }

    $sidebar_color_left = $ui_info['navbar_background_color_left'] ?? '#5dade2';
    $sidebar_color_right = $ui_info['navbar_background_color_right'] ?? '';
    $gradientColors = "$sidebar_color_left, $sidebar_color_right";

    $currentRoute = request()->route()->getName();

@endphp

<style>
    :root {
        --head_left_color: {{$sidebar_color_left ?? '#5dade2'}};
        --sky-blue: #5dade2;
        --sky-blue-dark: #2e86c1;
        --sky-blue-light: rgba(93, 173, 226, 0.12);
        --sky-blue-hover: rgba(93, 173, 226, 0.08);
        --text-primary: #111;
        --text-secondary: #222;
        --text-muted: #1c1b1b;
        --sidebar-bg: #ffffff;
        --sidebar-border: #e0e0e0;
        --transition-smooth: all 0.2s ease-in-out 0s;
        --transition-bounce: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.04);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.06);
        --radius-sm: 6px;
        --radius-md: 10px;
    }

    /* Sidebar container */
    .sidebar {
        background-color: var(--sidebar-bg);
        border-right: 1px solid var(--sidebar-border);
        box-shadow: var(--shadow-sm);
    }

    .sidebar-inner {
        height: 100%;
        overflow-y: auto;
        padding-bottom: 30px;
    }

    /* Menu structure */
    .sidebar-menu {
        padding: 8px 0;
    }

    .sidebar-menu ul {
        font-size: 15px;
        list-style-type: none;
        margin: 0;
        padding: 0;
        position: relative;
    }

    .sidebar-menu > ul > li {
        position: relative;
        margin-bottom: 1px;
    }

    /* Menu items - main link */
    .sidebar-menu li a {
        color: var(--text-primary);
        display: block;
        font-size: 15px;
        font-weight: 600;
        height: auto;
        padding: 12px 20px;
        text-decoration: none;
        position: relative;
        letter-spacing: 0.01em;
    }

    .sidebar-menu > ul > li > a {
        padding: 10px 15px;
        align-items: center;
        display: flex;
        justify-content: flex-start;
        position: relative;
        transition: var(--transition-smooth);
    }

    /* Icon styling */
    .sidebar-menu ul li a i {
        display: inline-block;
        font-size: 15px;
        line-height: 24px;
        text-align: left;
        vertical-align: middle;
        width: 20px;
        transition: var(--transition-smooth);
        color: var(--text-muted);
    }

    .sidebar .sidebar-menu > ul > li > a span {
        transition: var(--transition-smooth);
        display: inline-block;
        margin-left: 15px;
        white-space: nowrap;
    }

    /* Hover effect */
    .sidebar-menu li a:hover {
        background-color: var(--sky-blue-hover);
        color: var(--sky-blue);
    }

    .sidebar-menu li a:hover i {
        color: var(--sky-blue);
    }

    /* Hover accent bar */
    .sidebar-menu > ul > li > a:hover::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 60%;
        background: linear-gradient(180deg, var(--sky-blue), var(--sky-blue-dark));
        border-radius: 0 3px 3px 0;
        transition: var(--transition-smooth);
    }

    /* Active state */
    .sidebar-menu li.active > a,
    .sidebar-menu li.active a.subdrop {
        color: var(--sky-blue-dark) !important;
        background-color: #ffffff !important;
        box-shadow: none;
    }

    .sidebar-menu li.active > a i,
    .sidebar-menu li.active a.subdrop i,
    .sidebar-menu li.active > a span,
    .sidebar-menu li.active a.subdrop span {
        color: var(--sky-blue-dark) !important;
    }

    .sidebar-menu li.active > a::before {
        display: none;
    }

    .sidebar-menu li.active > a:hover {
        background-color: #ffffff !important;
        color: var(--sky-blue) !important;
        box-shadow: none;
    }

    /* Submenu items */
    .sidebar-menu ul ul a {
        display: block;
        font-size: 14px;
        font-weight: 500;
        padding: 8px 10px 8px 44px;
        position: relative;
        color: var(--text-secondary);
        border-radius: 0;
        margin: 0;
        transition: var(--transition-smooth);
        text-align: left;
        text-decoration: none;
        line-height: 1.4;
        letter-spacing: 0.01em;
    }

    .sidebar-menu ul ul a:hover {
        color: var(--sky-blue);
        background-color: var(--sky-blue-hover);
    }

    .sidebar-menu ul ul a.active {
        color: var(--sky-blue-dark);
        font-weight: 600;
        background-color: #ffffff;
    }

    /* CRITICAL FIX: Override global display:none with !important */
    .sidebar-menu ul ul {
        display: none;
    }

    .sidebar-menu li.subdrop > ul,
    .sidebar-menu li.submenu.subdrop > ul {
        display: block !important;
    }

    /* Submenu list items */
    .sidebar-menu ul ul li {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    /* Menu arrow */
    .sidebar-menu .menu-arrow {
        transition: var(--transition-smooth);
        position: absolute;
        right: 15px;
        display: inline-block;
        font-family: 'FontAwesome';
        text-rendering: auto;
        font-size: 12px;
        -webkit-font-smoothing: antialiased;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }

    .sidebar-menu .menu-arrow:before {
        content: "\f105";
    }

    .sidebar-menu li a.subdrop .menu-arrow {
        transform: translateY(-50%) rotate(90deg);
        color: var(--sky-blue);
    }

    .sidebar-menu li a:hover .menu-arrow {
        color: var(--sky-blue);
    }

    /* Notification dot */
    .noti-dot:before {
        content: '';
        width: 5px;
        height: 5px;
        border: 5px solid var(--sky-blue);
        border-radius: 30px;
        background-color: var(--sky-blue);
        z-index: 10;
        position: absolute;
        right: 11px;
        top: 15px;
    }

    .noti-dot:after {
        content: '';
        border: 4px solid var(--sky-blue);
        background: transparent;
        border-radius: 60px;
        height: 24px;
        width: 24px;
        animation: pulse 3s ease-out;
        animation-iteration-count: infinite;
        position: absolute;
        top: 8px;
        right: 4px;
        z-index: 1;
        opacity: 0;
    }

    @keyframes pulse {
        0% { transform: scale(0); opacity: 0.5; }
        100% { transform: scale(1.5); opacity: 0; }
    }

    /* Menu title (section header) */
    .menu-title {
        color: var(--text-primary);
        font-family: CircularStd, sans-serif;
        display: flex;
        font-size: 12px;
        opacity: 1;
        padding: 1px 10px;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 800;
        cursor: pointer;
        user-select: none;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--sidebar-border);
        transition: var(--transition-smooth);
    }

    .menu-title:hover {
        color: var(--sky-blue);
    }

    .menu-title .toggle-icon {
        font-size: 10px;
        transition: transform 0.3s;
        opacity: 0.5;
        line-height: 40px;
    }

    .menu-title.collapsed .toggle-icon {
        transform: rotate(-90deg);
    }

    .menu-title .section-label {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .menu-title .section-icon {
        font-size: 12px;
        width: 16px;
        text-align: center;
    }

    .menu-title .item-count {
        background: #f0f0f0;
        padding: 1px 7px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: 400;
        color: #999;
    }

    /* Section group collapse */
    .section-group {
        overflow: hidden;
        transition: max-height 0.3s ease;
        margin-bottom: 4px;
    }

    .section-group.collapsed {
        max-height: 0 !important;
    }

    /* Search box */
    .menu-search-container {
        padding: 16px 20px 12px;
    }

    .search-input-wrapper {
        position: relative;
    }

    .search-input-wrapper input {
        width: 100%;
        padding: 8px 35px 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        background-color: #f8f9fa;
        color: #333;
        outline: none;
        transition: all 0.3s ease;
    }

    .search-input-wrapper input:focus {
        border-color: var(--sky-blue);
        box-shadow: 0 0 5px rgba(93, 173, 226, 0.2);
    }

    .search-input-wrapper .search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        font-size: 14px;
    }

    /* Scrollbar */
    .sidebar-inner::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar-inner::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-inner::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 10px;
    }

    .sidebar-inner::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }
</style>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner">
        <!-- Menu Search -->
        <div class="menu-search-container">
            <div class="search-input-wrapper">
                <input type="text"
                       id="menu-search"
                       placeholder="Search menu...">
                <i class="fa fa-search search-icon">
                </i>
            </div>
        </div>
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>

                {{-- ================================================================
                     SECTION 1: DASHBOARD
                     ================================================================ --}}
                <li class="menu-title" data-section="dashboard">
                    <span class="section-label">
                        <i class="fas fa-home section-icon"></i> Dashboard
                    </span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </li>
                <div class="section-group" data-group="dashboard">
                    <li class="{{ set_active(['home']) }}">
                        <a href="{{ Route::has('home') ? route('home') : '#' }}" class="{{ set_active(['home']) ? 'noti-dot' : '' }}">
                            <i class="la la-dashboard"></i>
                            <span> Dashboard</span>
                        </a>
                    </li>

                    @if (!role_can('notice_admin'))
                        <li class="{{ request()->routeIs('notice.view') ? 'noti-dot' : '' }}">
                            <a href="{{ Route::has('notice.view') ? route('notice.view') : '#' }}" class="">
                                <i class="fa fa-clipboard"></i>
                                <span> Notices</span>
                            </a>
                        </li>
                    @endif
                </div>

                {{-- ================================================================
                     SECTION 2: HR MANAGEMENT
                     ================================================================ --}}
                <li class="menu-title" data-section="hr">
                    <span class="section-label">
                        <i class="fas fa-users section-icon"></i> HR Management
                    </span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </li>
                <div class="section-group" data-group="hr">

                    @if (role_can('company.edit'))
                        <li class="{{ set_active(['company.info']) }} submenu">
                            <a href="#" class="{{ set_active(['company.info']) ? 'noti-dot' : '' }}">
                                <i class="la la-building"></i>
                                <span> Company </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li>
                                    <a class="{{ set_active(['company.info']) }}" href="{{ Route::has('company.info') ? route('company.info') : '#' }}">
                                        Company info
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    @if (role_can('super_department'))
                        <li class="{{ set_active(['departments.index']) }}">
                            <a href="{{ Route::has('departments.index') ? route('departments.index') : '#' }}"
                               class="{{ set_active(['departments.index']) ? 'noti-dot' : '' }}">
                                <i class="la la-sitemap"></i>
                                <span> Department</span>
                            </a>
                        </li>
                    @endif
                    @if (role_can('designation') && \Illuminate\Support\Facades\Route::has('employees.designations.index'))
                        <li class="{{ set_active(['employees.designations.index']) }}">
                            <a href="{{ Route::has('employees.designations.index') ? route('employees.designations.index') : '#' }}"
                               class="{{ set_active(['employees.designations.index']) ? 'noti-dot' : '' }}">
                                <i class="la la-tags"></i>
                                <span> Designation</span>
                            </a>
                        </li>
                    @endif
                    @if (role_can('user') ||
                        role_can('user.self.super_department') ||
                        role_can('locations') ||
                        user()->geo_location_departments ||
                        role_can('interview_comment') ||
                        role_can('guest_management') ||
                        role_can('interview_comment_see') ||
                        is_admin_of_super_department()
                    )

                        <li
                                class="{{ request()->routeIs('employees.interview.*', 'employees.active.*', 'employees.probation.*', 'employees.terminate.*') }} submenu">
                            <a href="#"
                               class="{{ request()->routeIs('employees.interview.*', 'employees.active.*', 'employees.probation.*', 'employees.terminate.*') ? 'noti-dot' : '' }}">
                                <i class="la la-user-check"></i>
                                <span> Employees </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                @if (role_can('interview_comment') || role_can('interview_comment_see'))
                                    <li>
                                        <a class="{{ request()->routeIs('employees.interview.*') ? 'active' : '' }}"
                                           href="{{ Route::has('employees.interview.index') ? route('employees.interview.index') : '#' }}">
                                            Interview Employees
                                        </a>
                                    </li>
                                @endif
                                @if (role_can('user') || role_can('user.self.super_department') || role_can('locations') || user()->geo_location_departments || is_admin_of_super_department())
                                    <li>
                                        <a class="{{ request()->routeIs('employees.active.*') ? 'active' : '' }}"
                                           href="{{ Route::has('employees.active.list') ? route('employees.active.list') : '#' }}">
                                            Active Employees
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ request()->routeIs('employees.probation.*') ? 'active' : '' }}"
                                           href="{{ Route::has('employees.probation.list') ? route('employees.probation.list') : '#' }}">
                                            Probation Employees
                                        </a>
                                    </li>
                                    @if (role_can('user') || role_can('user.self.super_department') || is_admin_of_super_department())
                                        <li>
                                            <a class="{{ request()->routeIs('employees.terminate.*') ? 'active' : '' }}"
                                               href="{{ Route::has('employees.terminate.list') ? route('employees.terminate.list') : '#' }}">
                                                Terminated Employees
                                            </a>
                                        </li>
                                    @endif
                                @endif
                                @if (role_can('guest_management'))
                                    <li>
                                        <a class="{{ request()->routeIs('guests.*') ? 'active' : '' }}"
                                           href="{{ Route::has('guests.index') ? route('guests.index') : '#' }}">
                                            Guest Management
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif


                    @if (\Illuminate\Support\Facades\Route::has('performance.categories') && role_can('superadmin'))
                        <li class="{{ set_active([' performance.categories', 'performance.entry', 'performance.report']) }}
                                    submenu">
                            <a href="#"
                               class="{{ set_active([' performance.categories', 'performance.entry', 'performance.report']) ? 'noti-dot' : '' }}">
                                <i class="la la-bar-chart"></i>
                                <span> Emp. Performance </span> <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li>
                                    <a class="{{ set_active(['performance.categories']) }}"
                                       href="{{ Route::has('performance.categories') ? route('performance.categories') : '#' }}">
                                        Categories
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ set_active(['performance.entry']) }}"
                                       href="{{ Route::has('performance.entry') ? route('performance.entry') : '#' }}">
                                        New Entry
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ set_active(['performance.report']) }}"
                                       href="{{ Route::has('performance.report') ? route('performance.report') : '#' }}">
                                        Report
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if (has_module('outside_duty') && (user()->can_outside_attendence == 1 || role_can('superadmin')))
                        <li class="{{ set_active(['outside-duty']) }}">
                            <a href="{{ Route::has('attendence.outside-duty') ? route('attendence.outside-duty') : '#' }}"
                               class="{{ set_active(['outside-duty']) ? 'noti-dot' : '' }}">
                                <i class="fa-solid fa-person-hiking"></i>
                                <span>
                                @if (user()->is_student())
                                        Outside Punch
                                    @else
                                        Outside Duty
                                    @endif
                            </span>
                            </a>
                        </li>
                    @endif
                </div>

                {{-- ================================================================
                     SECTION 3: TIME & ATTENDANCE
                     ================================================================ --}}
                <li class="menu-title" data-section="attendance">
                    <span class="section-label">
                        <i class="fas fa-clock section-icon"></i> Time & Attendance
                    </span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </li>
                <div class="section-group" data-group="attendance">

                    @if (role_can('shift'))
                        <li class="{{ request()->routeIs('shifts.*') ? 'noti-dot' : '' }}">
                            <a href="{{ Route::has('shifts.index') ? route('shifts.index') : '#' }}" class="">
                                <i class="la la-business-time"></i>
                                <span> Shifts</span>
                            </a>
                        </li>
                    @endif
                    @if (role_can('shift_scheduling'))
                        <li class="{{ request()->routeIs('scheduling.*') ? 'noti-dot' : '' }}">
                            <a href="{{ Route::has('scheduling.shifts.index') ? route('scheduling.shifts.index') : '#' }}" class="">
                                <i class="la la-business-time"></i>
                                <span> Shifts Scheduling</span>
                            </a>
                        </li>
                    @endif
                    @if (\Illuminate\Support\Facades\Route::has('roster.index'))
                        @if(role_can('roster') || role_can('roster.view'))
                            <li class="{{ set_active(['roster']) }}">
                                <a href="{{ Route::has('roster.index') ? route('roster.index') : '#' }}"
                                   class="{{ set_active(['roster']) ? 'noti-dot' : '' }}">
                                  <i class="la la-calendar"></i>
                                  <span> Roster</span>
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ Route::has('roster.my') ? route('roster.my') : '#' }}" class="">
                                <i class="la la-calendar"></i>
                                <span>{{ role_can('roster') ? 'Emp.' : 'My' }} Roster</span>
                            </a>
                        </li>
                    @endif
                    @if (\Illuminate\Support\Facades\Route::has('attendance.overtime.schedule') && (role_can('overtime_schedule') || user()->admin_of_super_departments))
                        <li class="{{ set_active(['attendance.overtime.schedule']) }} submenu">
                            <a href="#" class="{{ set_active(['attendance.overtime.schedule']) ? 'noti-dot' : '' }}"
                               style="position: relative">
                                <i class="la la-business-time"></i>
                                <span> Overtime Schedule </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li>
                                    <a class="{{ set_active(['attendance.overtime.schedule']) }}"
                                       href="{{ Route::has('attendance.overtime.schedule') ? route('attendance.overtime.schedule') : '#' }}">
                                        General Overtime
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ set_active(['attendance.overtime.schedule_date']) }}"
                                       href="{{ Route::has('attendance.overtime.schedule_date') ? route('attendance.overtime.schedule_date') : '#' }}">
                                        Special Overtime
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif


                    @if ((role_can('locations') || role_can('locations.location') || user()->have_geo_location || user()->locations_admin->count() || user()->can_create_location || user()->geo_location_departments) && config('services.have_attendance_location'))
                        <li
                                class="submenu">
                            <a href="#">
                                <i class="la la-map"></i>
                                <span>Geo Location</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                @if (user()->locations_admin->count() || role_can('locations') || user()->can_create_location || role_can('locations.location'))
                                    <li>
                                        <a
                                                href="{{ Route::has('locations.index') ? route('locations.index') : '#' }}">
                                            Manage Locations
                                        </a>
                                    </li>
                                @endif
                                @if(user()->have_geo_location)
                                    <li>
                                        <a
                                                href="{{ Route::has('locations.checkin') ? route('locations.checkin') : '#' }}">
                                            Check In
                                        </a>
                                    </li>
                                @endif
                                <li>
                                    <a
                                            href="{{ Route::has('locations.checkin.report') ? route('locations.checkin.report') : '#' }}">
                                        Check In Report
                                    </a>
                                </li>
                                @if (role_can('locations') || user()->geo_location_departments)
                                    <li>
                                        <a class="{{ request()->routeIs('employees.weekly-schedules.*') ? 'active' : '' }}"
                                           href="{{ Route::has('employees.weekly-schedules.index') ? route('employees.weekly-schedules.index') : '#' }}">
                                            Weekly Schedules
                                        </a>
                                    </li>
                                @else
                                    <li>
                                        <a class="{{ request()->routeIs('employees.weekly-schedules.show') ? 'active' : '' }}"
                                           href="{{ Route::has('employees.weekly-schedules.show') ? route('employees.weekly-schedules.show') : '#' }}">
                                            My Weekly Schedule
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif


                    @if (
                        user()->admin_of_super_departments ||
                        role_can('attendance') ||
                        role_can('attendance.self.super_department') ||
                        (
                            role_can('attendance-requests') ||
                            role_can('attendance-requests.apply') ||
                            \App\Models\ApprovalProcess::canApprove('attendance_request', user())
                        ) ||
                        user()->work_from_home_request
                    )
                        <li
                                class="{{ set_active(['attendance.entry', 'attendance.note', 'attendance-requests', 'attendance-requests.create', 'attendance.editall', 'attendance.gracetime']) }} submenu">
                            <a href="#"
                               class="{{ set_active(['attendance.entry', 'attendance.note', 'attendance-requests', 'attendance-requests.create', 'attendance.editall', 'attendance.gracetime']) ? 'noti-dot' : '' }}">
                                <i class="la la-list"></i>
                                <span> Attendance </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                @if (role_can('attendance') || role_can('attendance.self.super_department') || user()->admin_of_super_departments)
                                    @if (role_can('attendance.gracetime'))
                                        <li>
                                            <a class="{{ set_active(['attendance.gracetime']) }}"
                                               href="{{ Route::has('attendance.gracetime') ? route('attendance.gracetime') : '#' }}">
                                                W.Hour Grace-time
                                            </a>
                                        </li>
                                    @endif
                                    <li>
                                        <a class="{{ set_active(['reports.attendance']) }}"
                                           href="{{ Route::has('reports.attendance') ? route('reports.attendance') : '#' }}">
                                            Attendance Report
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ set_active(['attendance.entry']) }}"
                                           href="{{ Route::has('attendance.entry') ? route('attendance.entry') : '#' }}">
                                            Attendance Entry
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ set_active(['attendance.note']) }}"
                                           href="{{ Route::has('attendance.note') ? route('attendance.note') : '#' }}">
                                            Attendance Note
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ set_active(['attendance.editall']) }}"
                                           href="{{ Route::has('attendance.editall') ? route('attendance.editall') : '#' }}">
                                            Attendance Edit
                                        </a>
                                    </li>
                                @endif
                                @if (
                                    role_can('attendance-requests') ||
                                    role_can('attendance-requests.apply') ||
                                    \App\Models\ApprovalProcess::canApprove('attendance_request', user())
                                )
                                    <li>
                                        <a href="{{ Route::has('attendance-requests') ? route('attendance-requests') : '#' }}">
                                            Att. In/Out Request
                                        </a>
                                    </li>
                                @endif
                                @if (user()->work_from_home_request)
                                    <li>
                                        <a href="{{ Route::has('attendance-requests-work-home') ? route('attendance-requests-work-home') : '#' }}">
                                            Work from home req.
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                </div>

                {{-- ================================================================
                     SECTION 4: LEAVE
                     ================================================================ --}}
                <li class="menu-title" data-section="leave">
                    <span class="section-label">
                        <i class="fas fa-calendar-minus section-icon"></i> Leave
                    </span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </li>
                <div class="section-group" data-group="leave">
                    @if (
                    \Illuminate\Support\Facades\Route::has('leaves.all') &&
                    (role_can('leave') ||
                    \App\Models\ApprovalProcess::canApprove('leave', user()) ||
                    role_can('leave.apply') ||
                    role_can('leave_type') ||
                    role_can('leave.balance-assign') ||
                    role_can('leave.balance-assign.self.super_department')))
                        <li
                                class="{{ set_active([' leaves.history', 'leaves.apply', 'leaves.all', 'leaves.types', 'leaves.balance']) }} submenu">
                            <a href="#" class="{{ set_active([' leaves.history', 'leaves.apply', 'leaves.all', 'leaves.types', 'leaves.balance'])
                                    ? 'noti-dot'
                                    : '' }}">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                <span> Leave </span> <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                @if (role_can('leave') ||
                                \App\Models\ApprovalProcess::canApproveEmployees('leave', user()) ||
                                role_can('leave.apply'))
                                    <li>
                                        <a class="{{ set_active(['leaves.apply']) }}" href="{{ Route::has('leaves.apply') ? route('leaves.apply') : '#' }}">
                                            Apply leave
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ set_active(['leaves.all']) }}" href="{{ Route::has('leaves.all') ? route('leaves.all') : '#' }}">
                                            Pending List
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ set_active(['leaves.all.approve']) }}"
                                           href="{{ Route::has('leaves.all.approve') ? route('leaves.all.approve') : '#' }}">
                                            Approved List
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ set_active(['leaves.all.rejected']) }}"
                                           href="{{ Route::has('leaves.all.rejected') ? route('leaves.all.rejected') : '#' }}">
                                            Rejected List
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ set_active(['leaves.history']) }}"
                                           href="{{ Route::has('leaves.history') ? route('leaves.history') : '#' }}">
                                            Leave History
                                        </a>
                                    </li>
                                @endif
                                @if (role_can('leave') || role_can('leave.balance-assign') ||
                                role_can('leave.balance-assign.self.super_department'))
                                    <li>
                                        <a class="{{ set_active(['leaves.balance']) }}"
                                           href="{{ Route::has('leaves.balance') ? route('leaves.balance') : '#' }}">
                                            Leave Balance
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ set_active(['leaves.earn_leave_history']) }}"
                                           href="{{ Route::has('leaves.earn_leave_history') ? route('leaves.earn_leave_history') : '#' }}">
                                            Earn Leave History
                                        </a>
                                    </li>
                                @endif
                                @if (role_can('leave_type'))
                                    <li>
                                        <a class="{{ set_active(['leaves.types']) }}" href="{{ Route::has('leaves.types') ? route('leaves.types') : '#' }}">
                                            Leave Types
                                        </a>
                                    </li>
                                @endif
                                @if (role_can('leave') ||
                                role_can('leave.self.show') ||
                                role_can('leave.show') ||
                                role_can('leave.show.self.super_department'))
                                    <li>
                                        <a class="{{ set_active(['leaves.emp_leave_report']) }}"
                                           href="{{ Route::has('leaves.emp_leave_report') ? route('leaves.emp_leave_report') : '#' }}">
                                            Emp. Leave Report
                                        </a>
                                    </li>
                                @endif

                            </ul>
                        </li>
                    @endif

                    @if (
                     \Illuminate\Support\Facades\Route::has('station.leave.pending') &&
                     (role_can('station_leave') ||
                      role_can('apply_station_leave') ||
                     \App\Models\ApprovalProcess::canApprove('station_leave', user())))
                        <li
                                class="{{ set_active(['station.leave.pending', 'station.leave.apply', 'station.leave.history']) }} submenu">
                            <a href="#" class="{{ set_active(['station.leave.pending', 'station.leave.apply'. 'station.leave.history'])
                                    ? 'noti-dot'
                                    : '' }}">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                <span> Station Leave </span> <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                @if (role_can('station_leave') ||
                                \App\Models\ApprovalProcess::canApproveEmployees('station_leave', user()) ||
                                role_can('apply_station_leave'))
                                    <li>
                                        <a class="{{ set_active(['station.leave.apply']) }}" href="{{ Route::has('station.leave.apply') ? route('station.leave.apply') : '#' }}">
                                            Apply Station leave
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ set_active(['station.leave.pending']) }}" href="{{ Route::has('station.leave.pending') ? route('station.leave.pending') : '#' }}">
                                            Pending List
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ set_active(['station.leave.all.approve']) }}" href="{{ Route::has('station.leave.all.approve') ? route('station.leave.all.approve') : '#' }}">
                                            Approved List
                                        </a>
                                    </li>

                                    <li>
                                        <a class="{{ set_active(['station.leave.all.rejected']) }}"
                                           href="{{ Route::has('station.leave.all.rejected') ? route('station.leave.all.rejected') : '#' }}">
                                            Rejected List
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ set_active(['station.leave.history']) }}"
                                           href="{{ Route::has('station.leave.history') ? route('station.leave.history') : '#' }}">
                                            Leave History
                                        </a>
                                    </li>

                                @endif

                            </ul>
                        </li>
                    @endif
                </div>

                {{-- ================================================================
                     SECTION 5: PAYROLL & FINANCE
                     ================================================================ --}}
                <li class="menu-title" data-section="payroll">
                    <span class="section-label">
                        <i class="fas fa-money-bill-wave section-icon"></i> Payroll & Finance
                    </span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </li>
                <div class="section-group" data-group="payroll">

                @if (role_can('admin-accounts') && has_module('accounting'))
                    <li
                            class="{{ set_active(['chart-of-accounts.index', 'chart-of-accounts.create', 'chart-of-accounts.edit', 'transactions.index', 'transactions.create', 'transactions.edit', 'transfers.index', 'transfers.create', 'transfers.edit', 'bills.index', 'bills.create', 'bills.edit', 'vendors.index', 'vendors.create', 'vendors.edit', 'budgets.index', 'budgets.create', 'budgets.edit', 'profit.loss', 'balance.sheet', 'cash.flow', 'accounts.payable', 'accounts.receivable']) }} submenu">
                        <a href="#"
                           class="{{ set_active(['chart-of-accounts.index', 'chart-of-accounts.create', 'chart-of-accounts.edit', 'bills.index', 'bills.create', 'bills.edit', 'transactions.index', 'transactions.create', 'transactions.edit', 'transfers.index', 'transfers.create', 'vendors.index', 'vendors.create', 'vendors.edit', 'budgets.index', 'budgets.create', 'budgets.edit', 'profit.loss', 'balance.sheet', 'cash.flow', 'accounts.payable', 'accounts.receivable']) ? 'noti-dot' : '' }}">
                            <i class="fa-solid fa-calculator"></i>
                            <span> Accounts </span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li>
                                <a class="{{ set_active(['chart-of-accounts.index', 'chart-of-accounts.create', 'chart-of-accounts.edit']) }}"
                                   href="{{ Route::has('chart-of-accounts.index') ? route('chart-of-accounts.index') : '#' }}">
                                    Chart of Accounts
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['transactions.index', 'transactions.create', 'transactions.edit']) }}"
                                   href="{{ Route::has('transactions.index') ? route('transactions.index') : '#' }}">
                                    Transactions
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['transfers.index', 'transfers.create', 'transfers.edit']) }}"
                                   href="{{ Route::has('transfers.index') ? route('transfers.index') : '#' }}">
                                    Transfer Account
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['bills.index', 'bills.create', 'bills.edit']) }}"
                                   href="{{ Route::has('bills.index') ? route('bills.index') : '#' }}">
                                    Bill Management
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['vendors.index', 'vendors.create', 'vendors.edit']) }}"
                                   href="{{ Route::has('vendors.index') ? route('vendors.index') : '#' }}">
                                    Vendors
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['budgets.index', 'budgets.create', 'budgets.edit']) }}"
                                   href="{{ Route::has('budgets.index') ? route('budgets.index') : '#' }}">
                                    Budget Management
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['profit.loss']) }}" href="{{ Route::has('profit.loss') ? route('profit.loss') : '#' }}">
                                    Profit and Loss
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['balance.sheet']) }}" href="{{ Route::has('balance.sheet') ? route('balance.sheet') : '#' }}">
                                    Balance Sheet
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['cash.flow']) }}" href="{{ Route::has('cash.flow') ? route('cash.flow') : '#' }}">
                                    Cash Flow
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['accounts.payable']) }}"
                                   href="{{ Route::has('accounts.payable') ? route('accounts.payable') : '#' }}">
                                    Accounts Payable
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['accounts.receivable']) }}"
                                   href="{{ Route::has('accounts.receivable') ? route('accounts.receivable') : '#' }}">
                                    Accounts Receivable
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif


                @php
                    $user = user();

                    $hasPayrollAccess =
                        role_can('payroll_card')
                        || role_can('payroll_card.show')
                        || role_can('payroll_card.self.show')
                        || role_can('payroll_card.self.show.super_department')
                        || role_can('payroll_card.create')
                        || role_can('payroll_card.create.self.super_department')
                        || role_can('payslip.generate')
                        || role_can('payslip.generate.self.super_department')
                        || role_can('payslip.show')
                        || role_can('payslip.self.show')
                        || role_can('payslip.self.show.super_department')
                        || role_can('deduction')
                        || role_can('earn_head')
                        || role_can('earn_head.show')
                        || role_can('earn_head.self.show')
                        || role_can('bonuses')
                        || role_can('grade_system')
                        || role_can('payslip.pf')
                        || role_can('provident_fund')
                        || role_can('payslip.payment')
                        || $user->admin_of_super_departments;

                    $hasPayslipMenu =
                        role_can('payslip.generate')
                        || role_can('payslip.generate.self.super_department')
                        || role_can('payslip.show')
                        || role_can('payslip.self.show')
                        || role_can('payslip.self.show.super_department')
                        || role_can('payroll_approval_history')
                        || role_can('payroll_payment_report')
                        || role_can('payroll_summary_report')
                        || $user->admin_of_super_departments;
                @endphp


                @if ($hasPayrollAccess)
                    <li class="submenu">

                        <a href="#" class="{{ request()->routeIs('earn-head.*') ? 'noti-dot' : '' }}">
                            <i class="la la-money"></i>
                            <span> Payroll </span>
                            <span class="menu-arrow"></span>
                        </a>

                        <ul>
                            {{-- ================= SALARY CARDS ================= --}}
                            @if (role_can('payroll_card') || role_can('payroll_card.show') || role_can('payroll_card.self.show') || role_can('payroll_card.create') || $user->admin_of_super_departments)
                                <li>
                                    <a class="{{ request()->routeIs('payroll-cards.*') ? 'active' : '' }}"
                                       href="{{ Route::has('payroll-cards.index') ? route('payroll-cards.index') : '#' }}">
                                        Salary Cards
                                    </a>
                                </li>
                            @endif


                            {{-- ================= PAYSLIP ================= --}}
                            @if ($hasPayslipMenu)

                                @if (role_can('payslip.generate'))
                                    <li>
                                        <a class="{{ request()->routeIs('payslip.create') ? 'active' : '' }}"
                                           href="{{ Route::has('payslip.create') ? route('payslip.create') : '#' }}">
                                            Generate
                                        </a>
                                    </li>
                                @endif

                                @if (role_can('payslip.show'))
                                    <li><a class="{{ $currentRoute === 'payslip.draft' ? 'active' : '' }}" href="{{ Route::has('payslip.draft') ? route('payslip.draft') : '#' }}">Draft</a></li>
                                    <li><a class="{{ $currentRoute === 'payslip.ready_to_send' ? 'active' : '' }}" href="{{ Route::has('payslip.ready_to_send') ? route('payslip.ready_to_send') : '#' }}">Ready to Send</a></li>
                                    <li><a class="{{ $currentRoute === 'payslip.approved' ? 'active' : '' }}" href="{{ Route::has('payslip.approved') ? route('payslip.approved') : '#' }}">Approved</a></li>
                                    <li><a class="{{ $currentRoute === 'payslip.done' ? 'active' : '' }}" href="{{ Route::has('payslip.done') ? route('payslip.done') : '#' }}">Done</a></li>
                                @endif

                                @if (role_can('payroll_approval_history'))
                                    <li>
                                        <a class="{{ $currentRoute === 'payslip.history' ? 'active' : '' }}"
                                           href="{{ Route::has('payslip.history') ? route('payslip.history') : '#' }}">
                                            History
                                        </a>
                                    </li>
                                @endif

                                @if (role_can('payroll_payment_report'))
                                    <li>
                                        <a class="{{ $currentRoute === 'payslip.paymentReport' ? 'active' : '' }}"
                                           href="{{ Route::has('payslip.paymentReport') ? route('payslip.paymentReport') : '#' }}">
                                            Payment Report
                                        </a>
                                    </li>
                                @endif

                                @if (role_can('payroll_summary_report'))
                                    <li>
                                        <a class="{{ $currentRoute === 'payslip.summary' ? 'active' : '' }}"
                                           href="{{ Route::has('payslip.summary') ? route('payslip.summary') : '#' }}">
                                            Summary Report
                                        </a>
                                    </li>
                                @endif

                            @endif


                            {{-- ================= PAYROLL PAYMENT ================= --}}
                            @if (has_module('payslip-payment') && (role_can('payslip.payment') || $user->admin_of_super_departments))
                                <li>
                                    <a class="{{ request()->routeIs('payslip-payment.index') ? 'active' : '' }}"
                                       href="{{ Route::has('payslip-payment.index') ? route('payslip-payment.index') : '#' }}">
                                        Payments
                                    </a>
                                </li>
                            @endif


                            {{-- ================= TAX ================= --}}
                            @if (role_can('payslip.tax') || $user->admin_of_super_departments)
                                <li>
                                    <a class="{{ request()->routeIs('payslip.tax') ? 'active' : '' }}"
                                       href="{{ Route::has('payslip.tax') ? route('payslip.tax') : '#' }}">
                                        Tax Report
                                    </a>
                                </li>
                            @endif


                            {{-- ================= EARN HEAD ================= --}}
                            @if (role_can('earn_head'))
                                <li>
                                    <a class="{{ request()->routeIs('earn-head.*') ? 'active' : '' }}"
                                       href="{{ Route::has('earn-head.index') ? route('earn-head.index') : '#' }}">
                                        Earn Head
                                    </a>
                                </li>
                            @endif


                            {{-- ================= DEDUCTIONS ================= --}}
                            @if (role_can('deduction'))
                                <li>
                                    <a href="{{ Route::has('deductions.index') ? route('deductions.index') : '#' }}">Deductions</a>
                                </li>

                                @if (config('app.customer') == 'asp')
                                    <li><a href="{{ route('deductions.bulk', 'Foods') }}">Bulk Foods Entry</a></li>
                                    <li><a href="{{ route('deductions.bulk', 'House Rent') }}">Bulk House Rent Entry</a></li>
                                @endif
                            @endif


                            {{-- ================= BONUSES ================= --}}
                            @if (role_can('bonuses'))
                                <li>
                                    <a class="{{ request()->routeIs('bonuses.category.*') ? 'active' : '' }}"
                                       href="{{ Route::has('bonuses.category.index') ? route('bonuses.category.index') : '#' }}">
                                        Bonus Categories
                                    </a>
                                </li>
                            @endif

                            @if (role_can('bonuses') || $user->admin_of_super_departments)
                                <li>
                                    <a class="{{ request()->routeIs('bonuses.index') || request()->routeIs('bonuses.create') || request()->routeIs('bonuses.generate') ? 'active' : '' }}"
                                       href="{{ Route::has('bonuses.index') ? route('bonuses.index') : '#' }}">
                                        Bonuses
                                    </a>
                                </li>
                            @endif


                            {{-- ================= GRADE ================= --}}
                            @if (role_can('grade_system'))
                                <li>
                                    <a class="{{ request()->routeIs('grades.*') ? 'active' : '' }}"
                                       href="{{ Route::has('grades.index') ? route('grades.index') : '#' }}">
                                        Salary Grade
                                    </a>
                                </li>
                            @endif


                            {{-- ================= PROVIDENT FUND ================= --}}
                            @if (role_can('provident_fund') || $user->admin_of_super_departments)
                                <li>
                                    <a class="{{ request()->routeIs('payslip.emp_provident_fund') ? 'active' : '' }}"
                                       href="{{ Route::has('payslip.emp_provident_fund') ? route('payslip.emp_provident_fund') : '#' }}">
                                        Emp. Provident Fund Bal.
                                    </a>
                                </li>
                            @endif

                            @if (role_can('payslip.pf') || $user->admin_of_super_departments)
                                <li>
                                    <a class="{{ request()->routeIs('payslip.provident_fund') ? 'active' : '' }}"
                                       href="{{ Route::has('payslip.provident_fund') ? route('payslip.provident_fund') : '#' }}">
                                        Provident Fund Bal.
                                    </a>
                                </li>
                            @endif

                            @if (role_can('provident_fund_payment') || $user->admin_of_super_departments)
                                <li>
                                    <a class="{{ request()->routeIs('pf-payment.index') ? 'active' : '' }}"
                                       href="{{ Route::has('pf-payment.index') ? route('pf-payment.index') : '#' }}">
                                        PF Payment History
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (role_can('emp-bills') || user()->admin_of_super_departments)
                    <li class="{{ set_active(['emp-bills.*', 'emp-bill-category.*']) }} submenu">
                        <a href="#" class="{{ set_active(['emp-bills.*', 'emp-bill-category.*']) ? 'noti-dot' : '' }}">
                            <i class="la la-money"></i>
                            <span> Emp. Bills </span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li>
                                <a class="{{ set_active(['emp-bills.*']) }}" href="{{ Route::has('emp-bills.index') ? route('emp-bills.index') : '#' }}">
                                    Bill List
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['emp-bill-category.*']) }}"
                                   href="{{ Route::has('emp-bill-category.index') ? route('emp-bill-category.index') : '#' }}">
                                    Category
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
                @if (
                    \Illuminate\Support\Facades\Route::has('loans') &&
                    (
                        App\Models\ApprovalProcess::canApprove('loan', user()) ||
                        role_can('loans') || role_can('loans.self')
                        || user()->admin_of_super_departments
                    )
                )
                    <li class="{{ set_active([' loans', 'loans.self']) }} submenu">
                        <a href="#" class="{{ set_active([' loans', 'loans.self']) ? 'noti-dot' : '' }}">
                            <i class="la la-money"></i>
                            <span> Emp. Loans </span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li>
                                <a class="{{ set_active(['loans']) }}" href="{{ Route::has('loans') ? route('loans') : '#' }}">
                                    Loan List
                                </a>
                            </li>
                            @if (role_can('loans') || user()->admin_of_super_departments)
                                <li>
                                    <a class="{{ set_active(['loans.bulk']) }}" href="{{ Route::has('loans.bulk') ? route('loans.bulk') : '#' }}">
                                        Bulk Loans
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (
                    (role_can('fines') || \App\Models\ApprovalProcess::canApprove('fine', user()) || user()->admin_of_super_departments)
                    && \Illuminate\Support\Facades\Route::has('fines.index')
                )
                    <li>
                        <a class="" href="{{ Route::has('fines.index') ? route('fines.index') : '#' }}">
                            <i class="la la-money"></i>
                            <span>
                                    Fine List
                                </span>
                        </a>
                    </li>
                @endif
                @if (
                    \Illuminate\Support\Facades\Route::has('emp-expenses.index') &&
                    (role_can('emp-expenses') || role_can('emp-expenses.self') || user()->admin_of_super_departments ||
                    \App\Models\ApprovalProcess::canApprove('emp_daily_expense', user()))
                )
                    <li class="submenu">
                        <a href="#" class="{{ request()->routeIs('emp-expenses.*') ? 'noti-dot' : '' }}">
                            <i class="la la-money"></i>
                            <span> Emp. Expenses </span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li>
                                <a class="{{ request()->routeIs('emp-expenses.index') ? 'active' : '' }}"
                                   href="{{ Route::has('emp-expenses.index') ? route('emp-expenses.index') : '#' }}">
                                    Entry List
                                </a>
                            </li>

                            @if (role_can('emp-expenses'))
                                <li>
                                    <a class="{{ request()->routeIs('emp-expenses.category.*') ? 'active' : '' }}"
                                       href="{{ Route::has('emp-expenses.category.index') ? route('emp-expenses.category.index') : '#' }}">
                                        Category
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (
                \Illuminate\Support\Facades\Route::has('rewards') &&
                (role_can('rewards') || role_can('rewards.self.super_department') || user()->admin_of_super_departments)
                )
                    <li class="{{ set_active([' rewards', 'auto-rewards.index']) }}">
                        <a href="{{ Route::has('rewards') ? route('rewards') : '#' }}"
                           class="{{ set_active([' rewards', 'auto-rewards.index']) ? 'noti-dot' : '' }}">
                            <i class="la la-money"></i>
                            <span> Rewards </span>
                        </a>
                    </li>
                @endif

                @if (role_can('auto-duty-bonuses') && \Illuminate\Support\Facades\Route::has('auto-duty-bonuses.index'))
                    <li
                            class="{{ request()->routeIs('auto-duty-bonuses.*') || request()->routeIs('auto-duty-bonuses-bonuses') ? 'active' : '' }} submenu">
                        <a href="#" class="{{ request()->routeIs('auto-duty-bonuses.*') ? 'noti-dot' : '' }}">
                            <i class="la la-money"></i>
                            <span> Auto Duty Bonus </span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li>
                                <a class="{{ request()->routeIs('auto-duty-bonuses.*') ? 'active' : '' }}"
                                   href="{{ Route::has('auto-duty-bonuses.index') ? route('auto-duty-bonuses.index') : '#' }}">
                                    Rules
                                </a>
                            </li>
                            <li>
                                <a class="{{ request()->routeIs('auto-duty-bonuses-bonuses') ? 'active' : '' }}"
                                   href="{{ Route::has('auto-duty-bonuses-bonuses') ? route('auto-duty-bonuses-bonuses') : '#' }}">
                                    Bonuses
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
                </div>

                {{-- ================================================================
                     SECTION 6: INVENTORY & ASSETS
                     ================================================================ --}}
                <li class="menu-title" data-section="inventory">
                    <span class="section-label">
                        <i class="fas fa-boxes-stacked section-icon"></i> Inventory & Assets
                    </span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </li>
                <div class="section-group" data-group="inventory">
                @php
                    $routes = [
                    'inventory.item.index',
                    'inventory.item.create',
                    'inventory.item.edit',
                    'inventory.category.index',
                    'inventory.category.create',
                    'inventory.category.edit',
                    'inventory.unit.index',
                    'inventory.unit.create',
                    'inventory.unit.edit',
                    'inventory.supplier.index',
                    'inventory.supplier.create',
                    'inventory.supplier.edit',
                    'inventory_item_request',
                    'inventory_item_request.create',
                    'inventory_item_request.all',
                    ];
                @endphp
                @if (
                has_module('inventory') && (
                role_can('inventory')
                || role_can('inventory-item-request')
                || \App\Models\ApprovalProcess::canApprove('inventory_item_request', user())
                ) && Route::has('inventory.item.index')
                )
                    <li class="{{ set_active($routes) }} submenu">
                        <a href="#" class="{{ set_active($routes) ? 'noti-dot' : '' }}">
                            <i class='fas fa-chart-line'></i>
                            <span> Inventory </span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (role_can('inventory'))
                                <li>
                                    <a class="{{ set_active(['inventory.category.index', 'inventory.category.create', 'inventory.category.edit']) }}"
                                       href="{{ Route::has('inventory.category.index') ? route('inventory.category.index') : '#' }}">
                                        Category
                                    </a>
                                </li>

                                <li>
                                    <a class="{{ set_active(['inventory.unit.index', 'inventory.unit.create', 'inventory.unit.edit']) }}"
                                       href="{{ Route::has('inventory.unit.index') ? route('inventory.unit.index') : '#' }}">
                                        Unit
                                    </a>
                                </li>

                                <li>
                                    <a class="{{ set_active(['inventory.item.index', 'inventory.item.create', 'inventory.item.edit']) }}"
                                       href="{{ Route::has('inventory.item.index') ? route('inventory.item.index') : '#' }}">
                                        Item
                                    </a>
                                </li>

                                <li>
                                    <a class="{{ set_active(['inventory.supplier.index', 'inventory.supplier.create', 'inventory.supplier.edit']) }}"
                                       href="{{ Route::has('inventory.supplier.index') ? route('inventory.supplier.index') : '#' }}">
                                        Supplier
                                    </a>
                                </li>

                                <li>
                                    <a class="{{ set_active(['inventory.purchase.index', 'inventory.purchase.create', 'inventory.purchase.edit']) }}"
                                       href="{{ Route::has('inventory.purchase.index') ? route('inventory.purchase.index') : '#' }}">
                                        Purchase
                                    </a>
                                </li>
                                <li>
                                    <a class="" href="{{ Route::has('inventory_label_print') ? route('inventory_label_print') : '#' }}">
                                        Label Print
                                    </a>
                                </li>
                            @endif

                            @if (role_can('inventory-item-request') ||
                            \App\Models\ApprovalProcess::canApprove('inventory_item_request', user()))
                                @if (role_can('inventory-item-request'))
                                    <li>
                                        <a class="{{ set_active(['inventory_item_request.create']) }}"
                                           href="{{ Route::has('inventory_item_request.create') ? route('inventory_item_request.create') : '#' }}">
                                            New Item Requests
                                        </a>
                                    </li>
                                @endif
                                <li>
                                    <a class="{{ set_active(['inventory_item_request']) }}"
                                       href="{{ Route::has('inventory_item_request') ? route('inventory_item_request') : '#' }}">
                                        Item Request Lists
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ set_active(['inventory_item_request.all']) }}"
                                       href="{{ Route::has('inventory_item_request.all') ? route('inventory_item_request.all') : '#' }}">
                                        Item Req. History
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ set_active(['inventory_item_request.report']) }}"
                                       href="{{ Route::has('inventory_item_request.report') ? route('inventory_item_request.report') : '#' }}">
                                        Inventory Request Report
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (
                \Illuminate\Support\Facades\Route::has('assets-management.index') &&
                (
                role_can('asset-manage') || role_can('asset-request')
                || \App\Models\ApprovalProcess::canApprove('asset_request', user())
                )
                )
                    <li
                            class="{{ set_active(['assets-management.*', 'asset_category.*', 'asset_request', 'asset_request.all']) }} submenu">
                        <a href="#"
                           class="{{ set_active(['assets-management.*', 'asset_category.*', 'asset_request', 'asset_request.all']) ? 'noti-dot' : '' }}">
                            <i class='fas fa-chart-line'></i>
                            <span> Asset Manage </span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (role_can('asset-manage'))
                                <li class="{{ set_active(['assets-management.*']) }}">
                                    <a href="{{ Route::has('assets-management.index') ? route('assets-management.index') : '#' }}"
                                       class="{{ set_active(['assets-management.*']) ? 'noti-dot' : '' }}">
                                        <span> List</span>
                                    </a>
                                </li>
                                <li class="{{ set_active(['asset_category.*']) }}">
                                    <a href="{{ Route::has('asset_category.index') ? route('asset_category.index') : '#' }}"
                                       class="{{ set_active(['asset_category.*']) ? 'noti-dot' : '' }}">
                                        <span> Category</span>
                                    </a>
                                </li>
                            @endif
                            @if (role_can('asset-request') || \App\Models\ApprovalProcess::canApprove('asset_request',
                            user()))
                                <li>
                                    <a class="{{ set_active(['asset_request']) }}" href="{{ Route::has('asset_request') ? route('asset_request') : '#' }}">
                                        Requests
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ set_active(['asset_request.all']) }}"
                                       href="{{ Route::has('asset_request.all') ? route('asset_request.all') : '#' }}">
                                        Req. History
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ set_active(['asset_request.report']) }}"
                                       href="{{ Route::has('asset_request.report') ? route('asset_request.report') : '#' }}">
                                        Asset Request Report
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (config('app.customer') === 'sas')
                    @if (Route::has('letters.generate.index'))
                        <li class=" submenu">
                            <a href="#"
                               class="{{ set_active(['letters.template.index', 'letters.generate.index']) ? 'noti-dot' : '' }}">
                                <i class="fa fa-file"></i>
                                <span> Letters </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li>
                                    <a class="{{ set_active(['letters.generate.index']) }}"
                                       href="{{ Route::has('letters.generate.index') ? route('letters.generate.index') : '#' }}">
                                        Generate Letter
                                    </a>
                                </li>
                                @if (role_can('letters.templates'))
                                    <li>
                                        <a class="{{ set_active(['letters.template.index']) }}"
                                           href="{{ Route::has('letters.template.index') ? route('letters.template.index') : '#' }}">
                                            Templates
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>

                        <li class="submenu">
                            <a href="#" class="{{ set_active(['official-form.index', 'employee-appraisal.index']) ? 'noti-dot' : '' }}">
                                <i class="fa fa-file"></i>
                                <span> Official Forms </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>

                                <li>
                                    <a class="{{ set_active(['official-form-generate.index']) }}"
                                       href="{{ Route::has('official-form-generate.index') ? route('official-form-generate.index') : '#' }}">
                                        Form Generate
                                    </a>
                                </li>

                                <li>
                                    <a class="{{ set_active(['employee-appraisal.index']) }}"
                                       href="{{ route('employee-appraisal.index') }}">
                                        Employee Appraisal
                                    </a>
                                </li>

                                @if (role_can('official-forms.templates'))
                                    <li>
                                        <a class="{{ set_active(['official-form.index']) }}"
                                           href="{{ Route::has('official-form.index') ? route('official-form.index') : '#' }}">
                                            Forms
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                @endif

                @if (role_can('vehicle-management') &&
                \Illuminate\Support\Facades\Route::has('vehicle') &&
                \Illuminate\Support\Facades\Route::has('vehicle_service'))
                    <li class="{{ set_active(['vehicle', 'vehicle.create', 'vehicle.edit']) }} submenu">
                        <a href="#"
                           class="{{ set_active(['vehicle', 'vehicle.create', 'vehicle.edit']) ? 'noti-dot' : '' }}">
                            <i class='fas fa-car'></i>
                            <span>Vehicle </span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li>
                                <a class="{{ set_active(['vehicle']) }}" href="{{ Route::has('vehicle') ? route('vehicle') : '#' }}">
                                    Vehicle List
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['vehicle_service']) }}" href="{{ Route::has('vehicle_service') ? route('vehicle_service') : '#' }}">
                                    Services
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['refuelings.index']) }}"
                                   href="{{ Route::has('refuelings.index') ? route('refuelings.index') : '#' }}">
                                    Refueling
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif


                @if (role_can('bulk-sms'))
                    <li class=" submenu">
                        <a href="#"
                           class="{{ set_active(['sms.bulk', 'sms.format.list', 'sms.history', 'sms.api']) ? 'noti-dot' : '' }}">
                            <i class="fa-solid fa-comment-sms"></i>
                            <span> SMS </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li>
                                <a class="{{ set_active(['sms.api']) }}" href="{{ Route::has('sms.api') ? route('sms.api') : '#' }}">
                                    SMS Api
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['sms.format.list']) }}" href="{{ Route::has('sms.format.list') ? route('sms.format.list') : '#' }}">
                                    SMS Format
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['sms.history']) }}" href="{{ Route::has('sms.history') ? route('sms.history') : '#' }}">
                                    History
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if (role_can('notifications') || user()->admin_of_super_departments)
                    <li class="{{ set_active(['notification.index']) }}">
                        <a href="{{ Route::has('notification.index') ? route('notification.index') : '#' }}"
                           class="{{ set_active(['notification.index']) ? 'noti-dot' : '' }}">
                            <i class="fas fa-bell fa-lg"></i>
                            <span> Notifications</span>
                        </a>
                    </li>
                @endif
                </div>

                {{-- ================================================================
                     SECTION 7: COMMUNICATION
                     ================================================================ --}}
                @if (
                    role_can('reports') ||
                    role_can('reports.self') ||
                    role_can('reports.self.super_department') ||
                    role_can('live-location-tracking') ||
                    user()->admin_of_super_departments
                )
                <li class="menu-title" data-section="reports">
                    <span class="section-label">
                        <i class="fas fa-chart-bar section-icon"></i> Reports
                    </span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </li>
                <div class="section-group" data-group="reports">
                    <li class="{{ set_active([
                            'reports.in-out',
                            'reports.attendance',
                            'reports.attendance.employee',
                            'reports.late',
                            'reports.absent',
                            'reports.absent.long',
                            'reports.machine-log',
                            'reports.total_duty_report',
                            'reports.live-location',
                        ]) }} submenu">
                        <a href="#" class="{{ set_active([
                                'reports.in-out',
                                'reports.attendance',
                                'reports.attendance.employee',
                                'reports.late',
                                'reports.absent',
                                'reports.absent.long',
                                'reports.machine-log',
                                'reports.total_duty_report',
                                'reports.live-location',
                            ])
                                ? 'noti-dot'
                                : '' }}"><i class="la la-pie-chart"></i>
                            <span> Reports </span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li>
                                <a class="{{ set_active(['reports.total_duty_report']) }}"
                                   href="{{ Route::has('reports.total_duty_report') ? route('reports.total_duty_report') : '#' }}">
                                    Total Duty Report
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['reports.attendance']) }}"
                                   href="{{ Route::has('reports.attendance') ? route('reports.attendance') : '#' }}">
                                    Attendance Report
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['reports.attendance.employee']) }}"
                                   href="{{ Route::has('reports.attendance.employee') ? route('reports.attendance.employee') : '#' }}">
                                    Emp. Attendance Report
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['reports.late']) }}" href="{{ Route::has('reports.late') ? route('reports.late') : '#' }}">
                                    Late Report
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['reports.missed-attendance']) }}"
                                   href="{{ Route::has('reports.missed-attendance') ? route('reports.missed-attendance') : '#' }}">
                                    Missed Check-Out Report
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['reports.absent']) }}" href="{{ Route::has('reports.absent') ? route('reports.absent') : '#' }}">
                                    Absent Report
                                </a>
                            </li>
                            @if(config('services.long_absent_days'))
                                <li>
                                    <a class="{{ set_active(['reports.absent.long']) }}"
                                       href="{{ Route::has('reports.absent.long') ? route('reports.absent.long') : '#' }}">
                                        Long Absent Report
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a class="{{ set_active(['reports.in-out']) }}" href="{{ Route::has('reports.in-out') ? route('reports.in-out') : '#' }}">
                                    In-Out Summery
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['reports.machine-log']) }}"
                                   href="{{ Route::has('reports.machine-log') ? route('reports.machine-log') : '#' }}">
                                    Machine Log
                                </a>
                            </li>
                            @if (Route::has('reports.live-location') && (role_can('live-location-tracking') || user()->admin_of_super_departments))
                                <li>
                                    <a class="{{ set_active(['reports.live-location']) }}"
                                       href="{{ Route::has('reports.live-location') ? route('reports.live-location') : '#' }}">
                                        Live Location Report
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                </div>
                @endif

                {{-- ================================================================
                     SECTION 8: SYSTEM & SETTINGS
                     ================================================================ --}}
                <li class="menu-title" data-section="system">
                    <span class="section-label">
                        <i class="fas fa-cogs section-icon"></i> System & Settings
                    </span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </li>
                <div class="section-group" data-group="system">

                @if (role_can('notice_admin'))
                    {{-- <li class="{{ set_active(['notice.index']) }}">
                        <a href="{{ route('notification.index') }}"
                            class="{{ set_active(['notice.index']) ? 'noti-dot' : '' }}">
                            <i class="fas fa-bell fa-lg"></i>
                            <span> Notice</span>
                        </a>
                    </li> --}}

                    <li class="{{ request()->routeIs('notice.*') ? 'noti-dot' : '' }}">
                        <a href="{{ Route::has('notice.index') ? route('notice.index') : '#' }}" class="">
                            <i class="fa fa-clipboard"></i>
                            <span> Notice</span>
                        </a>
                    </li>
                @endif
                @if (
                true
                )
                    <li class=" submenu">
                        <a href="#" class="">
                            <i class="la la-user-shield"></i>
                            <span> Policies </span> <span class="menu-arrow"></span>
                        </a>

                        <ul>
                            @if(
                            (role_can('fines') || \App\Models\ApprovalProcess::canApprove('fine', user()))
                            && \Illuminate\Support\Facades\Route::has('fines.index')
                            )
                                <li>
                                    <a class="{{ request()->routeIs('fines.auto-late-fine.*') ? 'active' : '' }}"
                                       href="{{ Route::has('fines.auto-late-fine.index') ? route('fines.auto-late-fine.index') : '#' }}">
                                        Auto Late Fines
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ request()->routeIs('fines.auto-absent-fine.*') ? 'active' : '' }}"
                                       href="{{ Route::has('fines.auto-absent-fine.index') ? route('fines.auto-absent-fine.index') : '#' }}">
                                        Auto Absent Fines
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ request()->routeIs('half-day-fines.*') ? 'active' : '' }}"
                                       href="{{ Route::has('half-day-fines.index') ? route('half-day-fines.index') : '#' }}">
                                        Half Day Fines
                                    </a>
                                </li>
                            @endif
                            @if(role_can('leave'))
                                <li>
                                    <a href="{{ Route::has('leaves.earn_leave.policy') ? route('leaves.earn_leave.policy') : '#' }}">
                                        Earn Leave
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (role_can('approval-processes'))
                    <li class="{{ request()->routeIs('approval-processes*') ? 'noti-dot' : '' }}">
                        <a href="{{ Route::has('approval-processes.index') ? route('approval-processes.index') : '#' }}" class="">
                            <i class="fa fa-sitemap"></i>
                            <span>Approval Process</span>
                        </a>
                    </li>
                @endif


                <li
                        class="{{ set_active(['settings.attendance', 'holidays', 'zkteco', 'roles', 'update', 'settings.interests', 'settings.attendance.location']) }} submenu">
                    <a href="#"
                       class="{{ set_active(['settings.attendance', 'holidays', 'zkteco', 'roles', 'update', 'settings.interests', 'settings.attendance.location']) ? 'noti-dot' : '' }}">
                        <i class="la la-gear"></i>
                        <span> Settings </span> <span class="menu-arrow"></span>
                    </a>
                    <ul>
                        @if (role_can('api_key') || role_can('holidays') || role_can('role') || role_can('settings'))

                            @if (role_can('settings'))
                                <li>
                                    <a class="{{ set_active(['settings.interests']) }}"
                                       href="{{ Route::has('settings.interests') ? route('settings.interests') : '#' }}">
                                        Interests
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ set_active(['settings.attendance']) }}"
                                       href="{{ Route::has('settings.attendance') ? route('settings.attendance') : '#' }}">
                                        Attendance
                                    </a>
                                </li>
                                @if (\Illuminate\Support\Facades\Route::has('settings.attendance.location'))
                                    <li>
                                        <a class="{{ set_active(['settings.attendance.location']) }}"
                                           href="{{ Route::has('settings.attendance.location') ? route('settings.attendance.location') : '#' }}">
                                            Attendance Location
                                        </a>
                                    </li>
                                @endif
                            @endif
                        @endif
                        {{-- @if (role_can('holidays')) --}}
                        <li>
                            <a class="{{ set_active(['holidays.range']) }}" href="{{ Route::has('holidays.range') ? route('holidays.range') : '#' }}">
                                Holidays
                            </a>
                        </li>
                        {{-- @endif --}}
                        @if (role_can('api_key') || role_can('holidays') || role_can('role') || role_can('settings'))
                            @if (role_can('role'))
                                <li>
                                    <a class="{{ request()->routeIs('role.*') }}" href="{{ Route::has('role.index') ? route('role.index') : '#' }}">
                                        User Roles
                                    </a>
                                </li>
                            @endif
                            @if (role_can('api_key'))
                                <li>
                                    <a class="{{ set_active(['zkteco']) }}" href="{{ Route::has('zkteco') ? route('zkteco') : '#' }}">
                                        ZkTeco Devices
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a class="{{ set_active(['ui.setting.info']) }}" href="{{ Route::has('ui.setting.info') ? route('ui.setting.info') : '#' }}">
                                    UI
                                </a>
                            </li>
                            <li>
                                <a class="{{ set_active(['cache.clean']) }}" href="{{ Route::has('cache.clean') ? route('cache.clean') : '#' }}">
                                    Cash Clean
                                </a>
                            </li>

                        @endif
                    </ul>
                </li>

                @if (role_can('activity-log'))
                    <li class="{{ request()->routeIs('activity-log*') ? 'noti-dot' : '' }}">
                        <a href="{{ Route::has('activity-log.index') ? route('activity-log.index') : '#' }}" class="">
                            <i class="fa fa-sitemap"></i>
                            <span>Activity Log</span>
                        </a>
                    </li>
                @endif

                {{-- <li class="{{ request()->routeIs('chat.*') ? 'noti-dot' : '' }}">
                    <a href="{{ route('chat.index') }}" class="">
                        <i class="fa fa-message" aria-hidden="true"></i>
                        <span> Live Chat</span>
                    </a>
                </li> --}}

                @if (role_can('backup'))
                    <li class="{{ set_active(['backup.index']) }}">
                        <a href="{{ Route::has('backup.index') ? route('backup.index') : '#' }}"
                           class="{{ set_active(['backup.index']) ? 'noti-dot' : '' }}">
                            <i class="fa fa-database" aria-hidden="true"></i>
                            <span> BackUp</span>
                        </a>
                    </li>
                @endif
                @if (role_can('zkteco-cloud') && \Illuminate\Support\Facades\Route::has('zkteco-cloud'))
                    <li class="{{ set_active(['zkteco-cloud']) }} submenu">
                        <a href="#" class="{{ set_active(['zkteco-cloud']) ? 'noti-dot' : '' }}">
                            <i class="fa-solid fa-cloud"></i>
                            <span> ZkTeco Cloud </span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li>
                                <a class="{{ set_active(['zkteco-cloud']) }}" href="{{ Route::has('zkteco-cloud') ? route('zkteco-cloud') : '#' }}">
                                    Device List
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
                <li class="{{ set_active(['faq']) }}">
                    <a href="{{ Route::has('faq') ? route('faq') : '#' }}" class="{{ set_active(['faq']) ? 'noti-dot' : '' }}">
                        <i class="fa-solid fa-question"></i>
                        <span> FAQ </span>
                    </a>
                </li>
                </div>

            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->
<style>
    .sidebar-menu {
        padding-bottom: 70vh;
    }

    .get-more {
        justify-content: center;
        padding: 10px 0;
        margin-top: 20px;
    }

    .get-more span {
        background: gold;
        color: #000;
        padding: 0px 20px;
        border-radius: 10px;
    }

    ul.noti-dot:before {
        display: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', e => {
        // ============================================
        // COLLAPSE ALL SECTIONS BY DEFAULT ON LOGIN
        // ============================================
        document.querySelectorAll('.section-group').forEach(group => {
            group.classList.add('collapsed');
        });
        document.querySelectorAll('.menu-title').forEach(title => {
            title.classList.add('collapsed');
            const icon = title.querySelector('.toggle-icon');
            if (icon) icon.style.transform = 'rotate(-90deg)';
        });

        // ============================================
        // SECTION COLLAPSE/EXPAND TOGGLE
        // ============================================
        document.querySelectorAll('.menu-title').forEach(title => {
            title.addEventListener('click', function() {
                const section = this.getAttribute('data-section');
                const group = document.querySelector(`.section-group[data-group="${section}"]`);
                const icon = this.querySelector('.toggle-icon');

                // Collapse all other sections
                document.querySelectorAll('.menu-title').forEach(otherTitle => {
                    const otherSection = otherTitle.getAttribute('data-section');
                    if (otherSection !== section) {
                        const otherGroup = document.querySelector(`.section-group[data-group="${otherSection}"]`);
                        const otherIcon = otherTitle.querySelector('.toggle-icon');
                        if (otherGroup) {
                            otherGroup.classList.add('collapsed');
                        }
                        otherTitle.classList.add('collapsed');
                        if (otherIcon) otherIcon.style.transform = 'rotate(-90deg)';
                    }
                });

                // Collapse all submenus in other sections
                document.querySelectorAll('.section-group').forEach(groupEl => {
                    if (groupEl.getAttribute('data-group') !== section) {
                        groupEl.querySelectorAll('li.submenu').forEach(submenu => {
                            submenu.classList.remove('subdrop');
                        });
                    }
                });

                // Toggle current section
                if (group) {
                    if (group.classList.contains('collapsed')) {
                        group.classList.remove('collapsed');
                        if (icon) icon.style.transform = 'rotate(0deg)';
                        this.classList.remove('collapsed');

                        // Re-expand submenus containing active items in the now-expanded section
                        group.querySelectorAll('li.submenu ul li a.active').forEach(activeA => {
                            const parentSubmenu = activeA.closest('li.submenu');
                            if (parentSubmenu) {
                                parentSubmenu.classList.add('subdrop');
                                // Also expand any parent submenus (for nested submenus)
                                let parentLi = parentSubmenu.parentElement ? parentSubmenu.parentElement.closest('li.submenu') : null;
                                while (parentLi) {
                                    parentLi.classList.add('subdrop');
                                    parentLi = parentLi.parentElement ? parentLi.parentElement.closest('li.submenu') : null;
                                }
                            }
                        });
                    } else {
                        group.classList.add('collapsed');
                        if (icon) icon.style.transform = 'rotate(-90deg)';
                        this.classList.add('collapsed');
                    }
                }
            });
        });

        function checkMatch(startPattern) {
            const fullUrl = location.href
            let regexPattern = "^" + startPattern.replace(/\*/g, ".*");
            let regex = new RegExp(regexPattern);
            return regex.test(fullUrl);
        }

        // ============================================
        // URL-BASED ACTIVE STATE DETECTION
        // ============================================
        $('#sidebar-menu a').each(function () {
            if (this.href && !this.href.includes('#') && checkMatch(this.href + '*')) {
                this.classList.add('active')
                this.classList.add('noti-dot')
                this.parentElement.classList.add('active')
                this.parentElement.parentElement.classList.add('active')
                this.parentElement.parentElement.parentElement.classList.add('active')
                // Also add subdrop to parent submenu li to keep it expanded
                const parentSubmenu = this.parentElement.closest('li.submenu');
                if (parentSubmenu) {
                    parentSubmenu.classList.add('subdrop');
                }
            }
        })

        // ============================================
        // AUTO-EXPAND ON PAGE LOAD
        // ============================================
        // Find the active element (could be <a class="active"> or <li class="active">)
        const activeLink = document.querySelector('.sidebar-menu a.active');
        const activeMenuItem = document.querySelector('.sidebar-menu li.active, .sidebar-menu ul ul a.active');
        const activeItem = activeMenuItem || activeLink;

        if (activeItem) {
            // Expand the section-group containing the active item
            const parentGroup = activeItem.closest('.section-group');
            if (parentGroup) {
                const sectionName = parentGroup.getAttribute('data-group');
                const title = document.querySelector(`.menu-title[data-section="${sectionName}"]`);
                const icon = title ? title.querySelector('.toggle-icon') : null;

                parentGroup.classList.remove('collapsed');
                if (title) title.classList.remove('collapsed');
                if (icon) icon.style.transform = 'rotate(0deg)';
            }

            // Expand all parent submenus (li.submenu) containing or around the active item
            let parentLi = activeItem.closest('li.submenu');
            while (parentLi) {
                parentLi.classList.add('subdrop');
                parentLi = parentLi.parentElement ? parentLi.parentElement.closest('li.submenu') : null;
            }
        }

        // Menu Search Functionality
        const searchInput = document.getElementById('menu-search');
        const sidebarMenu = document.getElementById('sidebar-menu');
        const allMenuItems = sidebarMenu.querySelectorAll('li');

        const originalDisplayStates = new Map();
        allMenuItems.forEach(item => {
            const subUl = item.querySelector('ul');
            if (subUl) {
                originalDisplayStates.set(item, subUl.style.display || '');
            }
        });

        function filterMenu(searchTerm) {
            const term = searchTerm.toLowerCase().trim();
            let hasVisibleItems = false;

            allMenuItems.forEach(item => {
                const textContent = item.textContent.toLowerCase();
                const isMainItem = !item.closest('ul').closest('li');
                const hasSubMenu = item.classList.contains('submenu');
                const isSubMenuItem = item.closest('ul').closest('li');

                let shouldShow = false;

                if (term === '') {
                    // Show all items when search is empty, restore original states
                    shouldShow = true;
                    if (hasSubMenu) {
                        const subUl = item.querySelector('ul');
                        if (subUl && originalDisplayStates.has(item)) {
                            subUl.style.display = originalDisplayStates.get(item);
                        }
                    }
                } else {
                    // Check if current item matches search term
                    const menuText = item.querySelector('a > span, a')?.textContent?.toLowerCase() || '';
                    shouldShow = menuText.includes(term);

                    // If this is a submenu item that matches, also show its parent
                    if (shouldShow && isSubMenuItem) {
                        const parentLi = item.closest('ul').closest('li');
                        if (parentLi) {
                            parentLi.style.display = 'block';
                            const parentSubUl = parentLi.querySelector('ul');
                            if (parentSubUl) {
                                parentSubUl.style.display = 'block';
                            }
                        }
                    }

                    // If this is a parent menu, check if any children match
                    if (hasSubMenu && !shouldShow) {
                        const childItems = item.querySelectorAll('ul li');
                        for (let child of childItems) {
                            const childText = child.querySelector('a > span, a')?.textContent?.toLowerCase() || '';
                            if (childText.includes(term)) {
                                shouldShow = true;
                                // Show the submenu
                                const subUl = item.querySelector('ul');
                                if (subUl) {
                                    subUl.style.display = 'block';
                                }
                                break;
                            }
                        }
                    }
                }

                // Apply visibility
                if (shouldShow) {
                    item.style.display = 'block';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            });

            return hasVisibleItems;
        }

        // Search input event listener
        searchInput.addEventListener('input', function () {
            filterMenu(this.value);
        });

        // Enhanced input styling on focus
        searchInput.addEventListener('focus', function () {
            this.style.borderColor = 'var(--sky-blue)';
            this.style.boxShadow = '0 0 5px rgba(93, 173, 226, 0.2)';
        });

        searchInput.addEventListener('blur', function () {
            this.style.borderColor = '#ddd';
            this.style.boxShadow = 'none';
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function (e) {
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
            }
            // Escape to clear search
            if (e.key === 'Escape' && document.activeElement === searchInput) {
                searchInput.value = '';
                filterMenu('');
                searchInput.blur();
            }
        });
        $('.submenu').each(function () {
            if ($(this).find('li').length === 0) {
                this.remove()
            }
        })
    })
</script>
