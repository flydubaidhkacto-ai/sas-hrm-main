@extends('layouts.master')
@section('title', "Notifications")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Notification</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Notification</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_holiday"><i
                            class="fa fa-plus"></i> Add Notification</a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        {{-- message --}}
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        @php
        $today_date = today()->format('d-m-Y');
        @endphp
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">

                    <table class="table-striped custom-table datatable table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Title </th>
                                <th>Notice </th>
                                <th>Notify Time</th>
                                <th>Assigned To</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($notifications as $key => $items)
                            @php
                            $dateTime = Carbon\Carbon::parse($items->time_of_notify);
                            $time = $dateTime->toTimeString();
                            $time24 = Carbon\Carbon::parse($time);
                            // Get the date component (year, month, and day)
                            $date = $dateTime->toDateString();
                            $time12 = $time24->format('h:i A');

                            $employee = App\Models\User::find($items->user_id);
                            // dump();
                            $employe_information = $employee
                            ? $employee->name
                            : 'All User';
                            $emp_id = $employee ? $employee->id : null;
                            @endphp

                            <tr class="holiday-upcoming">
                                <td hidden class="id">{{ $items->id }}</td>
                                <td>{{ $notifications->firstItem() + $key }}</td>
                                <td class="titleName">{{ $items->title }}</td>
                                <td style="">
                                    <div class="notificationMassage d-none">{{ $items->message }}</div>
                                    <div style="width: 400px;">
                                        {!! $items->message !!}
                                    </div>
                                </td>
                                <td>
                                    <div class="dateTi d-none">{{ $items->time_of_notify }}</div>
                                    {{ $date . ' - ' . $time12 }}
                                </td>
                                <td>
                                    <div style="width: 300px;">
                                        {{ $employe_information }}
                                    </div>
                                </td>
                                <td hidden class="emp_id">{{ $emp_id }}</td>

                                {{-- <td hidden class="endDate" date="{{ carbon($items->end_date)->format('d-m-Y') }}">
                                    {{$items->end_date }}</td>
                                <td>
                                    {{date('d F, Y',strtotime($items->end_date)) }}
                                </td> --}}
                                <td class="text-right">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown"
                                            aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item userUpdate" data-id="'.$items->id.'"
                                                href="{{ route('notification.edit', [$items->id]) }}"><i
                                                    class="fa fa-pencil m-r-5"></i>
                                                Edit</a>
                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                data-target="#delete_holiday"
                                                onclick="$('#delete_holiday .d_id').val({{ $items->id }})">
                                                <i class="fa fa-trash-o m-r-5"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            {{-- @endif --}}
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $notifications->withQueryString()->links() }}
            </div>
        </div>
    </div>
    <!-- /Page Content -->
    <!-- Add Holiday Modal -->
    <div class="modal custom-modal fade" id="add_holiday" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Notification</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('notification.save') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Notification Titel<span class="text-danger">*</span></label>
                            <input class="form-control" type="text" id="title" name="title">
                        </div>
                        <div class="form-group">
                            <label>Notification Message <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" id="message" name="message">
                        </div>
                        <div class="form-group">
                            <label>Notify Time <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" id="datepicker" name="notifyTime">
                        </div>

                        <div class="form-group">
                            <div class="row filter-row">
                                <div class="col-12 mt-2">
                                    <label class="mr-5" for="">Employee :</label>
                                    <select name="employee" id="" class="form-control employee-select2">
                                        @if (request()->employee)
                                        <option value="{{ request()->employee }}" selected>
                                            {{ \App\Models\User::find(request()->employee)?->name }}
                                        </option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Holiday Modal -->


    <!-- Delete Holiday Modal -->
    <div class="modal custom-modal fade" id="delete_holiday" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>Delete Notification</h3>
                        <p>Are you sure want to delete?</p>
                    </div>
                    <form class="modal-btn delete-action" action="{{ route('notification.delete') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" class="d_id">
                        @method('DELETE')
                        <div class="row">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary continue-btn w-100">Delete</button>
                            </div>
                            <div class="col-6">
                                <a href="javascript:void(0);" data-dismiss="modal"
                                    class="btn btn-primary cancel-btn">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete Holiday Modal -->

</div>
@endsection

@section('script')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    const currentDateTime = new Date()
        const previusTime =
            flatpickr("#datepicker", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                minDate: currentDateTime,
                defaultDate: currentDateTime // Optionally, set minimum date to today
            });

        flatpickr("#dateTimepicker", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: currentDateTime
            // Optionally, set minimum date to today
        });
</script>

{{-- update js --}}
<script>
    $(document).on('click', '.userUpdate', function() {
        var _this = $(this).parents('tr');
        $('#e_id').val(_this.find('.id').text());
        $('#title_edit').val(_this.find('.titleName').text());
        $('#message_edit').val(_this.find('.notificationMassage').text());
        $('#dateTimepicker').val(_this.find('.dateTi').text());


        $('#employee').val(_this.find('.emp_id').text()).change();

        // $('#holidayEndDate_edit').val(_this.find('.endDate').attr('date'));
    });

    $(document).ready(function() {
        $('.selectpicker').selectpicker();
    });
</script>
@endsection
@section('script')
<script>
    $('.init-select2-shift').select2({}).on('change', function() {
            $(this).next().next().next().css('display', 'block')
        })
</script>
@endsection
