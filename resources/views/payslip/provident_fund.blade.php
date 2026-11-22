@extends('layouts.master')
@section('title', "Provident Fund Balance")
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
                        Provident Fund Balance
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Provident Fund Balance</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        {{-- message --}}
        {!! Toastr::message() !!}
        @if($provident_funds ?? null)
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="miantable table table-striped custom-table">
                        <thead>
                            <tr>
                                <th>S.L</th>
                                <th>Code</th>
                                <th>Emp. ID</th>
                                <th>Name</th>
                                <th>Sup. Department</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Fund Balance</th>
                                <th>Interest Rate</th>
                                <th>Fund Interest</th>
                                <th>Balance with Interest</th>
                                <th>Available Balance</th>
                                @if(role_can('provident_fund_payment'))
                                <th>Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($provident_funds as $index => $item)
                                @php
                                    $totalBalance = $item['pf']->balance_with_interest ?? 0;
                                    $paidAmount = \App\Models\ProvidentFundPaymentRecord::where('user_id', $item['employee']->id)->sum('paid_amount');
                                    $availableBalance = $totalBalance - $paidAmount;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item['employee']->id }}</td>
                                    <td>{{ $item['employee']->employee_id }}</td>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="{{ route('employees.active.show', $item['employee']->id) }}">
                                                {{ $item['employee']->name }}
                                            </a>
                                        </h2>
                                    </td>
                                    <td>{{ $item['employee']->department?->super_department?->name }}</td>
                                    <td>{{ $item['employee']->department?->name }}</td>
                                    <td>{{ $item['employee']->designation?->name }}</td>
                                    <td>{{ $item['pf']->balance ?? 'N/A' }}</td>
                                    <td>{{ $item['pf']->interest ?? 'N/A' }}</td>
                                    <td>{{ $item['pf']->interest_amount ?? 'N/A' }}</td>
                                    <td>{{ $item['pf']->balance_with_interest ?? 'N/A' }}</td>
                                    <td>
                                        <span class="{{ $availableBalance > 0 ? 'text-success' : 'text-muted' }}">
                                            {{ number_format($availableBalance, 2) }} Tk
                                        </span>
                                    </td>
                                    @if(role_can('provident_fund_payment'))
                                    <td>
                                        @if($availableBalance > 0)
                                            <button type="button" class="btn btn-primary btn-sm"
                                                    onclick="openPaymentModal({{ $item['employee']->id }}, '{{ $item['employee']->name }}', {{ $availableBalance }})">
                                                <i class="fa fa-money"></i> Pay
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-secondary btn-sm" disabled>
                                                <i class="fa fa-ban"></i> No Balance
                                            </button>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->

{{-- Modal for Provident Fund Payment --}}
<div class="modal fade" id="pfPaymentModal" tabindex="-1" role="dialog" aria-labelledby="pfPaymentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pfPaymentModalLabel">Provident Fund Payment</h5>
                <button type="button" class="close" onclick="$('#pfPaymentModal').modal('hide')"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="pfPaymentModalBody">
                    <div>
                        <b>Payment Details:</b>
                    </div>
                    <table class="table w-100 mt-2">
                        <tbody>
                            <tr>
                                <td style="width: 150px;">Employee</td>
                                <td style="width: 10px;">:</td>
                                <td id="pf_employee_name"></td>
                            </tr>
                            <tr>
                                <td style="width: 150px;">Available Balance</td>
                                <td style="width: 10px;">:</td>
                                <td id="pf_available_balance"></td>
                            </tr>
                            <tr>
                                <td style="width: 150px;">Paying Amount</td>
                                <td style="width: 10px;">:</td>
                                <td>
                                    <input type="number" class="form-control" id="pf_paying_amount"
                                           placeholder="Enter amount to pay" step="0.01" min="0">
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 150px;">Note</td>
                                <td style="width: 10px;">:</td>
                                <td>
                                    <textarea class="form-control" id="pf_note" rows="3"
                                              placeholder="Optional payment note"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td >
                                    <button type="button" class="btn btn-primary btn-sm" id="pf_pay_btn" onclick="payProvidentFund()">
                                        <i class="fa fa-check"></i>
                                        <span id="pf_pay_text">Save Payment</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    onclick="$('#pfPaymentModal').modal('hide')"
                    data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
{{-- datatable --}}
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let selectedUserId = null;

    function openPaymentModal(userId, employeeName, availableBalance) {
        selectedUserId = userId;
        $('#pf_employee_name').text(employeeName);
        $('#pf_available_balance').text(availableBalance.toFixed(2) + ' Tk');
        $('#pf_paying_amount').val('');
        $('#pf_paying_amount').attr('max', availableBalance);
        $('#pf_note').val('');
        $('#pfPaymentModal').modal('show');
    }

    function payProvidentFund() {
        let amount = $('#pf_paying_amount').val();
        let note = $('#pf_note').val();

        if (!amount || parseFloat(amount) <= 0) {
            toastr.error('Please enter a valid payment amount');
            return;
        }

        if (!selectedUserId) {
            toastr.error('No employee selected');
            return;
        }

        $('#pf_pay_btn').prop('disabled', true);
        $('#pf_pay_text').text('Processing...');

        $.post('{{ route('pf-payment.pay') }}', {
            _token: '{{ csrf_token() }}',
            amount: amount,
            user_id: selectedUserId,
            note: note,
        }, function(data) {
            $('#pfPaymentModal').modal('hide');
            toastr.success(data.message);
            // Refresh the page to show updated balance
            window.location.reload();
        }).fail(function(xhr) {
            $('#pf_pay_btn').prop('disabled', false);
            $('#pf_pay_text').text('Save Payment');
            if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error('An error occurred while processing the payment');
            }
        });
    }

    $(document).ready(function() {
        let table = new DataTable('.miantable', {
            pageLength: 50
        });
    });
</script>
@endsection
