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
            <th style="width: 16px; height: 20px;">
                Code
            </th>
            <th>
                Emp. ID
            </th>
            <th>Name</th>
            <th>
                Department
            </th>
            <th>Sup. Department</th>
            <th>Designation</th>
        </tr>
    </thead>
    <tbody>
        @php
            $i = 1;
            $selectedId = [];
        @endphp
        @foreach ($employees as $key => $items )
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
                    {{ $items->name }}
                    @else
                        <h2 class="table-avatar">
                            <a href="{{ route('employees.active.show', $items->id) }}">
                                {{ $items->name }}
                            </a>
                        </h2>
                    @endif
                </td>
                <td>{{ $items->department?->name }}</td>
                <td>{{ $items->department?->super_department?->name }}</td>
                <td>{{ $items->designation?->name }}</td>
            </tr>
            @php
                $selectedId[$key] = $items->id;
            @endphp
        @endforeach
    </tbody>
</table>
{{-- @dump($selectedId) --}}
<form action="{{route('sms.employee.send')}}" method="post">
    @csrf
    @foreach ($employees as $key => $items )
        <input type="hidden" name="users[]" id="" value="{{$items->id}}">
    @endforeach
    <button type="submit" class="btn btn-info btn-lg active">Send Message</button>
</form>

