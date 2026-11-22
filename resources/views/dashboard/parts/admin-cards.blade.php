@if (role_can('dashboard') || role_can('dashboard.self') || role_can('dashboard.self.super_department'))
    <hr>
    <div class="row">
        @if(role_can('dashboard') || (!\App\Models\Setting::use_custom_sms_api() && role_can('sms.api')))
            <div class="col-lg-8 col-md-8 col-sm-12">
                <div class="row">
                    @if (role_can('dashboard'))
                        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-4">
                            <a class="card dash-widget" style="border-top: 3px solid #1071d6;"
                               href="{{ route('employees.active.list') }}">
                                <div class="card-body"><span class="dash-widget-icon"><i class="fa fa-users"></i></span>
                                    <div class="dash-widget-info">
                                        <h3>
                                            {{ \App\Models\User::active_employees()->count() }}
                                            /
                                            {{ \App\Models\User::employees()->count() }}
                                        </h3> <span>Active Employees</span>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-4">
                            <a class="card dash-widget" style="border-top: 3px solid #10d66b;"
                               href="{{ route('reports.attendance', ['start_date' => today()->format('d-m-Y'), 'end_date' => today()->format('d-m-Y')]) }}">
                                <div class="card-body">
                                    <span class="dash-widget-icon"><i class="fa fa-fingerprint"></i></span>
                                    <div class="dash-widget-info">
                                        <h3>
                                            {{ \App\Models\User::active_employees()
                                            ->whereHas('attendance', function ($query) {
                                            $query->where('date', now()->format('Y-m-d'));
                                            })->count() }}
                                            /
                                            {{ \App\Models\User::active_employees()->count() }}
                                        </h3>
                                        <span>Today Present</span>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-4">
                            <a class="card dash-widget" style="border-top: 3px solid #fba042;"
                               href="{{ route('reports.late', ['start_date' => today()->format('d-m-Y'), 'end_date' => today()->format('d-m-Y')]) }}">
                                <div class="card-body"><span class="dash-widget-icon"><i class="fa fa-clock"></i></span>
                                    <div class="dash-widget-info">
                                        <h3>
                                            {{ $total_absent_employees->count() }}
                                        </h3>
                                        <span>Today Late</span>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-4">
                            <a class="card dash-widget" style="border-top: 3px solid #fd3622;"
                               href="{{ route('reports.absent', ['start_date' => today()->format('d-m-Y'), 'end_date' => today()->format('d-m-Y')]) }}">
                                <div class="card-body"><span class="dash-widget-icon"><i
                                                class="fa fa-user-slash"></i></span>
                                    <div class="dash-widget-info">
                                        <h3>
                                            {{ \App\Models\User::absent($today)->count() }}
                                            /
                                            {{ \App\Models\User::active_employees()->count() }}
                                        </h3>
                                        <span>Today Absent</span>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-4">
                            <a class="card dash-widget" style="border-top: 3px solid #b27ef7;"
                               href="{{ route('leaves.all.approve', ['start_date' => today()->toDateString(), 'end_date' => today()->toDateString()]) }}">
                                <div class="card-body"> <span class="dash-widget-icon"><i
                                                class="fa fa-arrow-right-from-bracket"></i></span>
                                    <div class="dash-widget-info">

                                        <h3>
                                            {{ \App\Models\LeaveApplication::final_approved()->whereDate('start_date', '<=',
                                                $today)->whereDate('end_date', '>=', $today)->count() }}
                                            {{-- /
                                            {{ \App\Models\User::active_employees()->count() }} --}}
                                        </h3>
                                        <span>Today Leave</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endif
                    @if (!\App\Models\Setting::use_custom_sms_api() && role_can('sms.api'))
                        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-4">
                            <div class="card dash-widget" style="border-top: 3px solid #d1df04;">
                                <div class="card-body">
                                    <span class="dash-widget-icon"><i class="fa fa-comment"></i></span>
                                    <div class="dash-widget-info">
                                        <h3 class="sms-status">
                                            --
                                        </h3>
                                        <span>
                                ProHR SMS
                            </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script>
                            $(document).ready(function () {
                                setTimeout(function () {
                                    fetch('{{ route('sms.api.usage') }}').then(res => res.json()).then(data => {
                                        $('.sms-status').text(data.used + ' / ' + data.limit);
                                    });
                                }, 1000);
                            });
                        </script>
                    @endif
                </div>
            </div>
        @endif
        <div class="col-lg-4 col-xl-4 col-md-4 col-sm-12">
            <div>
                <div class="card dash-widget" style="border-top: 3px solid #10d66b;">
                    <div class="card-body" style="min-height: 250px;">
                        <a href="{{ route('notice.view') }}"><label class="" for=""><i
                                        class="fa fa-clipboard"
                                        style="color: var(--head_left_color)"></i>
                            Notice Board</label></a>
                        @include('notice.view-board', [
                        'limit' => 3,
                        'except_limit' => 50,
                        'slider' => true,
                        ])
                        <div class="notice-all d-none">
                            <div class="text-center"><a href="{{ route('notice.view') }}">See All</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('notice.partials.pdf-preview-modal')
    @include('notice.partials.notice-viewers-modal')

    @if (role_can('dashboard') || role_can('dashboard.self.super_department'))
        <h3>Branches:</h3>
        <hr>

        <div class="row">
            @foreach (role_can('dashboard') ? \App\Models\SuperDepartment::orderBy('name')->get() :
            \App\Models\SuperDepartment::where('id', user()->department?->super_department_id)->get() as $item)
                @php
                    $in = \App\Models\User::currently_inside()
                    ->whereHas('department', function ($query) use ($item) {
                    $query->where('super_department_id', $item->id);
                    })
                    ->count();
                    $out = \App\Models\User::currently_outside()
                    ->whereHas('department', function ($query) use ($item) {
                    $query->where('super_department_id', $item->id);
                    })
                    ->count();
                    $total = \App\Models\User::active_employees()
                    ->whereHas('department', function ($query) use ($item) {
                    $query->where('super_department_id', $item->id);
                    })
                    ->count();
                    $present = \App\Models\User::active_employees()
                    ->whereHas('attendance', function ($query) {
                    $query->where('date', now()->format('Y-m-d'));
                    })
                    ->whereHas('department', function ($query) use ($item) {
                    $query->where('super_department_id', $item->id);
                    })
                    ->count();
                @endphp
                <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                    <a class="card dash-widget" href="{{ route('department.current-present', $item) }}"
                       style="border-top:3px solid #0fd46b">
                        <div class="card-body">
                            <div>
                                <div class="text-center">
                                    {{ $item->name }}
                                </div>
                                <h4 class="mt-2 text-center">
                                    Check-In {{ $present }}/{{ $total }}
                                </h4>
                                <h4>
                                    In: {{ $in }}
                                    <span class="float-right">
                    Out: {{ $out }}
                </span>
                                </h4>
                                @php
                                    $lastData = \App\Models\InOutRecord::whereHas(
                                    'employee.department.super_department',
                                    function ($query) use ($item) {
                                    $query->where('id', $item->id);
                                    },
                                    )
                                    ->orderBy('date_time', 'desc')
                                    ->first();
                                @endphp

                                @if ($lastData)
                                    <div class="text-right">
                                        <small>
                                            <i>
                                                Synced
                                                at {{ ($lastData->created_at ?? $lastData->date_time)->format('d M, Y h:i A') }}
                                            </i>
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
    {!! Toastr::message() !!}

    <h3>Pending Tasks:</h3>
    <hr>
    <div class="row">
        <div class="col-md-4">
            @include('dashboard.parts.pending-leaves')
        </div>

        <div class="col-md-4">
            @include('dashboard.parts.pending-station-leaves')
        </div>

        <div class="col-md-4">
            @if (config('app.customer') === 'sas')
                @include('dashboard.parts.pending-expenses')
            @else
                @include('dashboard.parts.pending-fines')
            @endif
        </div>
        <div class="col-md-4">
            @include('dashboard.parts.pending-loans')
        </div>
        <div class="col-md-4">
            @include('dashboard.parts.pending-att-requests')
        </div>
    </div>
    <hr>
@endif
