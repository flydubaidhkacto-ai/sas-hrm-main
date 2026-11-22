@php
    $info = company_data();
@endphp
<table
    class="table table-striped custom-table data-table"
>

    @php
        $column_count = 9 - 1;
    @endphp
    <thead>
        <tr>
            <th>S.L</th>
            <th>Code</th>
            <th>Name</th>
            <th>Designation</th>
            {{-- <th>Month</th> --}}
            <th>Pay Type</th>
            @foreach (\App\Models\EarnHead::get() as $earnHead)
                <th>
                    {{ $earnHead->name }}
                </th>
                @php
                    $column_count++;
                @endphp
            @endforeach
            @foreach (\App\Models\Deduction::all() as $deduction)
                <th>
                    {{ $deduction->name }}
                </th>
                @php
                    $column_count++;
                @endphp
            @endforeach
            <th>Gross Salary</th>
            <th>
                Net Salary
            </th>
            @if (config('app.customer') === 'ssbd')
                <th>
                    Total Att.
                </th>
                <th>
                    Total Absent
                </th>
            @endif
            @if(has_module('payslip-payment'))
                <th class="text-right no-sort">Payment</th>
                <th class="text-right no-sort">
                    Curr. Balance
                </th>
            @endif
            <th class="text-right no-sort">Action</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total_gross_salary = 0;
            $total_net_salary = 0;
            $earnTotals = [];
            $deductTotals = [];
            $totalPayrollBalance = 0;
            $totalPayment = 0;
        @endphp
        @foreach ($payslips as $i => $items )
            <tr>
                <td>
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $items->employee?->id }}
                </td>

                <td>
                    <h2 class="table-avatar">
                        <a href="{{ route('employees.active.show', $items->employee) }}">
                            {{ $items->employee?->name }}
                        </a>
                    </h2>
                </td>
                <td>{{ $items->employee?->designation?->name }}</td>
                {{-- <td>
                    {{ carbon($items->month_date)->format('F, Y') }}
                </td> --}}
                <td>
                    {{ ucfirst($items->pay_type) }}
                </td>
                @foreach (\App\Models\EarnHead::get() as $index => $earnHead)
                    @php
                        $found = false;
                        $dutyBonusHead = null;
                    @endphp
                    @foreach ($items->earn_heads as $data)
                        @if ($data['name'] == $earnHead->name)
                            @php
                                $found = true;
                                $earnTotals[$i][$index] = $data['total_amount'] ?? 0;
                            @endphp
                            @if (in_array($earnHead->category, ['basic', 'overtime', 'duty_bonuses']) && config('app.customer') === 'ssbd')
                                <td>
                                    <input type="number" step="0.01" value="{{ $data['total_amount'] ?? 0 }}"
                                    oninput="updateSSBDField('{{ $earnHead->category }}', this, {{ $items->id }})"
                                    style="width: 110px" class="form-control"
                                    >
                                </td>
                            @else
                                <td>
                                    {{ $data['total_amount'] ?? 0 }}
                                </td>
                            @endif
                        @endif
                    @endforeach
                    @if (!$found)
                        <td>
                            @if (in_array($earnHead->category, ['basic', 'overtime', 'duty_bonuses']) && config('app.customer') === 'ssbd')
                                <input type="number" step="0.01" value="0"
                                oninput="updateSSBDField('{{ $earnHead->category }}', this, {{ $items->id }}, '{{ $earnHead->code }}', '{{ $earnHead->name }}')"
                                style="width: 110px" class="form-control"
                                >--
                            @else
                                -
                            @endif
                        </td>
                    @endif
                @endforeach
                @foreach (\App\Models\Deduction::all() as $index => $deduction)
                    @php
                        $found = false;
                    @endphp
                    @foreach ($items->deductions as $data)
                        @if ($data['code'] == $deduction->code)
                            @php
                                $found = true;
                                $deductTotals[$i][$index] = $data['total_amount'] ?? 0;
                            @endphp
                            <td>
                                {{ $data['total_amount'] ?? 0 }}
                            </td>
                        @else
                        @endif
                    @endforeach
                    @if (!$found)
                        <td>-</td>
                    @endif
                @endforeach
                <td>{{ $items->employee?->payroll_card?->gross_salary }}</td>
                <td class="total-salary" id="payslip_total_{{ $items->id }}">
                    {{ number_format($items->total_salary, 2) }}
                </td>
                @if (config('app.customer') === 'ssbd')
                    <td>
                        <input type="number" step="1" value="{{ $items->ssbd_total_attendance }}"
                            oninput="updateSSBDField('ssbd_total_attendance', this, '{{ $items->id }}')"
                            style="width: 80px" class="form-control"
                        >
                    </td>
                    <td>
                        <input type="number" step="1" value="{{ $items->ssbd_total_absent }}"
                            oninput="updateSSBDField('ssbd_total_absent', this, '{{ $items->id }}')"
                            style="width: 80px" class="form-control"
                        >
                    </td>
                @endif
                @if(has_module('payslip-payment'))
                <td class="text-right">
                    @php
                        $payment = $items->payment();
                        $totalPayment += $payment?->paid_amount ?: 0;
                    @endphp
                    @if($payment)
                        {{ $payment->paid_amount }}
                        <button class="ml-2 btn btn-sm" onclick="startPayment(this, {{ $payment->id }})"
                            data="{{ json_encode($items) }}"
                            id="paybtn_{{ $items->id }}"
                            amount="{{ $payment->paid_amount }}"
                        >
                            <i class="fa fa-pencil text-info"></i>
                        </button>
                    @else
                        <div style="width: 75px;">
                            <button class="ml-2 btn btn-success btn-sm" onclick="startPayment(this)"
                                data="{{ json_encode($items) }}"
                                id="paybtn_{{ $items->id }}"
                            >
                                <i class="fa fa-money-bill"></i>
                                Pay
                            </button>
                        </div>
                    @endif
                </td>
                <td class="text-right">
                    @php
                        $totalPayrollBalance += $items->employee?->payroll_balance ?: 0;
                    @endphp
                    {{  $items->employee?->payroll_balance }}
                </td>
                @endif

                <td class="text-right">
                    <div style="width: 75px;">
                        <a href="{{ route('payslip.show', $items->id) }}" class="btn btn-info btn-sm">
                            <i class="fa fa-eye"></i>
                            Open
                        </a>
                    </div>
                </td>
                @php
                    $total_gross_salary += $items->employee?->payroll_card?->gross_salary;
                    $total_net_salary += $items->total_salary;
                @endphp
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="5" style="text-align: right">
                <b>Total:</b>
            </td>
            @foreach (\App\Models\EarnHead::get() as $index => $earnHead)
                @php
                    $sum = 0;
                    foreach ($payslips as $i => $p) {
                        $sum += ($earnTotals[$i][$index] ?? 0);
                    }
                @endphp
                <td>
                    {{ number_format($sum, 2) }}
                </td>
            @endforeach
            @foreach (\App\Models\Deduction::all() as $index => $deduction)
                @php
                    $sum = 0;
                    foreach ($payslips as $i => $p) {
                        $sum += ($deductTotals[$i][$index] ?? 0);
                    }
                @endphp
                <td>
                    {{ number_format($sum, 2) }}
                </td>
            @endforeach
            <td id="totalGrossSalary">
                {{ number_format($total_gross_salary, 2) }}
            </td>
            <td id="totalNetSalary">
                {{ number_format($total_net_salary, 2) }}
            </td>
            @if(has_module('payslip-payment'))
            <td class="text-right">{{ number_format($totalPayment, 2) }}</td>
            <td class="text-right">{{ number_format($totalPayrollBalance, 2) }}</td>
            @endif
            <td></td>
        </tr>
    </tfoot>
</table>
