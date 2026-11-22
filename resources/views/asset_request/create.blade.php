@extends('layouts.master')
@section('title', "Asset Request")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">
                        Asset Request
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        @if(!user()->is_super_admin() && role_can('asset-request'))
                        <li class="breadcrumb-item"><a href="{{route('asset_request.all')}}">Asset Request</a></li>
                        @else
                        <li class="breadcrumb-item"><a href="{{route('asset_request')}}">Asset Request</a></li>
                        @endif
                        <li class="breadcrumb-item active">
                            Asset Request
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <form action="{{ route('asset_request.store') }}" method="POST">
            @csrf
            <div class="row">
                @if(user()->is_super_admin())
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="col-form-label">Select Employee <sup class="text-danger"></sup>:</label>
                        <br>
                        <select name="employee_id" id="" class="form-control employee-select2">
                            @if(request()->employee)
                            <option value="{{ request()->employee }}" selected>
                                {{ \App\Models\User::find(request()->employee)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                </div>
                @else
                <input type="hidden" name="employee_id" value="{{ user()->id }}">
                @endif
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-form-label">Asset Name <sup class="text-danger"></sup>:</label>
                        <br>
                        <select class="form-control select2" id="asset_id" name="asset_id" required>
                            <option value="">-- Select --</option>
                            @foreach ($asset as $row)
                            <option value="{{ $row->id }}">{{ $row->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-form-label">Reason <sup class="text-danger"></sup>:</label>
                        <br>
                        <textarea name="reason" class="form-control" placeholder="Reason"
                            required>{{ old('reason') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Start Date:</label>
                        <input type="text" name="start_date" class="form-control datetimepicker" required
                            value="{{ old('start_date') }}" onchange="calculateDuration()">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">End Date:</label>
                        <input type="text" name="end_date" class="form-control datetimepicker"
                            value="{{ old('end_date') }}" onchange="calculateDuration()">
                    </div>
                </div>
            </div>

            <div class="row">
                @if(user()->is_super_admin())
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select name="status" class="form-control select2" required>
                            <option value="pending" {{ old('status', 'approved' )=='pending' ? 'selected' : '' }}>
                                Pending</option>
                            <option value="approved" {{ old('status', 'approved' )=='approved' ? 'selected' : '' }}>
                                Approved</option>
                        </select>
                    </div>
                </div>
                @else
                <input type="hidden" name="status" value="pending">
                @endif
            </div>

            <div class="mt-2">
                <button class="btn btn-primary">
                    Create
                </button>
            </div>
        </form>
    </div>
</div>

<!-- /Page Wrapper -->
@section('script')
<script>
    $('.select2').select2({})

    calculateDuration = () => {
            let start_date = $('.datetimepicker[name="start_date"]').val();
            let end_date = $('.datetimepicker[name="end_date"]').val();
            if(!start_date || !end_date) return
            start_date = moment(start_date, 'DD-MM-YYYY');
            end_date = moment(end_date, 'DD-MM-YYYY');
            // const duration = (end_date.diff(start_date, 'days'));
            // const days = duration + 1;
            // if(days < 1)
            // {
            //     return document.getElementById('leave_duration').innerText = 'Invalid date';
            // }
            // document.getElementById('leave_duration').innerText = days + ' ' + (days > 1 ? 'days' : 'day');
        }

</script>
@endsection
@endsection