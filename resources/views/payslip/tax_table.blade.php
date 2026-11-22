@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Salary Tax Sheet
    </p>

    <style>
        table {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
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
            <th>
                S.L
            </th>
            <th>
                Code
            </th>
            <th>
                Emp. ID
            </th>
            <th>Name</th>
            <th>Sup. Department</th>
            <th>Department</th>
            <th>Designation</th>
            <th>Month</th>
            <th>
                Tax
            </th>
            @if(($for_export ?? false) || $for_one)
            @else
                <th class="text-right no-sort">Action</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($data['employees'] as $item)
            @php $loop_ = 1 @endphp
            @if($for_one)
                @php
                    $loop_ = count($item['months']);
                @endphp
            @endif
            @for ($i = 0; $i < $loop_; $i++)
                <tr>
                    <td>
                        {{ $loop->iteration }}
                    </td>
                    <td>
                        {{ $item['employee']->name }}
                    </td>
                    <td>
                        {{ $item['employee']?->employee_id }}
                    </td>
                    <td>
                        @if($for_export ?? false)
                            {{ $item['employee']?->name }}
                        @else
                            <h2 class="table-avatar">
                                <a href="{{ route('employees.active.show', $item['employee']->id) }}">
                                    {{ $item['employee']?->name }}
                                </a>
                            </h2>
                        @endif
                    </td>
                    <td>{{ $item['employee']?->department?->super_department?->name }}</td>
                    <td>{{ $item['employee']?->department?->name }}</td>
                    <td>{{ $item['employee']->designation?->name }}</td>
                    <td>
                        @if($for_one)
                            {{ carbon($item['months'][$i]['month'])->format('M, Y') }}
                        @else
                            @if(count($item['months']) > 1)
                                {{ carbon($item['months'][0]['month'])->format('M, Y') }} - {{ carbon($item['months'][count($item['months']) - 1]['month'])->format('M, Y') }}
                            @endif
                        @endif
                    </td>
                    <td>
                        @if($for_one)
                            {{ $item['months'][$i]['tax'] }}
                        @else
                            {{$item['tax']}}
                        @endif Tk.
                    </td>
                    @if(($for_export ?? false) || $for_one)
                    @else
                        <td class="text-right">
                            <a href="?employee_id={{ $item['employee']->id }}" class="btn btn-info">
                                See All
                            </a>
                        </td>
                    @endif
                </tr>
            @endfor
        @endforeach
    </tbody>
</table>
