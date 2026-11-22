@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Interview Employee List
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
        padding: 3px;
        font-size: 10pt;
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
                width: 100%; /* Make the table width 100% for print */
                font-size: 7pt; /* Adjust font size for better readability in print */
                /* Add any other print-specific styles here */
            }
        }
    </style>
@endif
<table
    @if($for_export ?? false)
        style="width: 100%"
    @else
        class="table table-striped custom-table"
    @endif
>
    <thead>
        <tr>
            <th style="width: 3px">
                S.L
            </th>
            <th>
                Emp. ID
            </th>
            <th>Name</th>
            <th style="width: 30px;">
                Department
            </th>
            <th style="width: 30px;">Sup. Department</th>
            <th>Designation</th>
            <th>Mobile</th>
            @if($for_export ?? false)
                <th>Email</th>
                <th>address</th>
                <th>Expected Salary</th>
            @endif
            <th>
                Interview Date
            </th>

            @if($for_export ?? false)
            @else
                <th class="text-right no-sort">Action</th>
            @endif
        </tr>
    </thead>
    <tbody>
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
                    @if($for_export ?? false)
                        {{ $items->name }}
                    @else
                        <h2 class="table-avatar">
                            <a href="{{ route('employees.interview.show', $items->id) }}">
                                {{ $items->name }}
                            </a>
                        </h2>
                    @endif
                </td>
                <td>{{ $items->department?->name }}</td>
                <td>{{ $items->department?->super_department?->name }}</td>
                <td>{{ $items->designation?->name }}</td>
                <td>{{ $items->phone_number }}</td>
                @if($for_export ?? false)
                    <td>{{ $items->email }}</td>
                    <td>{{ $items->address }}</td>
                    <td>{{ $items->expected_salary }}</td>
                @endif
                <td>
                    {{ carbon($items->interview_date)->format('d M, Y') }}
                </td>
                @if($for_export ?? false)
                @else
                    <td class="text-right">
                        <div class="dropdown dropdown-action">
                            <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                            <div class="dropdown-menu dropdown-menu-right">
                                @if(role_can('interview_comment') || role_can('interview_comment_see'))
                                    <a class="dropdown-item" href="{{ route('employees.interview.show', $items->id) }}">
                                        <i class="fa fa-eye" aria-hidden="true"></i> View
                                    </a>
                                @endif
                                @if((role_can('user') || role_can('user.self.super_department')))
                                    <a class="dropdown-item" href="{{ route('employees.interview.edit', $items->id) }}">
                                        <i class="fa fa-pencil m-r-5"></i> Edit
                                    </a>
                                @endif
                                @if(role_can('user') || role_can('user.self.super_department'))

                                    <a class="dropdown-item"
                                    href="javascript:void(0)"
                                    onclick="openActiveEmployeeModal({{ $items->id }}, '{{ $items->name }}', '{{ $items->designation?->name }}', '{{ $items->department?->name }}', '{{ $items->department?->super_department?->name }}')">
                                        <i class="fa fa-check"></i> Active Employee
                                    </a>

                                    <a class="dropdown-item"
                                    href="javascript:void(0)"
                                    onclick="openProbationEmployeeModal({{ $items->id }}, '{{ $items->name }}', '{{ $items->designation?->name }}', '{{ $items->department?->name }}', '{{ $items->department?->super_department?->name }}')">
                                        <i class="fa fa-check"></i> Probation Period Employee
                                    </a>
                                    <form action="{{ route('employees.interview.delete', $items->id) }}" method="post"
                                        class="d-inline-block dropdown-item"
                                        onclick="if(confirm('Are you sure to want to delete it? You will not be able to undo it!')) {event.preventDefault(); this.submit(); }"
                                        >
                                        @csrf
                                        @method('DELETE')
                                        <a href="#"
                                        >
                                            <i class="fa fa-trash"></i> Delete
                                        </a>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>


