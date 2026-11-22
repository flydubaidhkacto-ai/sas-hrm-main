@extends('layouts.master')
@section('title', "Interview Employee list")
@section('content')
@include('employees.terminate-modal')
@include('employees.activate-modal')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Interview Employee</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Interview Employee</li>
                    </ul>
                </div>
                @if(role_can('user') || role_can('user.self.super_department'))
                <div class="col-auto float-right ml-auto">
                    <a href="{{ route('employees.interview.create') }}" class="btn add-btn">
                        <i class="fa fa-plus"></i>
                        Add New
                    </a>
                </div>
                @endif

            </div>
        </div>
        <!-- /Page Header -->


        {{-- message --}}
        {!! Toastr::message() !!}
        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
            {{ $error }}
            @endforeach
        </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                {{-- <div class="exports">
                    @include('layouts.exports')
                </div> --}}
                <div class="exports">
                    <div style="text-align: end" class="mb-2">
                        <a href="{{ add_query_to_current_route(['export' => 'xlsx']) }}" class="btn btn-sm btn-info"
                            target="_blank">
                            <i class="fa fa-file-excel"></i>
                            Excel
                        </a>
                        <a href="{{ add_query_to_current_route(['export' => 'pdf']) }}" class="btn btn-sm btn-info"
                            target="_blank">
                            <i class="fa fa-file-pdf"></i>
                            PDF
                        </a>

                        <a href="{{ add_query_to_current_route(['export' => 'print']) }}" class="btn btn-sm btn-info"
                            target="_blank">
                            <i class="fa fa-print"></i>
                            Print
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    @include('employees_interview.list-table', [
                    'employees' => $employees
                    ])
                    {{-- <div class="mt-2">
                        {{ $employees->withQueryString()->links() }}
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->

</div>
<!-- /Page Wrapper -->

{{-- create a modal for active-employee employee --}}
<div class="modal custom-modal fade" id="active-employee-modal" tabindex="-1" role="dialog"
    aria-labelledby="active-employee-modal-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="active-employee-modal-label">Active Employee</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <b>Name: </b>
                    <span id="active-employee-modal-name"></span>
                    <br>
                    <b>Designation: </b>
                    <span id="active-employee-modal-designation"></span>
                    <br>
                    <b>Department: </b>
                    <span id="active-employee-modal-department"></span>
                    <br>
                    <b>Super Department: </b>
                    <span id="active-employee-modal-super-department"></span>
                </div>
                <form action="{{ route('employees.interview.active') }}" method="post" id="active-employee-form"
                    class="mt-3">
                    @csrf
                     <input type="hidden" name="interview_employee_id" id="active-employee-modal-employee-id">
                     <div class="form-group">
                         <label for="active-employee-id" class="w-100">
                             Employee ID: &nbsp;
                         </label>
                         <input type="text" class="form-control" id="active-employee-id" required name="employee_id"
                             value="{{ old('employee_id') }}">
                         <span class="active-employee-id-availability text-danger" style="display:none;"></span>
                     </div>
                     <div class="form-group">
                         <label for="join_date" class="w-100">
                             Joining Date: &nbsp;
                         </label>
                         <input type="text" class="form-control datetimepicker" type="text" id="join_date" required
                             name="join_date" value="{{ old('join_date') }}">
                     </div>

                     <button type="submit" class="btn btn-success mt-3">Move to Active Employee</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- create a modal for Probation employee --}}
