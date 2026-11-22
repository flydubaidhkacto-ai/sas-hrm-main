@extends('layouts.master')
@section('title', "Geo Location checkins")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Geo Location Check-In Report</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Check In Report</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Search Filter -->
        <form action="" method="get" class="mb-3">
            <div class="row filter-row">
                @if(role_can('locations') || user()->geo_location_departments)
                <div class="col-sm-6 col-md-3">
                    <label for="">Employee</label>
                    <select name="employee" id="" class="form-control">
                        <option value="">Select Employee</option>
                        @foreach (\App\Models\User::active_employees()
                            ->with(['weekly_schedules.location', 'department', 'designation'])
                            ->where('have_geo_location', true)
                            ->when(!role_can('locations') && user()->geo_location_departments, function ($q) {
                                $q->whereHas('department', fn($q2) => $q2->whereIn('super_department_id', user()->geo_location_departments));
                            })
                            ->get() as $item)
                            <option value="{{ $item->id }}" @selected(request()->employee == $item->id)>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @else
                <input type="hidden" name="employee" value="{{ user()->id }}">
                @endif
                <div class="col-sm-4 col-md-3">
                    <div class="form-group">
                        <label for="">From Date</label>
                        <input type="text" name="from_date" class="form-control datetimepicker" value="{{ $from_date }}" required>
                    </div>
                </div>
                <div class="col-sm-4 col-md-3">
                    <div class="form-group">
                        <label for="">To Date</label>
                        <input type="text" name="to_date" class="form-control datetimepicker" value="{{ $to_date }}" required>
                    </div>
                </div>
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-info">
                        <i class="fa fa-magnifying-glass"></i>
                        Search
                    </button>
                </div>
            </div>
        </form>
        <!-- Search Filter -->
        {{-- message --}}
        {!! Toastr::message() !!}
        @if ($data)
            <div class="exports text-right">
                @include('layouts.exports', [
                    'no_docx' => true,
                    'no_pdf' => true,
                    'no_print' => true,
                ])
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped" id="datatable" wefaw>
                            <thead>
                                <tr>
                                    <th >S.L</th>
                                    <th >Emp Code</th>
                                    <th >Emp ID</th>
                                    <th >Employee</th>
                                    @if (config('have_multi_sup_dept'))
                                        <th>Sup. Dept.</th>
                                    @endif
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Date</th>
                                    <th>Visited Locations</th>
                                    <th>Total Distance (KM)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $item)
                                     <tr>
                                         <td>{{ $loop->iteration }}</td>
                                         <td>{{ $item->user?->id }}</td>
                                         <td>{{ $item->user?->employee_id }}</td>
                                         <td>
                                            <div style="width: 130px">
                                            {{ $item->user?->name }}
                                            </div>
                                        </td>
                                         @if (config('have_multi_sup_dept'))
                                             <td>
                                            <div style="width: 130px">{{ $item->user?->department?->super_department?->name }}</div></td>
                                         @endif
                                         <td>{{ $item->user?->department?->name }}</td>
                                         <td>{{ $item->user?->designation?->name }}</td>
                                         <td>
                                            <div style="width: 130px">{{ $item->check_at->format('d F, Y') }}</div></td>
                                         <td>
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Location</th>
                                                        <th>Note</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($item->locations as $loc)
                                                        <tr>
                                                            <td>
                                                                <div style="width: 300px">
                                                                    {{ $loop->iteration }}.
                                                                    {{ $loc->location_name }}</div>
                                                            </td>
                                                            <td>
                                                                <div style="width: 190px">{{ $loc->note ?: '--' }}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                         </td>
                                         <td>
                                            {{ number_format($item->total_distance_km, 2) }} KM
                                         </td>
                                     </tr>
                                 @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            @if (request()->has('from_date') && request()->has('to_date'))
                <div class="alert alert-danger">
                    No data found for the date range.
                </div>
            @else
                <div class="alert alert-info">
                    Please select date range!
                </div>
            @endif
        @endif
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->
{{-- Have --}}
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('#datatable', {
        pageLength: 50
    });
</script>
@endsection

