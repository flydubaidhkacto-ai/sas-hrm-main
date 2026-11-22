@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Employee List
    </p>

    <style>
        table {
        font-family: Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        width: 100%;
        background: #fff !important;
        }

        table td, table th {
        border: 1px solid #ddd;
        padding: 8px;
        }

        table tr:nth-child(even){background-color: #f2f2f2;}

        table tr:hover {background-color: #ddd;}

        table th {
        padding-top: 10px;
        padding-bottom: 10px;
        text-align: left;
        background-color: #643a86;
        color: white;
        }
        @media print {
            table th {
                background-color: unset;
                color: unset;
            }
            table {
                width: 100%;
                font-size: 7pt;
            }
        }
    </style>
@endif
<table
    @if($for_export ?? false)
        style="width: 100%"
    @elseif(isset($groupedEmployees) && count($groupedEmployees) > 0)
        class="table table-striped custom-table grouped-table"
    @else
        class="table table-striped custom-table"
    @endif
>
    <thead>
        <tr>
            <th style="width: 3px">
                S.L
            </th>
            <th style="width: 16px; height: 20px;">
                Code
            </th>
            <th>
                Emp. ID
            </th>
            <th>Name</th>
            <th style="width: 30px;">
                Department
            </th>
            @if(config('have_multi_sup_dept'))
            <th style="width: 30px;">Sup. Department</th>
            @endif
            <th>Designation</th>
            <th>Mobile</th>
            <th>Shift</th>
            <th>
                O.T.
            </th>
            <th>O.D.</th>
            <th>Pay type</th>
            @if (has_module('payslip-payment'))
                <th>
                    Payroll Balance
                </th>
            @endif
            @if($for_export ?? false)
                <th>
                    Join Date
                </th>
            @else
                <th>
                    Status
                </th>
                <th>
                    Role
                </th>
                <th class="text-right no-sort">Action</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @if(isset($groupedEmployees) && count($groupedEmployees) > 0)
            @php
                $stationIndex = 1;
            @endphp
            @foreach ($groupedEmployees as $station => $departments)
                {{-- Station (Super Department) Header Row --}}
                <tr style="background-color: #3f51b5; color: #fff; font-weight: bold;">
                    <td colspan="20" style="padding: 10px 15px; font-size: 16px;">
                        <i class="fa fa-map-marker"></i> Station: {{ $station }}
                    </td>
                </tr>
                @foreach ($departments as $deptName => $employees)
                    {{-- Department Header Row --}}
                    <tr style="background-color: #e8eaf6; font-weight: bold;">
                        <td colspan="20" style="padding: 8px 15px 8px 25px; font-size: 14px; color: #1a237e;">
                            <i class="fa fa-building-o"></i> Department: {{ $deptName }}
                        </td>
                    </tr>
                    @php
                        $sl = 1;
                    @endphp
                    @foreach ($employees as $items)
                        <tr>
                            <td style="width: 5px">
                                {{ $sl++ }}
                            </td>
                            <td style="width: 20px; height: 20px">
                                {{$items->id}}
                            </td>
                            <td>
                                {{ $items->employee_id }}
                            </td>
                            <td>
                                @if($for_export ?? false)
                                    {{ $items->name . ' ' }}
                                    @if ($items->zk_device_fingerprint_json)
                                        <i class="fa-solid fa-fingerprint"></i>
                                    @endif
                                @else
                                    <h2 class="table-avatar">
                                        <a href="{{ route('employees.active.show', $items->id) }}">
                                            {{ $items->name }}
                                            @php
                                                $count = $items->zk_fingerprints()->count();
                                            @endphp
                                            @if($count)
                                                (<i class="fa-solid fa-fingerprint"></i> {{ $count }})
                                            @endif
                                            @if(!$items->is_active)
                                                <br />
                                                <span class="text-muted">Terminated since {{ carbon($items->updated_at)->format('d M, Y h:i A') }}</span>
                                            @endif
                                        </a>
                                    </h2>
                                    @if($items->schedule_termination_at)
                                        <br>
                                        <span class="badge badge-warning">
                                            <i class="fa fa-clock-o"></i> Scheduled for termination at
                                            {{ carbon($items->schedule_termination_at)->format('d M, Y h:i A') }}
                                        </span>
                                    @endif
                                    @if($items->schedule_re_activation_at)
                                        <br>
                                        <span class="badge badge-success">
                                            <i class="fa fa-clock-o"></i> Scheduled for re-activation at
                                            {{ carbon($items->schedule_re_activation_at)->format('d M, Y h:i A') }}
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $items->department?->name }}</td>
                            @if(config('have_multi_sup_dept'))
                                <td>{{ $items->department?->super_department?->name }}</td>
                            @endif
                            <td>{{ $items->designation?->name }}</td>
                            <td>{{ $items->phone_number }}</td>
                            <td>
                                @if($items->shift)
                                    {{ $items->shift->name }}
                                @endif
                            </td>
                            <td>
                                @if($items->can_overtime)
                                    <span class="text-info">Yes</span>
                                @else
                                    <span class="text-danger">No</span>
                                @endif
                            </td>
                            <td>
                                @if ($items->can_outside_attendence)
                                    <span class="text-info">Yes</span>
                                @else
                                    <span class="text-danger">No</span>
                                @endif
                            </td>
                            <td>
                               {{$items->paytype}}
                            </td>
                            @if (has_module('payslip-payment'))
                                <td>
                                    {{ $items->payroll_balance }} Tk
                                </td>
                            @endif
                            @if($for_export ?? false)
                                <td>
                                    {{ carbon($items->join_date)->format('d M, Y') }}
                                </td>
                            @else
                                <td>
                                    @if($items->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-warning">Terminated</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $items->assinged_role?->name ?: "None" }}
                                </td>
                                <td class="text-right">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            @if(role_can('user') || role_can('user.self.super_department'))
                                                <a class="dropdown-item" href="{{ route('employees.active.show', $items->id) }}">
                                                    <i class="fa fa-eye" aria-hidden="true"></i> View
                                                </a>
                                            @endif
                                            @if((role_can('user') || role_can('user.self.super_department') || in_array($items->department?->super_department_id, user()->geo_location_departments ?: [])) && $items->is_active)
                                                <a class="dropdown-item" href="{{ route('employees.active.edit', $items->id) }}">
                                                    <i class="fa fa-pencil m-r-5"></i> Edit
                                                </a>
                                            @endif
                                            @if (role_can('role') && $items->is_active)
                                                <a class="dropdown-item" href="{{ route('head-of-employees', $items->id)}}">
                                                    <i class="la la-user-check m-r-5"></i>
                                                    Make Head of Specific Employees
                                                </a>
                                            @endif
                                            @if (role_can('promotion-apply') && $items->is_active)
                                                <a class="dropdown-item" href="{{ route('promotion.apply', $items->id)}}">
                                                    <i class="fa-brands fa-product-hunt m-r-5"></i> Promote
                                                </a>
                                            @endif
                                            @if(role_can('user') || role_can('user.self.super_department') || is_admin_of_super_department($items->department?->super_department_id))

                                                @if($items->is_active)
                                                    <a class="dropdown-item"
                                                    href="javascript:void(0)"
                                                    onclick="openTerminateModal({{ $items->id }}, '{{ $items->name }}', '{{ $items->designation?->name }}', '{{ $items->department?->name }}', '{{ $items->department?->super_department?->name }}')">
                                                        <i class="fa fa-times"></i> Terminate employee
                                                    </a>
                                                @else
                                                    <a class="dropdown-item text-success"
                                                    href="javascript:void(0)"
                                                    onclick="openActivateModal({{ $items->id }}, '{{ $items->name }}', '{{ $items->designation?->name }}', '{{ $items->department?->name }}', '{{ $items->department?->super_department?->name }}')">
                                                        <i class="fa fa-check"></i> Activate employee
                                                    </a>
                                                    <form action="{{ route('employees.active.delete', $items->id) }}" method="post"
                                                        class="d-inline-block dropdown-item"
                                                        onclick="if(confirm('Are you sure to want to delete it? You will not be able to undo it!')) {event.preventDefault(); this.submit(); }"
                                                        >
                                                        @csrf
                                                        @method('DELETE')
                                                        <a href="#"
                                                        >
                                                            <i class="fa fa-trash-o m-r-5"></i> Delete
                                                        </a>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        @else
            {{-- Fallback to flat list if groupedEmployees is not available --}}
            @php
                $i = 1
            @endphp
            @foreach ($employees as $items )
                <tr>
                    <td style="width: 5px">
                        {{ $i++ }}
                    </td>
                    <td style="width: 20px; height: 20px">
                        {{$items->id}}
                    </td>
                    <td>
                        {{ $items->employee_id }}
                    </td>
                    <td>
                        @if($for_export ?? false)
                            {{ $items->name . ' ' }}
                            @if ($items->zk_device_fingerprint_json)
                                <i class="fa-solid fa-fingerprint"></i>
                            @endif
                        @else
                            <h2 class="table-avatar">
                                <a href="{{ route('employees.active.show', $items->id) }}">
                                    {{ $items->name }}
                                    @php
                                        $count = $items->zk_fingerprints()->count();
                                    @endphp
                                    @if($count)
                                        (<i class="fa-solid fa-fingerprint"></i> {{ $count }})
                                    @endif
                                    @if(!$items->is_active)
                                        <br />
                                        <span class="text-muted">Terminated since {{ carbon($items->updated_at)->format('d M, Y h:i A') }}</span>
                                    @endif
                                </a>
                            </h2>
                            @if($items->schedule_termination_at)
                                <br>
                                <span class="badge badge-warning">
                                    <i class="fa fa-clock-o"></i> Scheduled for termination at
                                    {{ carbon($items->schedule_termination_at)->format('d M, Y h:i A') }}
                                </span>
                            @endif
                            @if($items->schedule_re_activation_at)
                                <br>
                                <span class="badge badge-success">
                                    <i class="fa fa-clock-o"></i> Scheduled for re-activation at
                                    {{ carbon($items->schedule_re_activation_at)->format('d M, Y h:i A') }}
                                </span>
                            @endif
                        @endif
                    </td>
                    <td>{{ $items->department?->name }}</td>
                    @if(config('have_multi_sup_dept'))
                        <td>{{ $items->department?->super_department?->name }}</td>
                    @endif
                    <td>{{ $items->designation?->name }}</td>
                    <td>{{ $items->phone_number }}</td>
                    <td>
                        @if($items->shift)
                            {{ $items->shift->name }}
                        @endif
                    </td>
                    <td>
                        @if($items->can_overtime)
                            <span class="text-info">Yes</span>
                        @else
                            <span class="text-danger">No</span>
                        @endif
                    </td>
                    <td>
                        @php
                            // $perms = ($items->assinged_role?->permissions ?: []);
                        @endphp
                        @if ($items->can_outside_attendence)
                            <span class="text-info">Yes</span>
                        @else
                            <span class="text-danger">No</span>
                        @endif
                    </td>
                    <td>
                       {{$items->paytype}}
                    </td>
                    @if (has_module('payslip-payment'))
                        <td>
                            {{ $items->payroll_balance }} Tk
                        </td>
                    @endif
                    @if($for_export ?? false)
                        <td>
                            {{ carbon($items->join_date)->format('d M, Y') }}
                        </td>
                    @else
                        <td>
                            @if($items->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-warning">Terminated</span>
                            @endif
                        </td>
                        <td>
                            {{ $items->assinged_role?->name ?: "None" }}
                        </td>
                        <td class="text-right">
                            <div class="dropdown dropdown-action">
                                <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    @if(role_can('user') || role_can('user.self.super_department'))
                                        <a class="dropdown-item" href="{{ route('employees.active.show', $items->id) }}">
                                            <i class="fa fa-eye" aria-hidden="true"></i> View
                                        </a>
                                    @endif
                                    @if((role_can('user') || role_can('user.self.super_department') || in_array($items->department?->super_department_id, user()->geo_location_departments ?: [])) && $items->is_active)
                                        <a class="dropdown-item" href="{{ route('employees.active.edit', $items->id) }}">
                                            <i class="fa fa-pencil m-r-5"></i> Edit
                                        </a>
                                    @endif
                                    @if (role_can('role') && $items->is_active)
                                        <a class="dropdown-item" href="{{ route('head-of-employees', $items->id)}}">
                                            <i class="la la-user-check m-r-5"></i>
                                            Make Head of Specific Employees
                                        </a>
                                    @endif
                                    @if (role_can('promotion-apply') && $items->is_active)
                                        <a class="dropdown-item" href="{{ route('promotion.apply', $items->id)}}">
                                            <i class="fa-brands fa-product-hunt m-r-5"></i> Promote
                                        </a>
                                    @endif
                                    @if(role_can('user') || role_can('user.self.super_department') || is_admin_of_super_department($items->department?->super_department_id))

                                        @if($items->is_active)
                                            <a class="dropdown-item"
                                            href="javascript:void(0)"
                                            onclick="openTerminateModal({{ $items->id }}, '{{ $items->name }}', '{{ $items->designation?->name }}', '{{ $items->department?->name }}', '{{ $items->department?->super_department?->name }}')">
                                                <i class="fa fa-times"></i> Terminate employee
                                            </a>
                                        @else
                                            <a class="dropdown-item text-success"
                                            href="javascript:void(0)"
                                            onclick="openActivateModal({{ $items->id }}, '{{ $items->name }}', '{{ $items->designation?->name }}', '{{ $items->department?->name }}', '{{ $items->department?->super_department?->name }}')">
                                                <i class="fa fa-check"></i> Activate employee
                                            </a>
                                            <form action="{{ route('employees.active.delete', $items->id) }}" method="post"
                                                class="d-inline-block dropdown-item"
                                                onclick="if(confirm('Are you sure to want to delete it? You will not be able to undo it!')) {event.preventDefault(); this.submit(); }"
                                                >
                                                @csrf
                                                @method('DELETE')
                                                <a href="#"
                                                >
                                                    <i class="fa fa-trash-o m-r-5"></i> Delete
                                                </a>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

@if(!isset($groupedEmployees) || count($groupedEmployees) == 0)
@if(!($for_export ?? false))
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('table', {
        pageLength: 50
    });
</script>
@endif
@endif