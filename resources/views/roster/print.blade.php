</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roster - Common Shift</title>
    <style>
        @media print {
            @page {
                size: landscape;
                margin: 10mm;
            }

            body {
                font-family: Arial, sans-serif;
                padding: 20px;
                background: #fff;
                color: #000;
            }
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 200px;
            margin-bottom: 10px;
            height: 100px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            margin-top: 10px;
        }

        .sub-title {
            font-size: 18px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .table th,
        .table td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        .table th {
            background: #f4f4f4;
            font-size: 14px;
        }

        .legend {
            font-size: 14px;
            margin-top: 10px;
        }

        /* row disign */
        .shift-name {
            font-weight: bold;
            color: #007bff;
            background: #e3f2fd;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 5px;
        }

        .shift-time {
            background: #f8f9fa;
            padding: 5px;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="header">
        @php
            $formatted = $roster->formatted();

            $parentDomain = request()->getHost();
            $info = company_data();

        @endphp
        @if ('hrm.sasflydubai.com' == $parentDomain || config('app.customer') === 'sas')
            {{-- <img src="{{ asset('images/logo/flydubai.jpg') }}" alt="Company Logo" class="logo"> --}}
        @else
            <img src="{{ asset('uploaded_files/' . isset($info['logo']) ?? '') }}" alt="Company Logo" class="logo">
        @endif
        <div class="title">{{ $info['name'] ?? 'Flydubai' }}</div>
        <div class="sub-title">Roster Duty - {{ $roster->superDepartment->name ?? 'N/A' }}</div>
        <h2>{{ $roster->name }} Shift</h2>
    </div>

    @php
        $from = carbon($roster->start);
        $to = carbon($roster->end);
        $days = $from->diffInDays($to);
    @endphp
    {{-- <h3>For the Month of December 01-15, 2024</h3> --}}
    <h3>For the Month of {{ $from->format('F') }} {{ $from->format('d') }}-{{ $to->format('d') }},
        {{ $from->format('Y') }}</h3>

    <table class="table">
        <thead>
            <tr>
                <th rowspan="2">SL</th>
                <th rowspan="2">Emp. Name</th>
                <th>DATE</th>

                @for ($i = 0; $i <= $days; $i++)
                    <th
                        @if ($from->copy()->addDay($i)->format('l') == 'Sunday')
                            style="border-left: 3px solid #000;"
                        @endif
                    >
                        {{ $from->copy()->addDay($i)->format('d M Y') }}
                    </th>
                @endfor

            </tr>
            <tr>
                <th>DAY</th>
                @for ($i = 0; $i <= $days; $i++)
                    <th
                        @if ($from->copy()->addDay($i)->format('l') == 'Sunday')
                            style="border-left: 3px solid #000;"
                        @endif
                    >
                        {{ $from->copy()->addDay($i)->format('D') }}
                    </th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($formatted as $i1 => $item)
                <tr
                    @if (($i1 + 1) % 5 == 0 && $i1 != count($formatted) - 1)
                        style="page-break-after: always;break-after: page;"
                    @else
                        style="height: 61px"
                    @endif
                >
                    @php
                        $from = carbon($roster->start);
                    @endphp
                    <td>{{ $loop->iteration }}</td>
                    <td colspan="2">
                        @foreach ($item['employees'] as $emp_id)
                            {{ \App\Models\User::find($emp_id)?->name }}
                        @endforeach
                    </td>
                    @for ($i = 0; $i <= $days; $i++)
                        <td class="table-col"
                            @if ($from->format('l') == 'Sunday')
                                style="border-left: 3px solid #000;"
                            @endif
                        >
                            <div>
                                @php
                                $shiftfound = false;
                                @endphp
                                @foreach ($item['shifts'][$from->format('Y-m-d')] ?? [] as $shift)
                                @if ($shift)
                                <div class="">
                                    <div class="">
                                       
@include('roster.partials.shift-label', [
    'employeeIds' => $item['employees'] ?? [],
    'date' => $from->format('Y-m-d'),
])

                                    </div>
                                    @php
                                    $shiftfound = true;
                                    @endphp
                                </div>
                                @endif
                                @endforeach
                                @if (
                                !$shiftfound &&
                                ($roster->no_shift_duty &&
                                ($roster->no_shift_duty[$i1] ?? null) &&
                                ($roster->no_shift_duty[$i1][$from->format('Y-m-d')] ?? null)))
                                <div class="no-duty">
                                    @include('roster.partials.no-shift-duty', ['duty' => $roster->no_shift_duty[$i1][$from->format('Y-m-d')]])
                                </div>
                                @elseif(!$shiftfound)
                                <div class="no-duty">
                                    {{-- No Duty--}}
                                </div>
                                @endif
                            </div>
                        </td>
                        @php
                            $from->addDay();
                        @endphp
                    @endfor
                </tr>
                @endforeach
        </tbody>
    </table>

    <div class="legend">
        <strong>Checked by:</strong>
        D-Extra duty, W-Week Off, S-Sick, L-Leave On, A-Absent, H-Working on Gov. Holiday,<br>
        C-Compensation Off, O-Stand by, T-Training, G-Gov. Holiday, ML-Maternity Leave,<br>
        XA-Morning Shift, XP-Evening Shift, Off-Office Work, WFH-Work From Home.
    </div>

    <script>
        window.onload = function() {
            window.print();
            setTimeout(() => window.close(), 500);
        };
    </script>
</body>

</html>