<div class="modal custom-modal fade" id="probation-employee-modal" tabindex="-1" role="dialog"
    aria-labelledby="probation-employee-modal-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="probation-employee-modal-label">Probation Employee</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <b>Name: </b>
                    <span id="probation-employee-modal-name"></span>
                    <br>
                    <b>Designation: </b>
                    <span id="probation-employee-modal-designation"></span>
                    <br>
                    <b>Department: </b>
                    <span id="probation-employee-modal-department"></span>
                    <br>
                    <b>Super Department: </b>
                    <span id="probation-employee-modal-super-department"></span>
                </div>
                <form action="{{ route('employees.interview.probation') }}" method="post" id="probation-employee-form"
                    class="mt-3">
                    @csrf
                     <input type="hidden" name="interview_employee_id" id="probation-employee-modal-employee-id">
                     <div class="form-group">
                         <label for="probation-employee-id" class="w-100">
                             Employee ID: &nbsp;
                         </label>
                         <input type="text" class="form-control" id="probation-employee-id" required name="employee_id"
                             value="{{ old('employee_id') }}">
                         <span class="probation-employee-id-availability text-danger" style="display:none;"></span>
                     </div>

                     <div class="form-group">
                        <label for="join_date" class="w-100">
                            Joining Date: &nbsp;
                        </label>
                        <input type="text" class="form-control datetimepicker" type="text" id="join_date" required
                            name="join_date" value="{{ old('join_date') }}">
                    </div>

                    <div class="form-group">
                        <label for="probation_end_date" class="w-100">
                            Probation End Date: &nbsp;
                        </label>
                        <input type="text" class="form-control datetimepicker" type="text" id="probation_end_date"
                            required name="probation_end_date" value="{{ old('probation_end_date') }}">
                    </div>

                    <button type="submit" class="btn btn-success mt-3">Move to Probation Employee</button>
                </form>
            </div>
        </div>
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
    $('.init-select2-shift').select2({}).on('change', function () {
            $(this).next().next().next().css('display', 'block')
        })
</script>

<script>
    function openProbationEmployeeModal(employeeId, name, designation, department, super_department) {
        $('#probation-employee-modal').modal('show');
        $('#probation-employee-modal-employee-id').val(employeeId);
        $('#probation-employee-modal-name').text(name);
        $('#probation-employee-modal-designation').text(designation);
        $('#probation-employee-modal-department').text(department);
        $('#probation-employee-modal-super-department').text(super_department);
    }
    $('.close').on('click', function () {
        $('#probation-employee-modal').modal('hide');
    });
</script>

<script>
    function openActiveEmployeeModal(employeeId, name, designation, department, super_department) {
        $('#active-employee-modal').modal('show');
        $('#active-employee-modal-employee-id').val(employeeId);
        $('#active-employee-modal-name').text(name);
        $('#active-employee-modal-designation').text(designation);
        $('#active-employee-modal-department').text(department);
        $('#active-employee-modal-super-department').text(super_department);
    }
    function validateInterviewEmployeeId(modalPrefix) {
        const employeeIdField = $('#' + modalPrefix + '-id');
        const availabilityMessage = $('.' + modalPrefix + '-id-availability');
        const employeeId = employeeIdField.val().trim();

        if (employeeId === '') {
            availabilityMessage.hide();
            employeeIdField.removeClass('is-invalid');
            return true;
        }

        $.ajax({
            url: '{{ route('employees.ajax.check.employeeId') }}',
            method: 'GET',
            data: { employee_id: employeeId },
            success: function(response) {
                if (response.available) {
                    availabilityMessage
                        .removeClass('text-danger')
                        .addClass('text-success')
                        .text('Employee ID is available')
                        .show();
                    employeeIdField.removeClass('is-invalid');
                } else {
                    availabilityMessage
                        .removeClass('text-success')
                        .addClass('text-danger')
                        .text(response.message || 'The Employee ID is not available')
                        .show();
                    employeeIdField.addClass('is-invalid');
                }
            },
            error: function() {
                availabilityMessage
                    .removeClass('text-success')
                    .addClass('text-danger')
                    .text('Unable to verify Employee ID availability')
                    .show();
            },
        });
    }

    $('#active-employee-id').on('blur', function() {
        validateInterviewEmployeeId('active-employee');
    });

    $('#probation-employee-id').on('blur', function() {
        validateInterviewEmployeeId('probation-employee');
    });

    $('.close').on('click', function () {
        $('#active-employee-modal').modal('hide');
    });

</script>
@endsection