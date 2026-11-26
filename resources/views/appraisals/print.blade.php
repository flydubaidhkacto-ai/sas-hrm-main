<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Appraisal - {{ $appraisal->employee->name }}</title>
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
    h2, h3 { text-align: center; margin: 12px 0 6px; }
    h3 { background: #ddd; padding: 4px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    td, th { border: 1px solid #333; padding: 4px 6px; vertical-align: top; }
    .box { display: inline-block; width: 11px; height: 11px; border: 1px solid #000; text-align: center; line-height: 11px; font-size: 10px; margin-right: 4px; }
    .sig { height: 40px; }
    @media print { .noprint { display: none; } }
</style>
</head>
<body>
<button class="noprint" onclick="window.print()">Print</button>
<h2>Employee Performance Appraisal</h2>
<table>
    <tr><td><b>Name:</b> {{ $appraisal->employee->name }}</td><td><b>Designation:</b> {{ $appraisal->employee->designation?->name }}</td></tr>
    <tr><td><b>Employee Code:</b> {{ $appraisal->employee->employee_id }}</td><td><b>Department:</b> {{ $appraisal->employee->department?->name }}</td></tr>
    <tr><td><b>Period:</b> {{ $appraisal->period }}</td><td><b>Total %:</b> {{ $appraisal->total_percent }}</td></tr>
</table>

@foreach(['one' => 'Section ONE - Performance Rating', 'three' => 'Section THREE - Overall Work Performance'] as $sec => $title)
    @if($sec === 'three')
        <h3>Section TWO - Job Performance Standards</h3>
        <table>
            <tr><th>#</th><th>Job Description / Performance</th><th>Satisfactory</th><th>Not Satisfactory</th><th>Std</th></tr>
            @for($i = 0; $i < 10; $i++)
                @php $j = $appraisal->jobItems[$i] ?? null; @endphp
                <tr>
                    <td>{{ $i + 1 }}</td><td>{{ $j?->description }}</td>
                    <td align="center">{{ $j && $j->is_satisfactory === true ? '✔' : '' }}</td>
                    <td align="center">{{ $j && $j->is_satisfactory === false ? '✔' : '' }}</td>
                    <td align="center">{{ $j?->standard }}</td>
                </tr>
            @endfor
        </table>
    @endif
    <h3>{{ $title }}</h3>
    @foreach($ratings->filter(fn ($r) => $r->criterion->section === $sec) as $r)
        @php $c = $r->criterion; @endphp
        <table>
            <tr><th colspan="3">{{ $c->code }}. {{ $c->title }}</th></tr>
            <tr>
                @foreach(['low' => $c->low_text, 'mid' => $c->mid_text, 'high' => $c->high_text] as $k => $t)
                    <td width="33%"><span class="box">{{ $r->level === $k ? '✔' : '' }}</span>{{ $t }}</td>
                @endforeach
            </tr>
            <tr><td colspan="3"><b>Scale of Marking:</b> {{ $r->marks ? sprintf('%02d', $r->marks) : '' }} / 10 &nbsp;&nbsp; <b>Comments:</b> {{ $r->comment }}</td></tr>
        </table>
    @endforeach
@endforeach

<h3>Section FOUR - Employee's Comment</h3>
<table><tr><td style="height:70px">{{ $appraisal->employee_comment }}</td></tr>
<tr><td align="right">Staff's Signature &amp; Date: {{ $appraisal->employee_signed_at?->format('d M Y') }}</td></tr></table>

@foreach(['supervisor' => 'Supervisor', 'hod' => 'Head of Department', 'hr' => 'Human Resource (HR)', 'md' => 'Managing Director'] as $key => $label)
    @php $rv = $appraisal->review($key); @endphp
    <h3>This Section filled by {{ $label }}</h3>
    <table>
        <tr><th>Marks</th><th>Result</th><th>Recommendation</th><th>Increment</th></tr>
        <tr>
            <td align="center">{{ $rv?->marks }}</td>
            <td align="center">{{ $rv?->result_band }}</td>
            <td align="center">{{ $rv?->recommendation }}</td>
            <td align="center">{{ $rv ? $rv->increment_steps . '-Step' . ($rv->increment_steps > 1 ? 's' : '') : '' }}</td>
        </tr>
        <tr><td colspan="4"><b>Comments:</b> {{ $rv?->comment }}</td></tr>
        <tr><td colspan="4" align="right" class="sig">{{ $label }} Signature &amp; Date: {{ $rv ? $rv->reviewer?->name . ' - ' . $rv->signed_at?->format('d M Y') : '' }}</td></tr>
    </table>
@endforeach
</body>
</html>
