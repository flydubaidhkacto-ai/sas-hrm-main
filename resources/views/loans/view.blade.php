<div>
    <b>Amount: </b> {{ $loan->amount }} <br>
    <b>Pay Term: </b> {{ $loan->cut_off_as }} <br>
    <b>Created Date: </b> {{ $loan->created_at->format('d M, Y') }} <br>
    <b>Issue Date: </b> {{ carbon($loan->issue_date)->format('d M, Y') }} <br>
    @if ($loan->cut_off_as == 'monthly')
        <b>Monthly Interest: </b> {{ \App\Models\Setting::loan_interest_rate() }}% <br>
        <b>Tenure: </b> {{ $loan->tenure_in_months }} months <br>
        <b>Total Interest: </b> {{ $loan->paymentSchedules()->sum('interest') }} <br>
        <b>Total Amount: </b> {{ $loan->paymentSchedules()->sum('amount') }} <br>
        <table class="table mt-2 table-bordered">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Amount</th>
                    <th>Principal</th>
                    <th>Interest</th>
                    <th>Remaining</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($loan->paymentSchedules as $schedule)
                    <tr>
                        <td>{{ $schedule->month }}</td>
                        <td>{{ $schedule->amount }}</td>
                        <td>{{ $schedule->principal }}</td>
                        <td>{{ $schedule->interest }}</td>
                        <td>{{ $schedule->remaining }}</td>
                        <td>
                            {{-- @dump($schedule->loan_payment_id) --}}
                            @if ($schedule->loanPayment)
                                <span class="badge badge-success">Paid</span>
                            @else
                                <span class="badge badge-danger">Unpaid</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
<div class="p-3">

    @include('layouts.approval', [
        'model' => $loan,
        'model_type' => 'loan',
        'employee' => $loan->employee,
        'have_form' => true,
    ])

</div>
