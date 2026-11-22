@foreach($rosters as $roster)

    @php
        $formatted = $roster->formatted();
    @endphp

    @include('roster.print',[
        'roster'=>$roster,
        'formatted'=>$formatted
    ])

    @if(!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif

@endforeach