@extends('layouts.master')
@section('title', 'Apply Loan')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Apply Loan
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('loans') }}">Loan</a></li>
                        <li class="breadcrumb-item active">Apply</li>
                    </ul>
                </div>
            </div>
        </div>
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <form action="{{ route('loans.apply') }}" method="POST">
            @csrf
            <div class="">
                @if (role_can('loans') || user()->admin_of_super_departments)
                <div class="row">
                    <div class="col-sm-6 col-md-3">
                        <label for="">Super Department</label>
                        <select name="super_department" id="" class="form-control super-department-select2">
                            @if (request()->super_department)
                            <option value="{{ request()->super_department }}" selected>
                                {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label for="">Department</label>
                        <select name="department" id="" class="form-control department-select2">
                            @if (request()->department)
                            <option value="{{ request()->department }}" selected>
                                {{ \App\Models\Department::find(request()->department)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label for="">Designation</label>
                        <select name="designation" id="" class="form-control designation-select2">
                            @if (request()->designation)
                            <option value="{{ request()->designation }}" selected>
                                {{ \App\Models\Designation::find(request()->designation)?->name }}
                            </option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="mb-2 mt-2">
                    <label for="">Select Employee:</label>
                    <select name="employee" id="" class="form-control employee-select2" required>
                        @if (request()->employee)
                        <option value="{{ request()->employee }}" selected>
                            {{ \App\Models\User::find(request()->employee)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                @elseif(\App\Models\ApprovalProcess::canApproveEmployees('loan', user()))
                <div class="mb-2 mt-2">
                    <label for="">Select Employee:</label>
                    <select name="employee" id="" class="form-control init-select2" required>
                        <option value="">Select an employee</option>
                        <option value="{{ user()->id }}" @if(request()->employee == user()->id) selected @endif>{{
                            user()->name }}</option>
                        @foreach (\App\Models\ApprovalProcess::canApproveEmployees('loan', user())['employees'] as  $employee)
                        <option value="{{ $employee->id }}" @if(request()->employee == $employee->id) selected @endif
                            >
                            {{ $employee->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @else
                <select name="employee" id="" class="form-control d-none" required>
                    <option value="{{ user()->id }}" selected>
                        {{ user()->name }}
                    </option>
                </select>
                @endif
                <div>
                    <div class="row">
                        <div class="col-12">
                            <label for="">Reason:</label>
                            <input type="text" class="form-control" name="reason" value="{{ old('reason') }}">
                        </div>
                        <div class="mt-2 col-sm-6 col-md-3">
                            <label for="">Amount:</label>
                            <input type="number" class="form-control" name="amount" required
                                value="{{ old('amount') }}">
                        </div>
                        <div class="mt-2 col-sm-6 col-md-3">
                            <label for="">Pay as:</label>
                            <select name="cut_off_as" id="" class="form-control">
                                <option value="once" selected>At once</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="mt-2 col-sm-6 col-md-3" id="tenure-in-months">
                            <label for="">Tenure in months:</label>
                            <input name="tenure_in_months" type="number" class="form-control"
                                value="{{ old('tenure_in_months') }}">
                        </div>
                        <div class="mt-2 col-sm-6 col-md-3">
                            <label for="">Issue Date:</label>
                            <input name="date" type="date" class="form-control mb-2" required value="{{ old('date') }}">
                            <span class="text-danger d-none mt-4 fs-3" id="show_end_date"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Submit</button>

                <button type="button" class="btn btn-link text-blue float-right" onclick="previewPaymentSchedule()">
                    <i class="fa fa-eye"></i>
                    Preview Payment Schedule
                </button>
            </div>
        </form>
    </div>

    <div class="modal fade" id="payment-schedule-modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Schedule</h5>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Amount</th>
                                    <th>Principal</th>
                                    <th>Interest</th>
                                    <th>Remaining</th>
                                </tr>
                            </thead>
                            <tbody id="payment-schedule-body">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $(document).ready(function() {
            function toggleTenureField() {
                if ($('select[name="cut_off_as"]').val() === 'monthly') {
                    $('#tenure-in-months').show();
                } else {
                    $('#tenure-in-months').hide();
                    $('input[name="tenure_in_months"]').val('')
                }
            }

            function formatDate(dateObj) {
                let day = dateObj.getDate().toString().padStart(2, '0');
                let month = (dateObj.getMonth() + 1).toString().padStart(2, '0');
                let year = dateObj.getFullYear();
                return `${day}-${month}-${year}`;
            }

            function calculateEndDate() {
                let issueDate = $('input[name="date"]').val();
                let tenureMonths = parseInt($('input[name="tenure_in_months"]').val());
                let selectedOption = $('select[name="cut_off_as"]').val();

                if (issueDate) {
                    let dateObj = new Date(issueDate);

                    if (selectedOption === 'monthly' && tenureMonths) {
                        dateObj.setMonth(dateObj.getMonth() + tenureMonths);
                    } else if (selectedOption === 'once') {
                        dateObj.setMonth(dateObj.getMonth() + 1);
                    } else {
                        $('#show_end_date').addClass('d-none').text('');
                        return;
                    }

                    let formattedEndDate = formatDate(dateObj);
                    $('#show_end_date').removeClass('d-none').text('End Date: ' + formattedEndDate);
                } else {
                    $('#show_end_date').addClass('d-none').text('');
                }
            }

            toggleTenureField();

            $('select[name="cut_off_as"]').on('change', function() {
                toggleTenureField();
                calculateEndDate();
            });

            $('input[name="date"], input[name="tenure_in_months"]').on('input', function() {
                calculateEndDate();
            });
        });

        function previewPaymentSchedule() {
            let amount = $('input[name="amount"]').val();
            let tenure_in_months = $('input[name="tenure_in_months"]').val();
            if (!amount || !tenure_in_months) {
                return toastr.error('Please enter amount and tenure in months');
            }
            const modal = $('#payment-schedule-modal');
            modal.find('#payment-schedule-body').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
            $.ajax({
                url: "{{ route('loans.preview-payment-schedule') }}",
                type: "GET",
                data: {
                    amount: amount,
                    tenure_in_months: tenure_in_months,
                },
                success: function(response) {
                    let html = '';
                    modal.modal('show');
                    response.emi_schedule.forEach(item => {
                        html += `<tr>
                        <td>${item.month}</td>
                        <td>${item.emi}</td>
                        <td>${item.principal_paid}</td>
                        <td>${item.interest_paid}</td>
                        <td>${item.remaining_balance}</td>
                    </tr>`;
                    });
                    modal.find('#payment-schedule-body').html(html);
                },
                error: function(xhr, status, error) {
                    toastr.error(xhr.responseText);
                    modal.modal('hide');
                }
            });
        }
</script>

@endSection
