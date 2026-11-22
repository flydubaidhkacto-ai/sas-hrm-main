@extends('layouts.master')
@section('title', "Payslips")
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Payslip Summary Report</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Payslip Summary Report</li>
                        </ul>
                    </div>
                    @php
                        $can = role_can('payslip.generate') || role_can('payslip.generate.self.super_department') || user()->admin_of_super_departments;
                    @endphp

                </div>
            </div>
            <!-- /Page Header -->

            <!-- Search Filter -->
            <form action="{{ route('payslip.summary') }}" method="get" class="mb-3">
                <div class="row filter-row">
                    @if(
                        role_can('payslip.show')
                        || role_can('payslip.self.show.super_department')
                        || user()->admin_of_super_departments
                    )
                        <div class="mt-2 col-sm-6 col-md-3">
                            <label for="">Super Department</label>
                            @if(
                                !role_can('payslip.show')
                                && !user()->admin_of_super_departments
                                && role_can('payslip.self.show.super_department')
                            )
                                <input type="text" value="{{ user()->department?->super_department?->name }}"
                                       class="form-control"
                                       readonly>
                                <input type="hidden" name="super_department"
                                       value="{{ user()->department?->super_department_id }}">
                                <script>
                                    window.selected_super_department = '{{ user()->department?->super_department_id }}';
                                </script>
                            @else
                                <select name="super_department" id="" class="form-control super-department-select2">
                                    @if(request()->super_department)
                                        <option value="{{ request()->super_department }}" selected>
                                            {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                                        </option>
                                    @endif
                                </select>
                            @endif
                        </div>
                        <div class="mt-2 col-sm-6 col-md-3">
                            <label for="">Department</label>
                            <select name="department" id="" class="form-control department-select2">
                                @if(request()->department)
                                    <option value="{{ request()->department }}" selected>
                                        {{ \App\Models\Department::find(request()->department)?->name }}
                                    </option>
                                @endif
                            </select>
                        </div>
                        <div class="mt-2 col-sm-6 col-md-3">
                            <label for="">Designation</label>
                            <select name="designation" id="" class="form-control designation-select2">
                                @if(request()->designation)
                                    <option value="{{ request()->designation }}" selected>
                                        {{ \App\Models\Designation::find(request()->designation)?->name }}
                                    </option>
                                @endif
                            </select>
                        </div>
                        <div class="mt-2 col-sm-6 col-md-3">
                            <label for="">Employee</label>
                            <select name="employee" id="" class="form-control employee-select2">
                                @if(request()->employee)
                                    <option value="{{ request()->employee }}" selected>
                                        {{ \App\Models\User::find(request()->employee)?->name }}
                                    </option>
                                @endif
                            </select>
                        </div>
                    @endif
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Month</label>
                        <input name="month" type="text" class="form-control month-year-picker"
                               value="{{ request()->month }}">
                    </div>

                        <div class="mt-2 col-sm-6 col-md-2">
                            <label for="">Status</label>
                            <select name="status" class="form-control">
                                <option value="all">All</option>
                                <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Approved</option>
                                <option value="done" {{ request('status')=='done' ? 'selected' : '' }}>Done</option>
                                <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending
                                </option>
                            </select>
                        </div>

                    <div class="mt-2 col-sm-6 col-md-3">
                        <label>Payment Method</label>
                        <select name="payment_method" class="form-control">
                            <option value="">All</option>
                            <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Bank
                            </option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash
                            </option>
                        </select>
                    </div>


                        <div class="mt-2 col-sm-6 col-md-3">

                            <label>&nbsp;</label>

                            <div class="report-toolbar">

                                <button type="button"
                                        class="btn btn-danger btn-report btn-pdf"
                                        onclick="exportPdf()"
                                        title="Export PDF">
                                    <i class="fa fa-file-pdf-o"></i>
                                    <span>PDF</span>
                                </button>

                                <button type="button"
                                        class="btn btn-dark btn-report btn-print"
                                        onclick="exportPrint()"
                                        title="Print Report">
                                    <i class="fa fa-print"></i>
                                    <span>Print</span>
                                </button>

                            </div>

                        </div>

                </div>
            </form>
            <!-- Search Filter -->
            {{-- message --}}
            {!! Toastr::message() !!}


            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        @include('payslip.summary.index-table', [
                        'payslips' => $payslips
                        ])
                        <div class="mt-2">
                            {{ $payslips->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Content -->
    </div>
    <!-- /Page Wrapper -->

    {{-- modal for payment --}}
    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Payroll Payment</h5>
                    <button type="button" class="close" onclick="$('#paymentModal').modal('hide')" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="paymentModalBody">
                        <div>
                            <b>Details:</b>
                        </div>
                        <table class="table w-100 mt-2">
                            <tbody>
                            <tr>
                                <td style="width: 150px;">Employee</td>
                                <td style="width: 10px;">:</td>
                                <td id="employee_name"></td>
                            </tr>
                            <tr>
                                <td style="width: 150px;">Department</td>
                                <td style="width: 10px;">:</td>
                                <td id="department"></td>
                            </tr>
                            <tr>
                                <td style="width: 150px;">Designation</td>
                                <td style="width: 10px;">:</td>
                                <td id="designation"></td>
                            </tr>
                            <tr>
                                <td style="width: 150px;">Month</td>
                                <td style="width: 10px;">:</td>
                                <td id="month"></td>
                            </tr>
                            <tr>
                                <td style="width: 150px;">Previous Balance</td>
                                <td style="width: 10px;">:</td>
                                <td id="previous_balance"></td>
                            </tr>
                            <tr>
                                <td style="width: 150px;">Current Balance</td>
                                <td style="width: 10px;">:</td>
                                <td id="current_balance"></td>
                            </tr>
                            <tr>
                                <td style="width: 150px;">Net Salary</td>
                                <td style="width: 10px;">:</td>
                                <td id="net_salary"></td>
                            </tr>
                            <tr>
                                <td style="width: 150px;">Paying Amount</td>
                                <td style="width: 10px;">:</td>
                                <td id="amount">
                                    <input type="number" class="form-control" id="paying_amount">
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" id="pay" onclick="pay()">
                                        <i class="fa fa-check"></i>
                                        <span id="pay-text">Save Payment</span>
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="$('#paymentModal').modal('hide')"
                            data-dismiss="modal">Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        let selectedPayslip = null
        let selectedPayButton = null
        let selectedEditPayment = null

        function startPayment(btn, edit_id = null) {
            $('#paymentModal').modal('show');
            selectedEditPayment = edit_id
            let data = JSON.parse(btn.getAttribute('data'));
            selectedPayButton = btn;
            selectedPayslip = data;

            $('#employee_name').text(data.employee.name);
            $('#department').text(
                data.employee.department?.super_department?.name + ' -> ' + data.employee.department?.name
            );
            $('#designation').text(data.employee.designation?.name);
            $('#month').text(moment(data.month).format('MMMM YYYY'));
            let previousBalance = Number(data.employee.payroll_balance) - Number(data.total_salary);
            $('#previous_balance').text(
                Number(data.employee.payroll_balance) == 0 ? 0 : previousBalance
            );
            $('#current_balance').text(data.employee.payroll_balance);
            $('#net_salary').text(data.total_salary);
            $('#paying_amount').val(
                edit_id ? btn.getAttribute('amount') : data.employee.payroll_balance
            ) //data.total_salary);
        }

        function pay() {
            let amount = $('#paying_amount').val();
            if (Number(amount) === NaN || !selectedPayslip) {
                alert('Paying amount is not a number');
                return;
            }
            $('#paymentModal #pay').prop('disabled', true);
            $('#paymentModal #pay-text').text('Saving...');
            $.post('{{ route('payslip-payment.pay') }}', {
                _token: '{{ csrf_token() }}',
                amount: amount,
                payslip_id: selectedPayslip.id,
                edit_payment: selectedEditPayment,
            }, function (data) {
                $('#paymentModal').modal('hide');
                let editBtn = $(selectedPayButton).clone()
                editBtn.removeClass('btn-success')
                editBtn.attr('amount', amount)
                editBtn.attr('onclick', `startPayment(this, ${data.id})`)
                editBtn.html('<i class="fa fa-pencil text-info"></i>')
                selectedPayButton.parentElement.innerHTML = amount + ' Tk <i class="fa fa-check text-success"></i>' + editBtn.get(0).outerHTML;
                $('#paymentModal #pay').prop('disabled', false);
                $('#paymentModal #pay-text').text('Save Payment');
                toastr.success(data.message);
            }).fail(function (data) {
                $('#paymentModal #pay').prop('disabled', false);
                $('#paymentModal #pay-text').text('Save Payment');
                toastr.error(data.responseJSON.message);
            });
        }


        $(document).ready(function () {
            let table = new DataTable('.data-table', {
                pageLength: 50,
            });
        });
    </script>
@endsection
