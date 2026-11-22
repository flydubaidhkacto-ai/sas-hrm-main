@extends('layouts.master')
@section('title', "Employee list")
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">W.Hour Grace-time</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">W.Hour Grace-time</li>
                        </ul>
                    </div>
                </div>
            </div>
			<!-- /Page Header -->
            {{-- message --}}
            {!! Toastr::message() !!}
            <div class="alert alert-success">
                <i class="fa-solid fa-info-circle"></i>
                <span>
                    Grace time is the time in minutes that will always be added to the attendance working time of the employee.
                </span>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table
                            class="table table-striped custom-table"
                        >
                            <thead>
                                <tr>
                                    <th style="width: 3px">
                                        S.L
                                    </th>
                                    <th style="width: 130px">
                                        Grace Time (Minutes)
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
                                    <th>Mobile</th>
                                    <th>Shift</th>
                                    <th>
                                        O.T.
                                    </th>
                                    <th>O.D.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 1
                                @endphp
                                @foreach ($users as $items )
                                    <tr>
                                        <td style="width: 5px">
                                            {{ $i++ }}
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" data-user-id="{{ $items->id }}" value="{{ $items->grace_minutes }}" oninput="updateTime(this)">
                                        </td>
                                        <td style="width: 20px; height: 20px">
                                            {{$items->id}}
                                        </td>
                                        <td>
                                            {{ $items->employee_id }}
                                        </td>
                                        <td>
                                            <h2 class="table-avatar">
                                                <a href="{{ route('employees.active.show', $items->id) }}">
                                                    {{ $items->name }}
                                                    @php
                                                        $count = $items->zk_fingerprints()->count();
                                                    @endphp
                                                    @if($count)
                                                        (<i class="fa-solid fa-fingerprint"></i> {{ $count }})
                                                    @endif
                                                </a>
                                            </h2>
                                        </td>
                                        <td>{{ $items->department?->name }}</td>
                                        <td>{{ $items->department?->super_department?->name }}</td>
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Content -->

    </div>
    <!-- /Page Wrapper -->
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
        function updateTime(el)
        {
            let user_id = $(el).data('user-id');
            let time = $(el).val();
            $.ajax({
                url: "{{ route('attendance.gracetime') }}",
                type: "GET",
                data: { user: user_id, gracetime: time },
                success: function(response) {
                    el.value = response || 0;
                },
                error: function(response) {
                    console.log(response);
                    el.value = '0';
                }
            });
        }
    </script>
@endsection
