<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>

    <div style="width:790px">
        <p style="text-align:center"><span style="font-size:14px"><span
                    style="font-family:Comic Sans MS,cursive"><strong>Bashati Avenue, Plot-10, Unit C-4, Road#53,
                        Gulshan Avenue,</strong></span></span></p>

        <p style="text-align:center"><span style="font-size:14px"><span
                    style="font-family:Comic Sans MS,cursive"><strong>Gulshan-2, Dhaka-1212,
                        Bangladesh</strong></span></span></p>

        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
            <tbody>
                <tr>
                    <th style="text-align:center"><span style="font-family:Comic Sans MS,cursive"><strong>&nbsp; &nbsp;
                                &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                                LEAVE APPLICATION FOR</strong></span></th>
                    <td style="text-align:center">Date: {{ \Carbon\Carbon::parse($leave->created_at)->format('d.m.Y') }}</td>
                </tr>
            </tbody>
        </table>

        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
            <caption>&nbsp;</caption>
            <tbody>
                <tr>
                    <td colspan="3" style="text-align:center"><strong>TO&nbsp;BE FILLED BY
                            APPLICANT</strong>&nbsp;&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>&nbsp;Name:</strong>
                        <span>{{ $leave->employee?->name }}</span>
                    </td>
                    <td><strong>&nbsp;Designation:</strong>
                        <span>{{ $leave->employee?->designation?->name }}</span>
                    </td>
                </tr>
                <tr>
                    <td><strong>&nbsp;DOJ:</strong>
                        <span>{{ ($leave->employee?->join_date) ? \Carbon\Carbon::parse($leave->employee?->join_date)->format('d-m-Y') : '' }}</span>
                    </td>
                    <td><strong>&nbsp;Staff ID:</strong>
                        <span>{{ $leave->employee?->id }}</span>
                    </td>
                    <td><strong>Department:</strong>
                        <span>{{ $leave->employee?->department?->name }}</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align:center"><strong>LEAVE PERIOD</strong></td>
                </tr>
            </tbody>
        </table>

        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
            <tbody>
                <tr>
                    <td style="text-align:center"><strong>FORM</strong></td>
                    <td style="text-align:center"><strong>TO</strong></td>
                    <td style="text-align:center"><strong>TOTAL DAY&#39;s</strong></td>
                </tr>
                <tr>
                    <td style="text-align:center">
                        <span>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M, Y') }}</span>
                    </td>
                    <td style="text-align:center">
                        <span>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M, Y') }}</span>
                    </td>
                    <td style="text-align:center">
                        <span>{{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}</span>
                    </td>
                </tr>
            </tbody>
        </table>

        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
            <caption><strong>DESCRIPTION OF LEAVE</strong></caption>
            <tbody>
                <tr>
                    <td>&nbsp;</td>
                    <td style="text-align:center"><strong>REASON FOR LEAVE</strong></td>
                </tr>
                <tr>
                    <td style="text-align:center">
                        <div style="width: 400px; margin: 0 auto; padding: 10px 0;">
                            @foreach (\App\Models\LeaveType::orderBy('name')->get() as $type)
                            <span style="margin: 10px">
                                @if ($type->id == $leave->leave_type_id)
                                [&#10004;] {{ $type->name }}
                                @else
                                [ ] {{ $type->name }}
                                @endif
                            </span>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        <p style="text-align: center; padding: 10px">{{ $leave->title }}</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
            <tbody>
                @foreach ($leave->getApprovalSteps() as $i => $item)
                <tr>
                    <td style="padding: 10px">
                        {{  $item['approver']->designation?->name }}
                        Remarks:&nbsp;
                        <p>
                            @if ($item['decided']['note'] ?? null)
                                {!! str_replace("\n", "<br/>", $item['decided']['note']) !!}
                            @endif
                        </p>
                    </td>
                    <td >
                        <div style="width: 180px; text-align: center">
                            <p>&nbsp;</p>
                            @if ($item['approver']->signature_file)
                                <img src="{{ url('/uploaded_files/' . $item['approver']->signature_file) }}"
                                    alt="Signature" width="100">
                            @endif
                            <p>&nbsp; &nbsp;Signature of {{ $item['approver']->designation?->name }}</p>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</body>
<script>
    window.onload = function() {
        window.print();
        setTimeout(() => window.close(), 500);
    };
</script>
</html>
