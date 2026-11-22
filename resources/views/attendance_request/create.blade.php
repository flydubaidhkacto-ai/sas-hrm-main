@extends('layouts.master')
@section('title', config('app.customer') === 'vector' ? "Work from Home" : "Att. In/Out Request")
@section('content')
{{-- message --}}
{!! Toastr::message() !!}
<!-- Page Wrapper -->
<div class="page-wrapper" onmousemove="calculateDuration()" onkeyup="calculateDuration()">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">{{ config('app.customer') === 'vector' ? "Work from Home" : "Att. In/Out
                        Request" }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">{{ config('app.customer') === 'vector' ? "Work from Home" :
                            "Att. In/Out Request" }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        @include('layouts.alerts')
        <div>
            <form action="{{ route('attendance-requests.preview') }}" method="POST">
                @csrf
                {{-- <h4>Enter Any-one or Both Date-Time:</h4> --}}
                <div class="row">
                    @if(role_can('attendance-requests'))
                        <div class="col-md-4 mb-2">
                            <label for="">Employee:</label>
                            <select name="employee" class="form-control employee-select2" required>
                            </select>
                        </div>
                    @else
                        <div class="col-md-4 mb-2">
                            <label for="">Employee:</label>
                            <select name="employee" class="form-control">
                                <option value="{{ user()->id }}" selected>{{ user()->name }}</option>
                            </select>
                        </div>
                    @endif
                    <div class="col-md-4 mb-2">
                        <label for="">Select Request type:</label>
                        <select name="type" onchange="cachnge_type()" class="form-control">
                            <option value="IN">Check-In</option>
                            <option value="OUT">Check-Out</option>
                            <option value="IN_OUT">Check-In & Check-Out</option>
                        </select>
                    </div>
                    <div class="mb-2 col-md-4 c-in">
                        <label for="">Check-In Date & Time:</label>
                        <div class="row">
                            <div class="col-7 pr-0">
                                <input type="text" name="check_in_date" class="form-control datetimepicker"
                                    value="{{ old('check_in_date') ?? now()->format('d-m-Y') }}">
                            </div>
                            <div class="col-5 pl-0">
                                <input type="time" name="check_in" class="form-control" value="{{ old('check_in') }}">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 col-md-4 c-out">
                        <label for="">Check-Out Date & Time:</label>
                        <div class="row">
                            <div class="col-7 pr-0">
                                <input type="text" name="check_out_date" class="form-control datetimepicker"
                                    value="{{ old('check_out_date') ?? now()->format('d-m-Y') }}">
                            </div>
                            <div class="col-5 pl-0">
                                <input type="time" name="check_out" class="form-control" value="{{ old('check_out') }}">
                            </div>
                        </div>
                        <span id="shift_duration"></span>
                    </div>
                    <div class="col-md-6">
                        <label for="">Notes (optional): </label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') ?: (config('app.customer') === 'vector' ? 'Working from Home' : '') }}</textarea>
                    </div>
                </div>
                <button class="mt-3 btn btn-info"
                    >
                    <i class="fa fa-check"></i>
                    Submit
                </button>
                {{-- @if(!user()->shift)
                <div class="text-danger">
                    <i>
                        You need to have a shift to use this feature
                    </i>
                </div>
                @endif --}}
            </form>
        </div>
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->
@endsection
@section('script')
<script>
    function calculateDuration()
        {
            let startDate = $('[name=check_in_date]').val();
            let start = $('[name=check_in]').val();
            let endDate = $('[name=check_out_date]').val();
            let end = $('[name=check_out]').val();
            if(!start || !end || !startDate || !endDate) return
            start = moment(`${startDate} ${start}`, 'DD-MM-YYYY HH:mm');
            end = moment(`${endDate} ${end}`, 'DD-MM-YYYY HH:mm');

            const duration = moment.duration(end.diff(start));
            const totalHours = duration.asHours();
            const hours = Math.floor(totalHours);
            const minutes = Math.round((totalHours - hours) * 60);

            document.getElementById('shift_duration').innerText = `${hours} hours and ${minutes} minutes`;
            // document.getElementById('shift_duration').innerText = `Duration: ${hours > 9 ? hours : '0' + hours}:${minutes > 9 ? minutes : '0' + minutes}`;
        }
        function cachnge_type()
        {
            let type = $('[name=type]').val();
            if(type == 'IN_OUT'){
                $('.c-in').removeClass('d-none')
                $('.c-out').removeClass('d-none')
            }else
            if(type == 'IN'){
                $('.c-in').removeClass('d-none')
                $('.c-out').addClass('d-none')
            }
            else
            if(type == 'OUT')
            {
                $('.c-in').addClass('d-none')
                $('.c-out').removeClass('d-none')
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            calculateDuration()
            cachnge_type()
        })
</script>
@endsection