@if (role_can('user') ||
        role_can('user.self.super_department') ||
        role_can('promotion-apply') ||
        role_can('interview_comment') ||
        role_can('interview_comment_see'))

    <ul class="nav nav-tabs" id="employeeTabs" style="background-color: rgb(230, 234, 238); border-radius: 5px;">
        @if (role_can('interview_comment') || role_can('interview_comment_see'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('employees.interview.*') ? 'active' : '' }}"
                    href="{{ route('employees.interview.index') }}">
                    <i class="la la-user-check"></i>
                    <span> Interview Employees</span>
                </a>
            </li>
        @endif

        @if (role_can('user') || role_can('user.self.super_department') || role_can('promotion-apply'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('employees.active.*') ? 'active' : '' }}"
                    href="{{ route('employees.active.list') }}">
                    <i class="la la-user-check"></i>
                    <span> Active Employees</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('employees.probation.*') ? 'active' : '' }}"
                    href="{{ route('employees.probation.list') }}">
                    <i class="la la-user-check"></i>
                    <span> Probation Employees</span>
                </a>
            </li>

            @if (role_can('user') || role_can('user.self.super_department'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('employees.terminate.*') ? 'active' : '' }}"
                        href="{{ route('employees.terminate.list') }}">
                        <i class="la la-user-slash"></i>
                        <span> Terminated Employees</span>
                    </a>
                </li>
            @endif
        @endif
    </ul>
@endif
