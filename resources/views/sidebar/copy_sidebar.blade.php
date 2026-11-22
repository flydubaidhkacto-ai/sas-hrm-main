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

$sidebar_color_left = $ui_info['navbar_background_color_left'] ?? '#0ed96a';
$sidebar_color_right = $ui_info['navbar_background_color_right'] ?? '';
$gradientColors = "$sidebar_color_left, $sidebar_color_right";
@endphp



<style>
    :root {
        /* --sidebar_color: {{ $ui_info['sidebar_background_color'] ?? '' }}; */
        --head_left_color: $sidebar_color_left ?? ''
    }

    .sidebar {
        background-color: $ui_info['sidebar_background_color'] ?? '';
    }




    /* .sidebar-menu li.active a{
        color: {{ $sidebar_color_left }};
    } */
    /* .menu-title {
        color: {{ $ui_info['text_color'] ?? '' }};
    }
    .sidebar-menu li a{
        color: {{ $ui_info['text_color'] ?? '' }};
    }
    .sidebar-menu li a:hover{
        background-color: {{ $ui_info['text_color'] ?? '' }};
        color: {{ $ui_info['sidebar_background_color'] ?? '' }};
    }
    .sidebar-menu li.active a {
        color: rgba({{ hexToRgb($ui_info['text_color'] ?? '') }}, 0.8);
        background-color: rgba({{ hexToRgb($ui_info['navbar_background_color'] ?? '') }}, 0.4);
    } */
