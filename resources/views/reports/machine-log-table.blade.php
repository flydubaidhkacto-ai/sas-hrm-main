<div class="table-responsive">
    <table class="table table-striped custom-table mb-0">
        <thead>
            <tr>
                <th>
                    S.L
                </th>
                <th>
                    Code
                </th>
                <th>
                    Emp. ID
                </th>
                <th>Employee</th>
                <th>Timestamp</th>
                @if(\App\Models\Setting::block_auto_inout_state())
                <th>Status</th>
                @endif
                <th>Designation</th>
                <th>Department</th>
                @if(config('have_multi_sup_dept'))
                <th>Sup. Department</th>
                @endif
                <th>Device</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->user_id }}</td>
                    <td>
                        {{ $item->employee?->employee_id }}
                    </td>
                    <td>
                        <h2 class="table-avatar">
                            <a href="{{ route('employees.active.show', $item->user_id) }}">
                                @if ($item->employee)
                                    {{ $item->employee?->name }}
                                @else
                                    <i class="text-danger">
                                        Unknown user
                                    </i>
                                @endif
                            </a>
                        </h2>
                    </td>
                    <td>
                        {{ $item->date_time->format('j F, Y h:i A') }}
                        @if ($item && $item->location)
                            <a href="https://www.google.com/maps?q={{ str_replace('-', ',', $item->location) }}" target="_blank">
                                View <i class="fa fa-location"></i>
                            </a>
                        @endif
                    </td>
                    @if(\App\Models\Setting::block_auto_inout_state())
                    <td>
                        @if ($item->status == 0)
                            <span class="badge badge-success">Check In</span>
                        @elseif ($item->status == 1)
                            <span class="badge badge-danger">Check Out</span>
                        @else
                            <i class="text-danger" data-toggle="tooltip" title="{{ $item->status }}">Unknown</i>
                        @endif
                    </td>
                    @endif
                    <td>
                        {{ $item->employee?->designation?->name }}
                    </td>
                    <td>
                        {{ $item->employee?->department?->name }}
                    </td>
                    @if(config('have_multi_sup_dept'))
                    <td>
                        {{ $item->employee?->department?->super_department?->name }}
                    </td>
                    @endif
                    <td>
                        @if ($item->device)
                            {{  $item->device->name }}
                            - {{  $item->device->device_name }}
                        @elseif($item->cloud_device)
                            {{ $item->cloud_device->name }} - {{ $item->cloud_device->device_sn }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
