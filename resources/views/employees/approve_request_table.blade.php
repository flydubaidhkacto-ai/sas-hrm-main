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
    <tbody>
        @php
            $i = 1
        @endphp
       <thead>
        <th>
            No
        </th>
        <th>
             Requested Employee Name
        </th>
        <th>
            Designation
       </th>
        <th>
            Department
        </th>
        <th>
            Action
        </th>
       </thead>
        @foreach ($edits as $items )
            @php
                $employee = App\Models\User::find($items->user_id);
            @endphp
            <tr>
                <td style="width: 5px">
                    {{ $i++ }}
                </td>
                <td>
                    {{$employee->name}}
                </td>
                <td>
                    {{$employee->designation?->name}}
                </td>
                <td>
                    {{$employee->department?->name}}
                </td>
                <td>
                    <a href="{{route('employees.request.edit.approve.view', $items->id)}}" class="btn btn-info">
                        <i class="fa fa-eye" aria-hidden="true"></i> View
                    </a>

                    <form action="{{ route('employees.request.edit.delete', $items->id) }}" method="post"
                        class="d-inline-block dropdown-item"
                        onclick="if(confirm('Are you sure to want to delete it? You will not be able to undo it!')) {event.preventDefault(); this.submit(); }"
                        >
                        @csrf
                        @method('DELETE')
                        <a type="submit" href="#" class="btn btn-danger"
                        >
                            <i class="fa fa-trash-o m-r-5"></i> Delete
                        </a>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
