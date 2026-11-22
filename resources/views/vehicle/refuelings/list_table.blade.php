@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
    Vehicle Refueling List
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
        padding: 2px;
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
@endif>
    <thead>
        <tr>
            <th>ID</th>
            <th>Vehicle</th>
            <th>Fuel Type</th>
            <th>Driver</th>
            <th>Fuel Station</th>
            @if($for_export ?? false)
                <th>Place</th>
                <th>Budget</th>
                <th>Kilometer Per Unit</th>
                <th>Refuel Limit</th>
                <th>Max Unit</th>
                <th>Odometer End</th>
                <th>Odometer at Refueling</th>
                <th>Unit Taken</th>
                <th>Last Reading</th>
                <th>Last Unit</th>
                <th>Consumption Percent</th>
            @else
                <th>Actions</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($refuelings as $refueling)
        <tr>
            <td>{{ $refueling->id }}</td>
            <td>{{ $refueling->vehicle->name }}</td>
            <td>{{ $refueling->fuel_type }}</td>
            <td>{{ $refueling->driver->name }}</td>
            <td>{{ $refueling->fuel_station }}</td>
            @if($for_export ?? false)
                <td>{{ $refueling->place ?? '' }}</td>
                <td>{{ $refueling->budget ?? '' }}</td>
                <td>{{ $refueling->km_per_unit ?? '' }}</td>
                <td>{{ $refueling->refuel_limit ?? '' }}</td>
                <td>{{ $refueling->max_unit ?? '' }}</td>
                <td>{{ $refueling->odometer_end_of_day ?? '' }}</td>
                <td>{{ $refueling->odometer_at_refuel ?? '' }}</td>
                <td>{{ $refueling->unit_taken ?? '' }}</td>
                <td>{{ $refueling->last_reading ?? '' }}</td>
                <td>{{ $refueling->last_unit ?? '' }}</td>
                <td>{{ $refueling->consumption_percent ?? '' }}</td>
            @else
                <td>
                    <a href="{{ route('refuelings.show', $refueling->id) }}" class="btn btn-info btn-sm">
                        <i class="fa fa-eye"></i>
                    </a>
                    <a href="{{ route('refuelings.edit', $refueling->id) }}" class="btn btn-warning btn-sm">
                        <i class="fa fa-pen"></i>
                    </a>
                    <form action="{{ route('refuelings.destroy', $refueling->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </td>
            @endif
        </tr>
        @endforeach
    </tbody>
    </table>