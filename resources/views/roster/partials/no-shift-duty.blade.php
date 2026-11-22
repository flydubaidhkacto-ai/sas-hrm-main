@if ($duty === 'WFH')
    Select a shift for remote work
@else
    {{ $duty === 'S' ? 'SL' : $duty }}
@endif
