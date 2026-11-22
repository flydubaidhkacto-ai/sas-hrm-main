@extends('layouts.master')
@section('title', 'Role: Head Of Employees')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                {!! Toastr::message() !!}
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Manage Head Of Employees</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item active">Select Employee to assign to {{ $user->name }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row mt-2 mb-3">
                <div class="col-3">
                    <img src="{{ URL::to('assets/img/logo.png') }}" alt="">
                </div>
                <div class="col-9">
                    <h3>{{ $user->name }}</h3>
                    <div>{{ $user->email }}</div>
                    <div>Phone: {{ $user->phone_number }}</div>
                    <div>Sup. Department: {{ $user->department?->super_department?->name }}</div>
                    <div>Department: {{ $user->department?->name }}</div>
                </div>
            </div>
            <div class="mb-3">
                <div class="alert alert-warning">
                    <b>Note:</b>
                    This will not automatically assign permissions & roles to employees. You need to assign permissions &
                    roles manually from the
                    <a href="{{ route('role.index') }}">Roles</a>
                    Management. This Feature will only remove the barrier to be on the same super department as his
                    employees. So Even a Sales Super Department Head can Manage Leaves/Att. Req/Others of Tech/Other Super
                    Department Employees.
                </div>
            </div>
            {!! Toastr::message() !!}

            <form class="row" method="post" action="{{ route('head-of-employees', $user) }}">
                @csrf
                <div class="col-12 mb-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check"></i>
                        Assign <span class="count">{{ $user->managed_employees->count() }}</span> Emp. To
                        {{ $user->name }}
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table
                            @if ($for_export ?? false) style="width: 100%"
                            @else
                                class="table table-striped custom-table" @endif>
                            <thead>
                                <tr>
                                    <th>
                                        Choose
                                    </th>
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
                                    <th style="width: 30px;">Sup. Department</th>
                                    <th>Designation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 1;
                                @endphp
                                @foreach ($emps as $items)
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    id="flexCheckDefault{{ $items->id }}" style="scale: 1.5"
                                                    value="{{ $items->id }}"
                                                    onchange="if(this.checked) {
                                                $('.count').html((parseInt($('.count').first().text())) + 1);
                                            } else {
                                                $('.count').html((parseInt($('.count').first().text())) - 1);
                                            }"
                                                    name="employee_ids[]" @if ($user->managed_employees->contains($items->id)) checked @endif>
                                                <label class="form-check-label" for="flexCheckDefault{{ $items->id }}">
                                                    Select
                                                </label>
                                            </div>
                                        </td>
                                        <td style="width: 5px">
                                            {{ $i++ }}
                                        </td>
                                        <td style="width: 20px; height: 20px">
                                            {{ $items->id }}
                                        </td>
                                        <td>
                                            {{ $items->employee_id }}
                                        </td>
                                        <td>
                                            @if ($for_export ?? false)
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
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-12 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check"></i>
                        Assign <span class="count">{{ $user->managed_employees->count() }}</span> Emp. To
                        {{ $user->name }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        let table = new DataTable('table', {
            pageLength: 50
        });;
    </script>
@endsection
@section('script')
    <script>
        $('.init-select2-shift').select2({}).on('change', function() {
            $(this).next().next().next().css('display', 'block')
        })
    </script>
@endsection
