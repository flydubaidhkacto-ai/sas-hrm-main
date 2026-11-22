@extends('layouts.master')
@section('title', "Attendance Entry")
@section('content')
{{-- message --}}
{!! Toastr::message() !!}
<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Attendance Entry</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Attendance Entry</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        @include('layouts.alerts')
        <div>
            <form action="{{ route('attendance.entry.store') }}" method="POST">
                @csrf
                <h4>Enter Attendance Details:</h4>
                <div class="row">
                    <div class="mb-2 col-md-4">
                        <label for="">Search and Select an Employee:</label>
                        <select name="employee" class="form-control select-employee" id="" required>
                            <option value="">Search and select</option>
                            @if(old('employee'))
                            <option selected value="{{ old('employee') }}">{{ old('employee') }}</option>
                            @endif
                        </select>
                        <div class="mt-2">
                            <label for=""> Shift for Attendance:</label>
                            <select name="shift_id" id="" class="form-control" required>
                                <option value="">Select Shift</option>
                                @foreach (\App\Models\Shift::all() as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }} - {{ $shift->start }} - {{
                                    $shift->end }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-2 col-md-8">
                        <div class="items">
                            <div class="row item">
                                <div class="col-sm-6">
                                    <label for="">Check-In:</label>
                                    <div>
                                        <div>
                                            <input type="datetime-local" name="punches[]" class="form-control" value=""
                                                onchange="calculateDuration()" onkeyup="calculateDuration()"
                                                onmouseup="calculateDuration()">
                                            <a href="#" class="float-right"
                                                onclick="this.previousElementSibling.value = ''">
                                                <small>
                                                    <i class="fa fa-ban"></i> Clear time
                                                </small>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="">Check-Out:</label>
                                    <div>
                                        <div>
                                            <input type="datetime-local" name="punches[]" class="form-control" value=""
                                                onchange="calculateDuration()" onkeyup="calculateDuration()"
                                                onmouseup="calculateDuration()">
                                            <a href="#" class="float-right"
                                                onclick="this.previousElementSibling.value = ''">
                                                <small>
                                                    <i class="fa fa-ban"></i> Clear time
                                                </small>
                                            </a>
                                        </div>
                                        <span class="shift_duration"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="mt-2 btn btn-primary btn-sm" type="button" onclick="addMore()">
                            <i class="fa fa-plus"></i>
                            Add More
                        </button>
                    </div>
                    <hr>
                    <div class="col-md-6">
                        <label for="">Notes: </label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="">Duty Type: </label>
                        <select name="duty_type" class="form-control">
                            <option selected>Inside of Office</option>
                            <option>Outside of Office</option>
                            <option>Work from Home</option>
                        </select>
                    </div>
                </div>
                <button class="mt-3 btn btn-primary">
                    Submit
                </button>
            </form>
        </div>
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->
@endsection
@section('script')
<script>
    let super_department = '{{
            !role_can('attendance')
            && role_can('attendance.self.super_department')
            ? user()->department?->super_department_id : ''
        }}'
        let tmpEmployees = {}
        $('.select-employee').select2({
            ajax: {
                url: '{{ route('employees.active.search.all') }}',
                data: function (params) {
                    var query = {
                        search: params.term,
                        page: params.page || 1,
                        super_department: super_department,
                    }
                    return query;
                },
                processResults: function (data) {
                    return {
                        results: data.map(function (item) {
                            tmpEmployees[item.id] = item
                            return {
                                id: item.id,
                                text: item.name + (item.designation ? ' - ' + item.designation.name : '') + (item.department ? ' - ' + item.department.name : ''),
                            }
                        })
                    };
                },
            },
        })
        .on('select2:select', function (e) {
            let employee = tmpEmployees[e.params.data.id]
            let shift = employee.shift
            if(shift) {
                // $('.shift-details').html(
                //     `${shift.name}: <br> - Start: ${moment(shift.start, 'HH:mm').format('h:mm A')} <br> - End: ${moment(shift.end, 'HH:mm').format('h:mm A')}`)
                $('select[name="shift_id"]').val(shift.id).trigger('change')
            }
        });
        function addMore() {
            $('.items').append(`
                <div class="row mt-2 item">
                    <div class="col-sm-6">
                        <label for="">Check-In:</label>
                        <input type="datetime-local" name="punches[]" class="form-control" value=""
                            onchange="calculateDuration()"
                            onkeyup="calculateDuration()"
                            onmouseup="calculateDuration()"
                        >
                        <a href="#" class="float-right" onclick="this.previousElementSibling.value = ''">
                            <small>
                                <i class="fa fa-ban"></i> Clear time
                            </small>
                        </a>
                    </div>
                    <div class="col-sm-5">
                        <label for="">Check-Out:</label>
                        <input type="datetime-local" name="punches[]" class="form-control" value=""
                            onchange="calculateDuration()"
                            onkeyup="calculateDuration()"
                            onmouseup="calculateDuration()"
                        >
                        <a href="#" class="float-right" onclick="this.previousElementSibling.value = ''">
                            <small>
                                <i class="fa fa-ban"></i> Clear time
                            </small>
                        </a>
                        <span class="shift_duration"></span>
                    </div>
                    <div class="col-sm-1 text-right text-end">
                        <label>&nbsp;</label>
                        <div>
                            <button class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.parentElement.remove()">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `)
        }

        function calculateDuration()
        {
            let el = event.target
            $(el).closest('.item').each(function () {
                let start = $(this).find('input[type=datetime-local]').first().val()
                let end = $(this).find('input[type=datetime-local]').last().val()
                if(!start || !end) return

                start = moment(`${start}`);
                end = moment(`${end}`);

                const duration = moment.duration(end.diff(start));
                const totalHours = duration.asHours();
                let hours = Math.floor(totalHours);
                let minutes = Math.round((totalHours - hours) * 60);
                if(hours < 10) hours = '0' + hours
                if(minutes < 10) minutes = '0' + minutes
                $(this).find('.shift_duration').html(`<small>Duration: ${hours}:${minutes}</small>`)
            })
        }
</script>
@endsection