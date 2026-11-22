@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Salary Sheet of {{ carbon($month)->format('F, Y') }}
    </p>

    <style>
        table {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
            font-size: 8px;
        }

        table td, table th {
            border: 1px solid #ddd;
            padding: 2 4px;
        }

        table tr:nth-child(even){background-color: #f2f2f2;}

        table tr:hover {background-color: #ddd;}

        table th {
            /* padding-top: 10px;
            padding-bottom: 10px; */
            text-align: left;
            background-color: #643a86;
            color: white;
        }
        @media print {
            table th {
                background-color: unset;
                color: unset;
            }
        }
    </style>
@endif
<table
    @if($for_export ?? false)
        style="width: 100%" class="data-table"
    @else
        class="table table-striped custom-table data-table"
    @endif
>

    @php
        $column_count = 9 - (($for_export ?? false) ? 0 : 1);
        $grouped = [];
        foreach ($payslips as $payslip) {
            $super = $payslip->employee?->department?->super_department?->name ?? 'No Super Department';
            $dept = $payslip->employee?->department?->name ?? 'No Department';
            $grouped[$super][$dept][] = $payslip;
        }
        ksort($grouped);
        foreach ($grouped as $super => &$depts) {
            ksort($depts);
            foreach ($depts as $dept => &$items) {
                usort($items, function($a, $b) {
                    return strcasecmp($a->employee?->name ?? '', $b->employee?->name ?? '');
                });
            }
        }
        $unique_supers = array_keys($grouped);
        $only_one_super = count($unique_supers) === 1;
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
            @if($for_export ?? false)
                <th>
                    Remarks
                </th>
            @else
                <th class="text-right no-sort">Action</th>
            @endif
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
            $sl = 1;
            $counter = 0;
        @endphp
        @if(!$only_one_super)
            @foreach($grouped as $super => $depts)
                <tr>
                    <td colspan="{{ $column_count +1 }}" style="background-color: #e6e6fa; font-weight: bold; text-align: left; font-size: 10px;">{{ strtoupper($super) }}</td>
                </tr>
                @foreach($depts as $dept => $items)
                    <tr>
                        <td colspan="{{ $column_count +1 }}" style="background-color: #f0f8ff; font-weight: bold; text-align: left; font-size: 10px;">{{ strtoupper($dept) }}</td>
                    </tr>
                    @foreach($items as $payslip)
                        @php $i = $counter++; @endphp
                        <tr>
                            <td>{{ $sl++ }}</td>
                            <td>{{ $payslip->employee?->id }}</td>
                            <td>
                                @if($for_export ?? false)
                                    {{ $payslip->employee?->name }}
                                @else
                                    <h2 class="table-avatar">
                                        <a href="{{ route('employees.active.show', $payslip->employee) }}">
                                            {{ $payslip->employee?->name }}
                                        </a>
                                    </h2>
                                @endif
                            </td>
                            <td>{{ $payslip->employee?->designation?->name }}</td>
                            <td>{{ ucfirst($payslip->pay_type) }}</td>
                            @foreach (\App\Models\EarnHead::get() as $index => $earnHead)
                                @php
                                    $found = false;
                                    $dutyBonusHead = null;
                                @endphp
                                @foreach ($payslip->earn_heads as $data)
                                    @if ($data['name'] == $earnHead->name)
                                        @php
                                            $found = true;
                                            $earnTotals[$i][$index] = $data['total_amount'] ?? 0;
                                        @endphp
                                        @if (in_array($earnHead->category, ['basic', 'overtime', 'duty_bonuses']) && config('app.customer') === 'ssbd')
                                            <td>
                                                <input type="number" step="0.01" value="{{ $data['total_amount'] ?? 0 }}" oninput="updateSSBDField('{{ $earnHead->category }}', this, {{ $payslip->id }})" style="width: 110px" class="form-control">
                                            </td>
                                        @else
                                            <td>{{ $data['total_amount'] ?? 0 }}</td>
                                        @endif
                                    @endif
                                @endforeach
                                @if (!$found)
                                    <td>
                                        @if (in_array($earnHead->category, ['basic', 'overtime', 'duty_bonuses']) && config('app.customer') === 'ssbd')
                                            <input type="number" step="0.01" value="0" oninput="updateSSBDField('{{ $earnHead->category }}', this, {{ $payslip->id }}, '{{ $earnHead->code }}', '{{ $earnHead->name }}')" style="width: 110px" class="form-control">
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endif
                            @endforeach
                            @foreach (\App\Models\Deduction::all() as $index => $deduction)
                                @php $found = false; @endphp
                                @foreach ($payslip->deductions as $data)
                                    @if ($data['code'] == $deduction->code)
                                        @php
                                            $found = true;
                                            $deductTotals[$i][$index] = $data['total_amount'] ?? 0;
                                        @endphp
                                        <td>{{ $data['total_amount'] ?? 0 }}</td>
                                    @endif
                                @endforeach
                                @if (!$found)
                                    <td>-</td>
                                @endif
                            @endforeach
                            <td>{{ $payslip->employee?->payroll_card?->gross_salary }}</td>
                            <td class="total-salary" id="payslip_total_{{ $payslip->id }}">{{ number_format($payslip->total_salary, 2) }}</td>
                            @if (config('app.customer') === 'ssbd')
                                <td><input type="number" step="1" value="{{ $payslip->ssbd_total_attendance }}" oninput="updateSSBDField('ssbd_total_attendance', this, '{{ $payslip->id }}')" style="width: 80px" class="form-control"></td>
                                <td><input type="number" step="1" value="{{ $payslip->ssbd_total_absent }}" oninput="updateSSBDField('ssbd_total_absent', this, '{{ $payslip->id }}')" style="width: 80px" class="form-control"></td>
                            @endif
                            @if(has_module('payslip-payment'))
                            <td class="text-right">
                                @php $payment = $payslip->payment(); $totalPayment += $payment?->paid_amount ?: 0; @endphp
                                @if($payment)
                                    {{ $payment->paid_amount }}
                                    <button class="ml-2 btn btn-sm" onclick="startPayment(this, {{ $payment->id }})" data="{{ json_encode($payslip) }}" id="paybtn_{{ $payslip->id }}" amount="{{ $payment->paid_amount }}"><i class="fa fa-pencil text-info"></i></button>
                                @else
                                    @if($for_export ?? false)
                                        -
                                    @else
                                        <div style="width: 75px;"><button class="ml-2 btn btn-success btn-sm" onclick="startPayment(this)" data="{{ json_encode($payslip) }}" id="paybtn_{{ $payslip->id }}"><i class="fa fa-money-bill"></i> Pay</button></div>
                                    @endif
                                @endif
                            </td>
                            <td class="text-right">
                                @php $totalPayrollBalance += $payslip->employee?->payroll_balance ?: 0; @endphp
                                {{ $payslip->employee?->payroll_balance }}
                            </td>
                            @endif
                            @if($for_export ?? false)
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            @else
                                <td class="text-right">
                                    <div style="width: 75px;"><a href="{{ route('payslip.show', $payslip->id) }}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i> Open</a></div>
                                </td>
                            @endif
                            @php
                                $total_gross_salary += $payslip->employee?->payroll_card?->gross_salary;
                                $total_net_salary += $payslip->total_salary;
                            @endphp
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        @else
            @foreach($grouped as $super => $depts)
                @foreach($depts as $dept => $items)
                    <tr>
                        <td colspan="{{ $column_count }}" style="background-color: #f0f8ff; font-weight: bold; text-align: left; font-size: 10px;">{{ strtoupper($dept) }}</td>
                    </tr>
                    @foreach($items as $payslip)
                        @php $i = $counter++; @endphp
                        <tr>
                            <td>{{ $sl++ }}</td>
                            <td>{{ $payslip->employee?->id }}</td>
                            <td>
                                @if($for_export ?? false)
                                    {{ $payslip->employee?->name }}
                                @else
                                    <h2 class="table-avatar">
                                        <a href="{{ route('employees.active.show', $payslip->employee) }}">
                                            {{ $payslip->employee?->name }}
                                        </a>
                                    </h2>
                                @endif
                            </td>
                            <td>{{ $payslip->employee?->designation?->name }}</td>
                            <td>{{ ucfirst($payslip->pay_type) }}</td>
                            @foreach (\App\Models\EarnHead::get() as $index => $earnHead)
                                @php
                                    $found = false;
                                    $dutyBonusHead = null;
                                @endphp
                                @foreach ($payslip->earn_heads as $data)
                                    @if ($data['name'] == $earnHead->name)
                                        @php
                                            $found = true;
                                            $earnTotals[$i][$index] = $data['total_amount'] ?? 0;
                                        @endphp
                                        @if (in_array($earnHead->category, ['basic', 'overtime', 'duty_bonuses']) && config('app.customer') === 'ssbd')
                                            <td>
                                                <input type="number" step="0.01" value="{{ $data['total_amount'] ?? 0 }}" oninput="updateSSBDField('{{ $earnHead->category }}', this, {{ $payslip->id }})" style="width: 110px" class="form-control">
                                            </td>
                                        @else
                                            <td>{{ $data['total_amount'] ?? 0 }}</td>
                                        @endif
                                    @endif
                                @endforeach
                                @if (!$found)
                                    <td>
                                        @if (in_array($earnHead->category, ['basic', 'overtime', 'duty_bonuses']) && config('app.customer') === 'ssbd')
                                            <input type="number" step="0.01" value="0" oninput="updateSSBDField('{{ $earnHead->category }}', this, {{ $payslip->id }}, '{{ $earnHead->code }}', '{{ $earnHead->name }}')" style="width: 110px" class="form-control">
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endif
                            @endforeach
                            @foreach (\App\Models\Deduction::all() as $index => $deduction)
                                @php $found = false; @endphp
                                @foreach ($payslip->deductions as $data)
                                    @if ($data['code'] == $deduction->code)
                                        @php
                                            $found = true;
                                            $deductTotals[$i][$index] = $data['total_amount'] ?? 0;
                                        @endphp
                                        <td>{{ $data['total_amount'] ?? 0 }}</td>
                                    @endif
                                @endforeach
                                @if (!$found)
                                    <td>-</td>
                                @endif
                            @endforeach
                            <td>{{ $payslip->employee?->payroll_card?->gross_salary }}</td>
                            <td class="total-salary" id="payslip_total_{{ $payslip->id }}">{{ number_format($payslip->total_salary, 2) }}</td>
                            @if (config('app.customer') === 'ssbd')
                                <td><input type="number" step="1" value="{{ $payslip->ssbd_total_attendance }}" oninput="updateSSBDField('ssbd_total_attendance', this, '{{ $payslip->id }}')" style="width: 80px" class="form-control"></td>
                                <td><input type="number" step="1" value="{{ $payslip->ssbd_total_absent }}" oninput="updateSSBDField('ssbd_total_absent', this, '{{ $payslip->id }}')" style="width: 80px" class="form-control"></td>
                            @endif
                            @if(has_module('payslip-payment'))
                            <td class="text-right">
                                @php $payment = $payslip->payment(); $totalPayment += $payment?->paid_amount ?: 0; @endphp
                                @if($payment)
                                    {{ $payment->paid_amount }}
                                    <button class="ml-2 btn btn-sm" onclick="startPayment(this, {{ $payment->id }})" data="{{ json_encode($payslip) }}" id="paybtn_{{ $payslip->id }}" amount="{{ $payment->paid_amount }}"><i class="fa fa-pencil text-info"></i></button>
                                @else
                                    @if($for_export ?? false)
                                        -
                                    @else
                                        <div style="width: 75px;"><button class="ml-2 btn btn-success btn-sm" onclick="startPayment(this)" data="{{ json_encode($payslip) }}" id="paybtn_{{ $payslip->id }}"><i class="fa fa-money-bill"></i> Pay</button></div>
                                    @endif
                                @endif
                            </td>
                            <td class="text-right">
                                @php $totalPayrollBalance += $payslip->employee?->payroll_balance ?: 0; @endphp
                                {{ $payslip->employee?->payroll_balance }}
                            </td>
                            @endif
                            @if($for_export ?? false)
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            @else
                                <td class="text-right">
                                    <div style="width: 75px;"><a href="{{ route('payslip.show', $payslip->id) }}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i> Open</a></div>
                                </td>
                            @endif
                            @php
                                $total_gross_salary += $payslip->employee?->payroll_card?->gross_salary;
                                $total_net_salary += $payslip->total_salary;
                            @endphp
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        @endif
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
            @if($for_export ?? false)
            @else
                <td></td>
            @endif
        </tr>
    </tfoot>
</table>
