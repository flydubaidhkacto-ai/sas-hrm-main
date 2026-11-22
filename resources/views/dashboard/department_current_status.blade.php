@extends('layouts.master')
@section('content')
    <script>
        setTimeout(() => {
            let rr = "{{ request()->url() }}";
            $.get(rr).then(res => {
                newHTML = document.open("text/html", "replace");
                newHTML.write(res);
                newHTML.close();
            })
        }, 10000);
    </script>
    <style>
        a.card {
            color: inherit;
        }
        a.card:hover {
            background: rgba(207, 207, 207, 0.815);
        }
    </style>
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">
                            Employees of: {{ $department->name }}
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active">
                                Employees of: {{ $department->name }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Checked In</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    @if (!$in->count())
                                        <i>
                                            No employees are inside!
                                        </i>
                                    @else
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        Code
                                                    </th>
                                                    <th>
                                                        Emp. ID
                                                    </th>
                                                    <th>
                                                        Name
                                                    </th>
                                                    <th>
                                                        Checked In
                                                    </th>
                                                    <th>
                                                        Department
                                                    </th>
                                                    <th>
                                                        Designation
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($in as $employee)
                                                    <tr>
                                                        <td>{{ $employee->id }}</td>
                                                        <td>
                                                            {{ $employee->employee_id }}
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('employees.active.show', $employee) }}">
                                                                {{ $employee->name }}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            {{
                                                                $employee->in_outs()
                                                                    ->orderBy('date_time', 'desc')
                                                                    ->first()?->date_time?->format('d F, Y h:i A')
                                                            }}
                                                        </td>
                                                        <td>
                                                            {{ $employee->department?->name }}
                                                        </td>
                                                        <td>
                                                            {{ $employee->designation?->name }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Checked Out</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    @if (!$out->count())
                                        <i>
                                            No employees are outside!
                                        </i>
                                    @else
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        Code
                                                    </th>
                                                    <th>
                                                        Emp. ID
                                                    </th>
                                                    <th>
                                                        Name
                                                    </th>
                                                    <th>
                                                        Checked out
                                                    </th>
                                                    <th>
                                                        Department
                                                    </th>
                                                    <th>
                                                        Designation
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($out as $employee)
                                                    <tr>
                                                        <td>
                                                            {{ $employee->id }}
                                                        </td>
                                                        <td>
                                                            {{ $employee->employee_id }}
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('employees.active.show', $employee) }}">
                                                                {{ $employee->name }}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            {{
                                                                $employee->in_outs()
                                                                    ->orderBy('date_time', 'desc')
                                                                    ->first()?->date_time?->format('d F, Y h:i A')
                                                            }}
                                                        </td>
                                                        <td>
                                                            {{ $employee->department?->name }}
                                                        </td>
                                                        <td>
                                                            {{ $employee->designation?->name }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    Absent
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    @if ($absent->count() == 0)
                                        <i>
                                            No employees are absent!
                                        </i>
                                    @else
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Emp. ID</th>
                                                    <th>
                                                        Name
                                                    </th>
                                                    <th>
                                                        Department
                                                    </th>
                                                    <th>
                                                        Designation
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($absent as $employee)
                                                    <tr>
                                                        <td>{{ $employee->id }}</td>
                                                        <td>{{ $employee->employee_id }}</td>
                                                        <td>
                                                            <a href="{{ route('employees.active.show', $employee) }}">
                                                                {{ $employee->name }}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            {{ $employee->department?->name }}
                                                        </td>
                                                        <td>
                                                            {{ $employee->designation?->name }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
