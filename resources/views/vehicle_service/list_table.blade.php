@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
    Vehicle Service List
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
@endif>
<thead>
    <tr>
        <th style="width: 30px;">#</th>
        <th>Vehicle</th>
        <th>Cost</th>
        <th>Reason</th>
        <th>Changed Parts</th>
        <th>Responsible Person</th>
        @if ($for_export ?? false)
        @else
            <th class="">Action</th>
        @endif
    </tr>
</thead>
<tbody>
    @foreach ($data as $key => $item )
    <tr>
        <td>{{ ++$key }}</td>
        <td>{{ $item->vehicle->name }}</td>
        <td>{{ $item->cost }}</td>
        <td>{{ $item->reason }}</td>
        <td>{{ $item->changed_parts }}</td>
        <td>{{ $item->responsible_person_id }}</td>
        @if ($for_export ?? false)
        @else
            <td>

                <a href="javascript:void(0)" class="btn btn-info btn-sm view_vehicle_service"
                    data-name="{{ $item->vehicle->name }}"
                    data-cost="{{ $item->cost }}"
                    data-reason="{{ $item->reason }}"
                    data-changed_parts="{{ $item->changed_parts }}"
                    data-responsible_person_id="{{ $item->responsible_person_id }}">
                    <i class="fa fa-eye"></i>
                </a>
                @if(role_can('vehicle-management') && user()->is_super_admin())
                <a href="{{ route('vehicle_service.edit', $item->id) }}" class="btn btn-info btn-sm"><i class="fa fa-edit"></i> </a>

                <a href="#"
                    onclick="event.preventDefault();
                        if(confirm('Are you sure?')) {
                            $(this).next('form').submit();
                        }" class="delete-link btn btn-danger btn-sm"><i class="fa fa-trash"></i>
                    </a>

                    <form method="POST" action="{{ route('vehicle_service.delete', $item->id) }}" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                    @endif
            </td>
        @endif
    </tr>
    @endforeach
</tbody>
</table>