</style>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner" style="overflow-y: auto; padding-bottom: 40px">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="{{ set_active(['home']) }}">
                    <a href="{{ route('home') }}" class="{{ set_active(['home']) ? 'noti-dot' : '' }}">
                        <i class="la la-dashboard"></i>
                        <span> Dashboard</span>
                    </a>
                </li>
                @if (role_can('company.edit'))
                <li class="{{ set_active(['company.info']) }} submenu">
                    <a href="#" class="{{ set_active(['company.info']) ? 'noti-dot' : '' }}">
                        <i class="la la-building"></i>
                        <span> Company </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        <li>
                            <a class="{{ set_active(['company.info']) }}" href="{{ route('company.info') }}">
                                Company info
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                @if (role_can('super_department'))
                <li class="{{ set_active(['departments.index']) }}">
                    <a href="{{ route('departments.index') }}"
                        class="{{ set_active(['departments.index']) ? 'noti-dot' : '' }}">
                        <i class="la la-sitemap"></i>
                        <span> Department</span>
                    </a>
                </li>
                @endif
                @if (role_can('designation'))
                <li class="{{ set_active(['employees.designations.index']) }}">
                    <a href="{{ route('employees.designations.index') }}"
                        class="{{ set_active(['employees.designations.index']) ? 'noti-dot' : '' }}">
                        <i class="la la-tags"></i>
                        <span> Designation</span>
                    </a>
                </li>
                @endif

                @if (role_can('user') ||
                role_can('user.self.super_department') ||
                role_can('promotion-apply') ||
                role_can('interview_comment') ||
                role_can('interview_comment_see'))
                {{-- <li class="menu-title"> <span>Employees</span> </li> --}}
                @if (role_can('user') || role_can('user.self.super_department') || role_can('promotion-apply'))
                <li
                    class="{{ request()->routeIs('employees.active.*', 'employees.interview.*', 'employees.probation.*', 'employees.terminate.*') ? 'noti-dot' : '' }}">
                    <a href="{{ route('employees.active.list') }}" class="">
                        <i class="la la-user-check"></i>
                        <span>Employees</span>
                    </a>
                </li>
                @endif


                {{-- @if (role_can('interview_comment') || role_can('interview_comment_see'))
                <li class="{{ request()->routeIs('employees.interview.*') ? 'noti-dot' : '' }}">
                    <a href="{{ route('employees.interview.index') }}" class="">
                        <i class="la la-user-check"></i>
                        <span> Interview Employees</span>
                    </a>
                </li>
                @endif

                @if (role_can('user') || role_can('user.self.super_department') || role_can('promotion-apply'))
                <li class="{{ request()->routeIs('employees.active.*') ? 'noti-dot' : '' }}">
                    <a href="{{ route('employees.active.list') }}" class="">
                        <i class="la la-user-check"></i>
                        <span> Active Employees</span>
                    </a>
                </li>

                <li class="{{ request()->routeIs('employees.probation.*') ? 'noti-dot' : '' }}">
                    <a href="{{ route('employees.probation.list') }}" class="">
                        <i class="la la-user-check"></i>
                        <span> Probation Employees</span>
                    </a>
                </li>

                @if (role_can('user') || role_can('user.self.super_department'))
                <li class="{{ request()->routeIs('employees.terminate.*') ? 'noti-dot' : '' }}">
                    <a href="{{ route('employees.terminate.list') }}" class="">
                        <i class="la la-user-slash"></i>
                        <span> Terminated Employees</span>
                    </a>
                </li>
                @endif
                @endif --}}


                @endif
                {{-- @if ((role_can('student_attendance') || user()->is_student()) && Route::has('students')) --}}
                @if (
                \Illuminate\Support\Facades\Route::has('students.index') &&
                (role_can('student_attendance') || user()->is_student()))
                <li class="menu-title"> <span>For Student</span> </li>
                @if (role_can('student_attendance'))
                <li class="{{ request()->routeIs('students.*') ? 'noti-dot' : '' }}">
                    <a href="{{ route('students.index') }}" class="">
                        <i class="la la-users"></i>
                        <span>Students</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('student_shifts') ? 'noti-dot' : '' }}">
                    <a href="{{ route('student_shifts', ['students' => true]) }}" class="">
                        <i class="la la-business-time"></i>
                        <span>Student Shifts</span>
                    </a>
                </li>
                @endif
                <li class="{{ request()->routeIs('attendance.students.*') ? 'noti-dot' : '' }}">
                    <a href="{{ route('attendance.students.show') }}"
                        class="{{ request()->routeIs('attendance.students.*') ? 'noti-dot' : '' }}">
                        <i class="la la-list"></i>
                        <span>
                            @if (user()->is_student() && !role_can('student_attendance'))
                            My Attendance
                            @else
                            Student Attendance
                            @endif
                        </span>
                    </a>
                </li>
                @endif

                <li class="menu-title"> <span>HR</span> </li>
                @if (role_can('shift'))
                <li class="{{ request()->routeIs('shifts.*') ? 'noti-dot' : '' }}">
                    <a href="{{ route('shifts.index') }}" class="">
                        <i class="la la-business-time"></i>
                        <span> Shifts</span>
                    </a>
                </li>
                @endif
                @if (role_can('shift_scheduling'))
                <li class="{{ request()->routeIs('scheduling.*') ? 'noti-dot' : '' }}">
                    <a href="{{ route('scheduling.shifts.index') }}" class="">
                        <i class="la la-business-time"></i>
                        <span> Shifts Scheduling</span>
                    </a>
                </li>
                @endif
                @if (role_can('roster') && \Illuminate\Support\Facades\Route::has('roster.index'))
                <li class="{{ set_active(['roster']) }}">
                    <a href="{{ route('roster.index') }}" class="{{ set_active(['roster']) ? 'noti-dot' : '' }}">
                        <i class="la la-calendar"></i>
                        <span> Roster</span>
                    </a>
                </li>
                @endif

                @if (role_can('overtime_schedule'))
                <li class="{{ set_active(['attendance.overtime.schedule']) }} submenu">
                    <a href="#" class="{{ set_active(['attendance.overtime.schedule']) ? 'noti-dot' : '' }}"
                        style="position: relative">
                        <i class="la la-business-time"></i>
                        <i class="fa fa-plus bg-dark" style="
                                position: absolute; bottom: 10px; font-size: 11px;
                                width: 12px; height: 12px; border-radius: 100%; line-height: 15px;
                                "></i>
                        <span> Overtime Schedule </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        <li>
                            <a class="{{ set_active(['attendance.overtime.schedule']) }}"
                                href="{{ route('attendance.overtime.schedule') }}">
                                General Overtime
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['attendance.overtime.schedule_date']) }}"
                                href="{{ route('attendance.overtime.schedule_date') }}">
                                Special Overtime
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                @if (role_can('attendance') ||
                role_can('attendance.self.super_department') ||
                (role_can('attendance-requests') ||
                role_can('attendance-requests.apply') ||
                role_can('attendance-requests.approve') ||
                role_can('attendance-requests.approve.super_department') ||
                role_can('attendance-requests.approve.final') ||
                role_can('attendance-requests.approve.final.super_department')))
                <li
                    class="{{ set_active(['attendance.entry', 'attendance.note', 'attendance-requests', 'attendance-requests.create', 'attendance.editall', 'attendance.gracetime']) }} submenu">
                    <a href="#"
                        class="{{ set_active(['attendance.entry', 'attendance.note', 'attendance-requests', 'attendance-requests.create', 'attendance.editall', 'attendance.gracetime']) ? 'noti-dot' : '' }}">
                        <i class="la la-list"></i>
                        <span> Attendance </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        @if (role_can('attendance') || role_can('attendance.self.super_department'))
                        @if (role_can('attendance.gracetime'))
                        <li>
                            <a class="{{ set_active(['attendance.gracetime']) }}"
                                href="{{ route('attendance.gracetime') }}">
                                W.Hour Grace-time
                            </a>
                        </li>
                        @endif
                        <li>
                            <a class="{{ set_active(['reports.attendance']) }}"
                                href="{{ route('reports.attendance') }}">
                                Attendance Report
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['attendance.entry']) }}" href="{{ route('attendance.entry') }}">
                                Attendance Entry
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['attendance.note']) }}" href="{{ route('attendance.note') }}">
                                Attendance Note
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['attendance.editall']) }}"
                                href="{{ route('attendance.editall', ['editable' => 1]) }}">
                                Attendance Edit
                            </a>
                        </li>
                        @endif
                        @if (role_can('attendance-requests') ||
                        role_can('attendance-requests.apply') ||
                        role_can('attendance-requests.approve') ||
                        role_can('attendance-requests.approve.super_department') ||
                        role_can('attendance-requests.approve.final') ||
                        role_can('attendance-requests.approve.final.super_department'))
                        <li>
                            <a href="{{ route('attendance-requests') }}">
                                {{ config('app.customer') === 'vector' ? "Work from Home" : "Att. In/Out Request" }}
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if ((role_can('promotion-apply') || role_can('promotion-approve') ||
                role_can('final-promotion-approve')) && false)
                <li class=" submenu">
                    <a href="#"
                        class="{{ set_active(['promotion.requested', 'promotion.pending', 'promotion.final.list', 'promotion.select']) ? 'noti-dot' : '' }}">
                        <i class="fa fa-line-chart" aria-hidden="true"></i>
                        <span> Promotion </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        @if (role_can('promotion-apply'))
                        <li>
                            <a class="{{ set_active(['promotion.select']) }}" href="{{ route('promotion.select') }}">
                                Select Employee
                            </a>
                        </li>

                        <li>
                            <a class="{{ set_active(['promotion.requested']) }}"
                                href="{{ route('promotion.requested') }}">
                                Requested Promotion
                            </a>
                        </li>
                        @endif

                        @if (role_can('promotion-approve'))
                        <li>
                            <a class="{{ set_active(['promotion.pending']) }}" href="{{ route('promotion.pending') }}">
                                Pending Promotion
                            </a>
                        </li>
                        @endif

                        @if (role_can('final-promotion-approve'))
                        <li>
                            <a class="{{ set_active(['promotion.final.list']) }}"
                                href="{{ route('promotion.final.list') }}">
                                Final Promotion
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
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        <li>
                            <a class="{{ set_active(['performance.categories']) }}"
                                href="{{ route('performance.categories') }}">
                                Categories
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['performance.entry']) }}" href="{{ route('performance.entry') }}">
                                New Entry
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['performance.report']) }}"
                                href="{{ route('performance.report') }}">
                                Report
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                @if (has_module('outside_duty') && (user()->can_outside_attendence == 1 || user()->is_super_admin()))
                <li class="{{ set_active(['outside-duty']) }}">
                    <a href="{{ route('attendence.outside-duty') }}"
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
                @if (
                \Illuminate\Support\Facades\Route::has('leaves.all') &&
                (role_can('leave') ||
                role_can('leave.self.show') ||
                role_can('leave.self.apply') ||
                role_can('leave.show') ||
                role_can('leave.show.self.super_department') ||
                role_can('leave.apply') ||
                role_can('leave.apply.self.super_department') ||
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
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        @if (role_can('leave') ||
                        role_can('leave.apply') ||
                        role_can('leave.apply.self.super_department') ||
                        role_can('leave.self.apply'))
                        <li>
                            <a class="{{ set_active(['leaves.apply']) }}" href="{{ route('leaves.apply') }}">
                                Apply leave
                            </a>
                        </li>
                        @endif
                        @if (role_can('leave') ||
                        role_can('leave.self.show') ||
                        role_can('leave.show') ||
                        role_can('leave.show.self.super_department'))
                        <li>
                            <a class="{{ set_active(['leaves.all']) }}" href="{{ route('leaves.all') }}">
                                Pending List
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['leaves.all.approve']) }}"
                                href="{{ route('leaves.all.approve') }}">
                                Approved List
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['leaves.all.rejected']) }}"
                                href="{{ route('leaves.all.rejected') }}">
                                Rejected List
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['leaves.history']) }}" href="{{ route('leaves.history') }}">
                                Leave History
                            </a>
                        </li>
                        @endif
                        @if (role_can('leave') || role_can('leave.balance-assign') ||
                        role_can('leave.balance-assign.self.super_department'))
                        <li>
                            <a class="{{ set_active(['leaves.balance']) }}" href="{{ route('leaves.balance') }}">
                                Leave Balance
                            </a>
                        </li>
                        @endif
                        @if (role_can('leave_type'))
                        <li>
                            <a class="{{ set_active(['leaves.types']) }}" href="{{ route('leaves.types') }}">
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
                                href="{{ route('leaves.emp_leave_report') }}">
                                Emp. Leave Report
                            </a>
                        </li>
                        @endif

                    </ul>
                </li>
                @endif

                @if (role_can('admin-accounts') && has_module('accounting'))
                <li class="menu-title"> <span>Accounts Management</span> </li>
                <li
                    class="{{ set_active(['chart-of-accounts.index','chart-of-accounts.create', 'chart-of-accounts.edit', 'transactions.index', 'transactions.create', 'transactions.edit', 'transfers.index', 'transfers.create', 'transfers.edit', 'bills.index', 'bills.create', 'bills.edit', 'vendors.index', 'vendors.create', 'vendors.edit', 'budgets.index', 'budgets.create', 'budgets.edit', 'profit.loss', 'balance.sheet', 'cash.flow', 'accounts.payable', 'accounts.receivable']) }} submenu">
                    <a href="#"
                        class="{{ set_active(['chart-of-accounts.index', 'chart-of-accounts.create', 'chart-of-accounts.edit', 'bills.index', 'bills.create', 'bills.edit', 'transactions.index', 'transactions.create', 'transactions.edit', 'transfers.index', 'transfers.create', 'vendors.index', 'vendors.create', 'vendors.edit', 'budgets.index', 'budgets.create', 'budgets.edit', 'profit.loss', 'balance.sheet', 'cash.flow', 'accounts.payable', 'accounts.receivable']) ? 'noti-dot' : '' }}">
                        <i class="fa-solid fa-calculator"></i>
                        <span> Accounts </span> <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        <li>
                            <a class="{{ set_active(['chart-of-accounts.index', 'chart-of-accounts.create', 'chart-of-accounts.edit']) }}"
                                href="{{ route('chart-of-accounts.index') }}">
                                Chart of Accounts
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['transactions.index', 'transactions.create', 'transactions.edit']) }}"
                                href="{{ route('transactions.index') }}">
                                Transactions
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['transfers.index', 'transfers.create', 'transfers.edit']) }}"
                                href="{{ route('transfers.index') }}">
                                Transfer Account
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['bills.index', 'bills.create', 'bills.edit']) }}"
                                href="{{ route('bills.index') }}">
                                Bill Management
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['vendors.index', 'vendors.create', 'vendors.edit']) }}"
                                href="{{ route('vendors.index') }}">
                                Vendors
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['budgets.index', 'budgets.create', 'budgets.edit']) }}"
                                href="{{ route('budgets.index') }}">
                                Budget Management
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['profit.loss']) }}" href="{{ route('profit.loss') }}">
                                Profit and Loss
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['balance.sheet']) }}" href="{{ route('balance.sheet') }}">
                                Balance Sheet
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['cash.flow']) }}" href="{{ route('cash.flow') }}">
                                Cash Flow
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['accounts.payable']) }}" href="{{route('accounts.payable')}}">
                                Accounts Payable
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['accounts.receivable']) }}"
                                href="{{ route('accounts.receivable') }}">
                                Accounts Receivable
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

                @if ((role_can('work-admin') || role_can('work-employee')) && has_module('work'))
                <li class="menu-title"> <span>Work</span> </li>
                <li
                    class="{{ set_active(['clients.index', 'clients.create', 'clients.edit', 'projects.index', 'projects.create', 'projects.edit', 'projects.show', 'project-categories.index', 'project-categories.create', 'project-categories.edit', 'tasks.index', 'tasks.create', 'tasks.edit', 'time-trackers.index', 'task-categories.index', 'task-categories.create', 'task-categories.edit', 'tickets.index', 'tickets.create', 'tickets.edit', 'tickets.show', 'task-status.index', 'task-status.create', 'task-status.edit']) }} submenu">
                    <a href="#"
                        class="{{ set_active(['clients.index', 'clients.create', 'clients.edit', 'projects.index', 'projects.create', 'projects.edit', 'projects.show', 'project-categories.index', 'project-categories.create', 'project-categories.edit', 'tasks.index', 'tasks.create', 'tasks.edit', 'time-trackers.index', 'task-categories.index', 'task-categories.create', 'task-categories.edit', 'tickets.index', 'tickets.create', 'tickets.edit', 'tickets.show', 'task-status.index', 'task-status.create', 'task-status.edit']) ? 'noti-dot' : '' }}">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span> Work </span> <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        @if (role_can('work-admin'))
                        <li>
                            <a class="{{ set_active(['clients.index', 'clients.create', 'clients.edit']) }}"
                                href="{{ route('clients.index') }}">
                                Clients
                            </a>
                        </li>

                        <li>
                            <a class="{{ set_active(['project-categories.index', 'project-categories.create', 'project-categories.edit']) }}"
                                href="{{ route('project-categories.index') }}">
                                Project Category
                            </a>
                        </li>

                        <li>
                            <a class="{{ set_active(['projects.index', 'projects.create', 'projects.edit', 'projects.show']) }}"
                                href="{{ route('projects.index') }}">
                                Project
                            </a>
                        </li>

                        @endif
                        @if (role_can('work-employee') || role_can('work-admin'))
                        <li>
                            <a class="{{ set_active(['task-categories.index', 'task-categories.create', 'task-categories.edit']) }}"
                                href="{{ route('task-categories.index') }}">
                                Task Category
                            </a>
                        </li>
                        @endif
                        @if (role_can('work-employee') || role_can('work-admin'))
                        <li>
                            <a class="{{ set_active(['task-status.index', 'task-status.create', 'task-status.edit']) }}"
                                href="{{ route('task-status.index') }}">
                                Task Status
                            </a>
                        </li>
                        @endif
                        @if (role_can('work-employee') || role_can('work-admin'))
                        <li>
                            <a class="{{ set_active(['tasks.index', 'tasks.create', 'tasks.edit']) }}"
                                href="{{ route('tasks.index') }}">
                                Task
                            </a>
                        </li>
                        @endif
                        @if (role_can('work-employee') || role_can('work-admin'))
                        <li>
                            <a class="{{ set_active(['time-trackers.timesheet']) }}"
                                href="{{ route('time-trackers.timesheet') }}">
                                Timesheet
                            </a>
                        </li>
                        @endif
                        @if (role_can('work-employee') || role_can('work-admin'))
                        <li>
                            <a class="{{ set_active(['tickets.index', 'tickets.create', 'tickets.edit', 'tickets.show']) }}"
                                href="{{ route('tickets.index') }}">
                                Ticket
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif


                @if (has_module('payroll') &&
                (role_can('payroll_card') ||
                role_can('payroll_card.show') ||
                role_can('payroll_card.self.show') ||
                role_can('payroll_card.self.show.super_department') ||
                role_can('payroll_card.create') ||
                role_can('payroll_card.create.self.super_department') ||
                role_can('payslip.generate') ||
                role_can('payslip.generate.self.super_department') ||
                role_can('payslip.show') ||
                role_can('payslip.self.show') ||
                role_can('payslip.self.show.super_department') ||
                role_can('deduction') ||
                role_can('earn_head') ||
                role_can('earn_head.show') ||
                role_can('earn_head.self.show') ||
                role_can('bonuses') ||
                role_can('grade_system') ||
                role_can('provident_fund')))
                <li class="menu-title"> <span>Payroll</span> </li>
                {{-- {{ set_active([ 'earn-head', 'deductions', 'payroll-cards', 'payslip', 'bonuses.category',
                'bonuses', 'bonuses.create', 'payslip.tax', 'grade_system', 'provident_fund' ]) }} --}}
                <li class="{{ request()->routeIs('earn-head.*') ||
                        request()->routeIs('deductions.*') ||
                        request()->routeIs('payslip.*') ||
                        request()->routeIs('bonuses.*') ||
                        request()->routeIs('payroll-cards') ||
                        request()->routeIs('payroll-cards.*') ||
                        request()->routeIs('grades.*')
                            ? 'active'
                            : '' }} submenu">
                    <a href="#" class="{{ request()->routeIs('earn-head.*') ? 'noti-dot' : '' }}">
                        <i class="la la-money"></i>
                        <span> Payroll </span> <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ request()->is('payroll/*') ? 'display: block;' : 'display: none;' }}">
                        @if (role_can('payroll_card') ||
                        role_can('payroll_card.show') ||
                        role_can('payroll_card.self.show') ||
                        role_can('payroll_card.self.show.super_department') ||
                        role_can('payroll_card.create') ||
                        role_can('payroll_card.create.self.super_department'))
                        <li>
                            <a class="{{ request()->routeIs('payroll-cards.*') ? 'active' : '' }}"
                                href="{{ route('payroll-cards.index') }}">
                                Salary Cards
                            </a>
                        </li>
                        @endif
                        @if (role_can('payslip.generate') ||
                        role_can('payslip.generate.self.super_department') ||
                        role_can('payslip.show') ||
                        role_can('payslip.self.show') ||
                        role_can('payslip.self.show.super_department'))
                        <li>
                            <a class="{{ request()->routeIs('payslip.draft') ? 'active' : '' }}"
                                href="{{ route('payslip.draft') }}">
                                Payslip
                            </a>
                        </li>
                        @endif
                        @if (role_can('payslip.tax'))
                        <li>
                            <a class="{{ request()->routeIs('payslip.tax') ? 'active' : '' }}"
                                href="{{ route('payslip.tax') }}">
                                Tax Report
                            </a>
                        </li>
                        @endif

                        @if (role_can('earn_head'))
                        <li>
                            <a class="{{ request()->routeIs('earn-head.*') ? 'active' : '' }}"
                                href="{{ route('earn-head.index') }}">
                                Earn Head
                            </a>
                        </li>
                        @endif
                        @if (role_can('deduction'))
                        <li>
                            <a class="{{ request()->routeIs('deductions.*') ? 'active' : '' }}"
                                href="{{ route('deductions.index') }}">
                                Deductions
                            </a>
                        </li>
                        @endif
                        @if (role_can('bonuses'))
                        <li>
                            <a class="{{ request()->routeIs('bonuses.category.*') ? 'active' : '' }}"
                                href="{{ route('bonuses.category.index') }}">
                                Bonus Categories
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('bonuses.index') || request()->routeIs('bonuses.create') || request()->routeIs('bonuses.generate') ? 'active' : '' }}"
                                href="{{ route('bonuses.index') }}">
                                Bonuses
                            </a>
                        </li>
                        @endif

                        @if (role_can('grade'))
                        <li>
                            <a class="{{ request()->routeIs('grades.*') ? 'active' : '' }}"
                                href="{{ route('grades.index') }}">
                                Salary Grade
                            </a>
                        </li>
                        @endif
                        @if (role_can('provident_fund'))
                        <li>
                            <a class="{{ request()->routeIs('payslip.provident_fund') ? 'active' : '' }}"
                                href="{{ route('payslip.provident_fund') }}">
                                Provident Fund Bal.
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @if (role_can('emp-bills'))
                <li class="{{ set_active(['emp-bills.*', 'emp-bills-category.*']) }} submenu">
                    <a href="#" class="{{ set_active(['emp-bills.*', 'emp-bills-category.*']) ? 'noti-dot' : '' }}">
                        <i class="la la-money"></i>
                        <span> Emp. Bills </span> <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        <li>
                            <a class="{{ set_active(['emp-bills.*']) }}" href="{{ route('emp-bills.index') }}">
                                Bill List
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['emp-bills-category.*']) }}"
                                href="{{ route('emp-bills-category.index') }}">
                                Category
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                @endif
                @if (\Illuminate\Support\Facades\Route::has('loans') && App\Models\Loan::can_approve('loan', user()))
                <li class="{{ set_active([' loans', 'loans.apply']) }} submenu">
                    <a href="#" class="{{ set_active([' loans', 'loans.apply']) ? 'noti-dot' : '' }}">
                        <i class="la la-money"></i>
                        <span> Emp. Loans </span> <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        <li>
                            <a class="{{ set_active(['loans']) }}" href="{{ route('loans') }}">
                                Loan List
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['loans.create']) }}" href="{{ route('loans.create') }}">
                                Apply
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                @if (role_can('fines') && \Illuminate\Support\Facades\Route::has('fines.index'))
                <li class="{{ request()->routeIs('fines.index') ? 'active' : '' }} submenu">
                    <a href="#"
                        class="{{ request()->routeIs('fines.*') || request()->routeIs('half-day-fines.*') ? 'noti-dot' : '' }}">
                        <i class="la la-money"></i>
                        <span> Emp. Fines </span> <span class="menu-arrow"></span>
                    </a>

                    <ul style="{{ request()->routeIs('fines.*') ? 'display: block' : 'display: none' }}">
                        <li>
                            <a class="{{ request()->routeIs('fines.index') ? 'active' : '' }}"
                                href="{{ route('fines.index') }}">
                                Fine List
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('fines.create') ? 'active' : '' }}"
                                href="{{ route('fines.create') }}">
                                Create
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('fines.auto-late-fine.*') ? 'active' : '' }}"
                                href="{{ route('fines.auto-late-fine.index') }}">
                                Auto Late Fines
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('half-day-fines.*') ? 'active' : '' }}"
                                href="{{ route('half-day-fines.index') }}">
                                Half Day Fines
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                @if (
                \Illuminate\Support\Facades\Route::has('emp-expenses.index') &&
                (role_can('emp-expenses') || role_can('emp-expenses.self')))
                <li class="submenu">
                    <a href="#" class="{{ request()->routeIs('emp-expenses.*') ? 'noti-dot' : '' }}">
                        <i class="la la-money"></i>
                        <span> Emp. Expenses </span> <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        <li>
                            <a class="{{ request()->routeIs('emp-expenses.index') ? 'active' : '' }}"
                                href="{{ route('emp-expenses.index') }}">
                                Entry List
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('emp-expenses.create') ? 'active' : '' }}"
                                href="{{ route('emp-expenses.create') }}">
                                New Entry
                            </a>
                        </li>

                        @if (role_can('emp-expenses'))
                        <li>
                            <a class="{{ request()->routeIs('emp-expenses.category.*') ? 'active' : '' }}"
                                href="{{ route('emp-expenses.category.index') }}">
                                Category
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif
                @if (
                \Illuminate\Support\Facades\Route::has('rewards') &&
                (role_can('rewards') || role_can('rewards.self.super_department')))
                <li class="{{ set_active([' rewards', 'auto-rewards.index']) }}">
                    <a href="{{ route('rewards') }}"
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
                    <ul style="{{ request()->routeIs('auto-duty-bonuses.*') ? 'display: block;' : 'display: none;' }}">
                        <li>
                            <a class="{{ request()->routeIs('auto-duty-bonuses.*') ? 'active' : '' }}"
                                href="{{ route('auto-duty-bonuses.index') }}">
                                Rules
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('auto-duty-bonuses-bonuses') ? 'active' : '' }}"
                                href="{{ route('auto-duty-bonuses-bonuses') }}">
                                Bonuses
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
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
                @if (has_module('inventory') && role_can('inventory') && Route::has('inventory.item.index'))
                <li class="{{ set_active($routes) }} submenu">
                    <a href="#" class="{{ set_active($routes) ? 'noti-dot' : '' }}">
                        <i class='fas fa-chart-line'></i>
                        <span> Inventory </span> <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">

                        <li>
                            <a class="{{ set_active(['inventory.category.index', 'inventory.category.create', 'inventory.category.edit']) }}"
                                href="{{ route('inventory.category.index') }}">
                                Category
                            </a>
                        </li>

                        <li>
                            <a class="{{ set_active(['inventory.unit.index', 'inventory.unit.create', 'inventory.unit.edit']) }}"
                                href="{{ route('inventory.unit.index') }}">
                                Unit
                            </a>
                        </li>

                        <li>
                            <a class="{{ set_active(['inventory.item.index', 'inventory.item.create', 'inventory.item.edit']) }}"
                                href="{{ route('inventory.item.index') }}">
                                Item
                            </a>
                        </li>

                        <li>
                            <a class="{{ set_active(['inventory.supplier.index', 'inventory.supplier.create', 'inventory.supplier.edit']) }}"
                                href="{{ route('inventory.supplier.index') }}">
                                Supplier
                            </a>
                        </li>

                        <li>
                            <a class="{{ set_active(['inventory.purchase.index', 'inventory.purchase.create', 'inventory.purchase.edit']) }}"
                                href="{{ route('inventory.purchase.index') }}">
                                Purchase
                            </a>
                        </li>

                        @if (role_can('inventory-item-request'))
                        <li>
                            <a class="{{ set_active(['inventory_item_request.create']) }}"
                                href="{{ route('inventory_item_request.create') }}">
                                New Request
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['inventory_item_request']) }}"
                                href="{{ route('inventory_item_request') }}">
                                Request Lists
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['inventory_item_request.all']) }}"
                                href="{{ route('inventory_item_request.all') }}">
                                Req. History
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if (
                \Illuminate\Support\Facades\Route::has('assets-management.index') &&
                (role_can('asset-manage') || role_can('asset-category')))
                <li
                    class="{{ set_active(['assets-management.*', 'asset_category.*', 'asset_request', 'asset_request.all']) }} submenu">
                    <a href="#"
                        class="{{ set_active(['assets-management.*', 'asset_category.*', 'asset_request', 'asset_request.all']) ? 'noti-dot' : '' }}">
                        <i class='fas fa-chart-line'></i>
                        <span> Asset Manage </span> <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        @if (role_can('asset-manage'))
                        <li class="{{ set_active(['assets-management.*']) }}">
                            <a href="{{ route('assets-management.index') }}"
                                class="{{ set_active(['assets-management.*']) ? 'noti-dot' : '' }}">
                                <span> List</span>
                            </a>
                        </li>
                        @endif
                        @if (role_can('asset-category'))
                        <li class="{{ set_active(['asset_category.*']) }}">
                            <a href="{{ route('asset_category.index') }}"
                                class="{{ set_active(['asset_category.*']) ? 'noti-dot' : '' }}">
                                <span> Category</span>
                            </a>
                        </li>
                        @endif
                        @if (role_can('asset-request'))
                        <li>
                            <a class="{{ set_active(['asset_request']) }}" href="{{ route('asset_request') }}">
                                Requests
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['asset_request.all']) }}" href="{{ route('asset_request.all') }}">
                                Req. History
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif
                {{-- @endif --}}

                @if (Route::has('letters.generate.index'))
                <li class=" submenu">
                    <a href="#"
                        class="{{ set_active(['letters.template.index', 'letters.generate.index']) ? 'noti-dot' : '' }}">
                        <i class="fa fa-file"></i>
                        <span> Letters </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        <li>
                            <a class="{{ set_active(['letters.generate.index']) }}"
                                href="{{ route('letters.generate.index') }}">
                                Generate Letter
                            </a>
                        </li>
                        @if (role_can('letters.templates'))
                        <li>
                            <a class="{{ set_active(['letters.template.index']) }}"
                                href="{{ route('letters.template.index') }}">
                                Templates
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>

                <li class="submenu">
                    <a href="#" class="{{ set_active(['official-form.index']) ? 'noti-dot' : '' }}">
                        <i class="fa fa-file"></i>
                        <span> Official Forms </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">

                        <li>
                            <a class="{{ set_active(['official-form-generate.index']) }}"
                                href="{{ route('official-form-generate.index') }}">
                                Form Generate
                            </a>
                        </li>

                        @if (role_can('official-forms.templates'))
                        <li>
                            <a class="{{ set_active(['official-form.index']) }}"
                                href="{{ route('official-form.index') }}">
                                Forms
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
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
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        <li>
                            <a class="{{ set_active(['vehicle']) }}" href="{{ route('vehicle') }}">
                                Vehicle List
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['vehicle_service']) }}" href="{{ route('vehicle_service') }}">
                                Services
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['refuelings.index']) }}" href="{{ route('refuelings.index') }}">
                                Refueling
                            </a>
                        </li>
                    </ul>
                </li>
                @endif


                @if (role_can('bulk-sms'))
                <li class="menu-title"> <span>Others</span> </li>
                <li class=" submenu">
                    <a href="#"
                        class="{{ set_active(['sms.bulk', 'sms.format.list', 'sms.history', 'sms.api']) ? 'noti-dot' : '' }}">
                        <i class="fa-solid fa-comment-sms"></i>
                        <span> SMS </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        <li>
                            <a class="{{ set_active(['sms.api']) }}" href="{{ route('sms.api') }}">
                                SMS Api
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['sms.format.list']) }}" href="{{ route('sms.format.list') }}">
                                SMS Format
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['sms.history']) }}" href="{{ route('sms.history') }}">
                                History
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

                @if (role_can('notifications'))
                <li class="{{ set_active(['notification.index']) }}">
                    <a href="{{ route('notification.index') }}"
                        class="{{ set_active(['notification.index']) ? 'noti-dot' : '' }}">
                        <i class="fas fa-bell fa-lg"></i>
                        <span> Notifications</span>
                    </a>
                </li>
                @endif
                @if (role_can('reports') || role_can('reports.self') || role_can('reports.self.super_department') ||
                role_can('live-location-tracking'))
                <li class="{{ set_active([
                            'reports.in-out',
                            'reports.attendance',
                            'reports.attendance.employee',
                            'reports.late',
                            'reports.absent',
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
                                'reports.machine-log',
                                'reports.total_duty_report',
                                'reports.live-location',
                            ])
                                ? 'noti-dot'
                                : '' }}"><i class="la la-pie-chart"></i>
                        <span> Reports </span> <span class="menu-arrow"></span></a>
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        <li>
                            <a class="{{ set_active(['reports.total_duty_report']) }}"
                                href="{{ route('reports.total_duty_report') }}">
                                Total Duty Report
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['reports.attendance']) }}"
                                href="{{ route('reports.attendance') }}">
                                Attendance Report
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['reports.attendance.employee']) }}"
                                href="{{ route('reports.attendance.employee') }}">
                                Emp. Attendance Report
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['reports.late']) }}" href="{{ route('reports.late') }}">
                                Late Report
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['reports.absent']) }}" href="{{ route('reports.absent') }}">
                                Absent Report
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['reports.in-out']) }}" href="{{ route('reports.in-out') }}">
                                In-Out Summery
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['reports.machine-log']) }}"
                                href="{{ route('reports.machine-log') }}">
                                Machine Log
                            </a>
                        </li>
                        @if (Route::has('reports.live-location') && role_can('live-location-tracking'))
                        <li>
                            <a class="{{ set_active(['reports.live-location']) }}"
                                href="{{ route('reports.live-location') }}">
                                Live Location Report
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if (role_can('notice_admin'))
                {{-- <li class="{{ set_active(['notice.index']) }}">
                    <a href="{{ route('notification.index') }}"
                        class="{{ set_active(['notification.index']) ? 'noti-dot' : '' }}">
                        <i class="fas fa-bell fa-lg"></i>
                        <span> Notice</span>
                    </a>
                </li> --}}

                <li class="{{ request()->routeIs('notice.*') ? 'noti-dot' : '' }}">
                    <a href="{{ route('notice.index') }}" class="">
                        <i class="fa fa-clipboard"></i>
                        <span> Notice</span>
                    </a>
                </li>
                @endif

                @if (role_can('approval-processes'))
                <li class="{{ request()->routeIs('approval-processes*') ? 'noti-dot' : '' }}">
                    <a href="{{ route('approval-processes.index') }}" class="">
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
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        @if (role_can('api_key') || role_can('holidays') || role_can('role') || role_can('settings'))

                        @if (role_can('settings'))
                        <li>
                            <a class="{{ set_active(['settings.interests']) }}"
                                href="{{ route('settings.interests') }}">
                                Interests
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['settings.attendance']) }}"
                                href="{{ route('settings.attendance') }}">
                                Attendance
                            </a>
                        </li>
                        @if (\Illuminate\Support\Facades\Route::has('settings.attendance.location'))
                        <li>
                            <a class="{{ set_active(['settings.attendance.location']) }}"
                                href="{{ route('settings.attendance.location') }}">
                                Attendance Location
                            </a>
                        </li>
                        @endif
                        @endif
                        @endif
                        {{-- @if (role_can('holidays')) --}}
                        <li>
                            <a class="{{ set_active(['holidays.range']) }}" href="{{ route('holidays.range') }}">
                                Holidays
                            </a>
                        </li>
                        {{-- @endif --}}
                        @if (role_can('api_key') || role_can('holidays') || role_can('role') || role_can('settings'))
                        @if (role_can('role'))
                        <li>
                            <a class="{{ request()->routeIs('role.*') }}" href="{{ route('role.index') }}">
                                User Roles
                            </a>
                        </li>
                        @endif
                        @if (role_can('api_key'))
                        <li>
                            <a class="{{ set_active(['zkteco']) }}" href="{{ route('zkteco') }}">
                                ZkTeco Devices
                            </a>
                        </li>
                        @endif
                        <li>
                            <a class="{{ set_active(['ui.setting.info']) }}" href="{{ route('ui.setting.info') }}">
                                UI
                            </a>
                        </li>
                        <li>
                            <a class="{{ set_active(['cache.clean']) }}" href="{{ route('cache.clean') }}">
                                Cash Clean
                            </a>
                        </li>

                        @endif
                    </ul>
                </li>

                @if (role_can('activity-log'))
                <li class="{{ request()->routeIs('activity-log*') ? 'noti-dot' : '' }}">
                    <a href="{{ route('activity-log.index') }}" class="">
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
                <li class="menu-title"><span>Back Up</span> </li>
                <li class="{{ set_active(['backup.index']) }}">
                    <a href="{{ route('backup.index') }}" class="{{ set_active(['backup.index']) ? 'noti-dot' : '' }}">
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
                    <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                        <li>
                            <a class="{{ set_active(['zkteco-cloud']) }}" href="{{ route('zkteco-cloud') }}">
                                Device List
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

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
</style>
