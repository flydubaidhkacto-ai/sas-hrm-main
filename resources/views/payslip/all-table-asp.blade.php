@php
    $info = company_data();
@endphp
@if ($for_export ?? false)
    <h2 style="text-align: center">
        {{ $info['name'] ?? 'Hr payroll' }}
    </h2>
    <p style="text-align: center">
        Salary Sheet
    </p>

    <style>
        table {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
            font-size: 9px;
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
    @endphp
    <thead>
        <tr>
            <th>S.L</th>
            <th>Code</th>
            <th>Name</th>
            <th>Designation</th>
            <th>Month</th>
            <th>Pay Type</th>
            @foreach (\App\Models\EarnHead::where('category', '!=', 'basic')->get() as $earnHead)
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
            @if($for_export ?? false)
            {{-- <th>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </th> --}}
            @else
                @if(has_module('payslip-payment'))
                    <th class="text-right no-sort">Payment</th>
                @endif
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
                    @if($for_export ?? false)
                        {{ $items->employee?->name }}
                    @else
                        <h2 class="table-avatar">
                            <a href="{{ route('employees.active.show', $items->employee) }}">
                                {{ $items->employee?->name }}
                            </a>
                        </h2>
                    @endif
                </td>
                <td>{{ $items->employee?->designation?->name }}</td>
                <td>
                    {{ carbon($items->month_date)->format('F, Y') }}
                </td>
                <td>
                    {{ ucfirst($items->pay_type) }}
                </td>
                @foreach (\App\Models\EarnHead::where('category', '!=', 'basic')->get() as $index => $earnHead)
                    @php
                        $found = false;
                    @endphp
                    @foreach ($items->earn_heads as $data)
                        @if ($data['code'] == $earnHead->code)
                            @php
                                $found = true;
                                $earnTotals[$i][$index] = $data['total_amount'] ?? $data['amount'] ?? 0;
                            @endphp
                            <td>
                                {{ $data['total_amount'] ?? $data['amount'] ?? 0 }}
                            </td>
                        @endif
                    @endforeach
                    @if (!$found)
                        <td>-</td>
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
                                $deductTotals[$i][$index] = $data['total_amount'] ?? $data['amount'] ?? 0;
                            @endphp
                            <td>
                                {{ $data['total_amount'] ?? $data['amount'] ?? 0 }}
                            </td>
                        @else
                        @endif
                    @endforeach
                    @if (!$found)
                        <td>-</td>
                    @endif
                @endforeach
                <td>{{ $items->employee?->payroll_card?->gross_salary }}</td>
                <td>
                    {{ number_format($items->total_salary, 2) }} Tk.
                </td>
                @if($for_export ?? false)
                    {{-- <td></td> --}}
                @else
                    @if(has_module('payslip-payment'))
                    <td class="text-right">
                        @if($items->payment)
                            {{ $items->payment->paid_amount }} Tk
                        @else
                            <div style="width: 75px;">
                                <button class="ml-2 btn btn-success btn-sm" onclick="startPayment(this)"
                                    data="{{ json_encode($items) }}"
                                >
                                    <i class="fa fa-money-bill"></i>
                                    Pay
                                </button>
                            </div>
                        @endif
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
                @endif
                @php
                    $total_gross_salary += $items->employee?->payroll_card?->salary;
                    $total_net_salary += $items->total_salary;
                @endphp
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total-row">
            {{-- <td colspan="{{ $column_count - 2 }}" style="text-align: right;">
                <strong>Total</strong>
            </td> --}}
            <td colspan="6" style="text-align: right">
                <b>Total:</b>
            </td>
            @foreach (\App\Models\EarnHead::where('category', '!=', 'basic')->get() as $index => $earnHead)
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
            <td></td>
        </tr>
    </tfoot>
</table>
