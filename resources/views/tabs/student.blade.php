@if (
    \Illuminate\Support\Facades\Route::has('students.index') &&
    (role_can('student_attendance') || user()->is_student()))
    <ul class="nav nav-tabs" id="studentsTabs" style="background-color: rgb(230, 234, 238); border-radius: 5px;">
        @if (role_can('student_attendance'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}"
                    href="{{ route('students.index') }}">
                    <i class="la la-users"></i>
                    <span>Students</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('student_shifts') ? 'active' : '' }}"
                    href="{{ route('student_shifts', ['students' => true]) }}">
                    <i class="la la-business-time"></i>
                    <span>Student Shifts</span>
                </a>
            </li>
        @endif

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('attendance.students.*') ? 'active' : '' }}"
                    href="{{ route('attendance.students.show') }}">
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
        </ul>
    @endif



{{-- @if ((role_can('student_attendance') || user()->is_student()) && Route::has('students'))
@if (
    \Illuminate\Support\Facades\Route::has('students.index') &&
        (role_can('student_attendance') || user()->is_student()))
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
@endif --}}
