@foreach($list as $r)
    @php $c = $r->criterion; @endphp
    <div class="card mb-2"><div class="card-body">
        <h5>{{ $c->code }}. {{ $c->title }}</h5>
        <div class="row">
            @foreach(['low' => $c->low_text, 'mid' => $c->mid_text, 'high' => $c->high_text] as $k => $text)
                <div class="col-md-4 mb-2">
                    <label class="border rounded p-2 d-block h-100 {{ $r->level === $k ? 'bg-light border-primary' : '' }}">
                        @if($edit)
                            <input type="radio" name="ratings[{{ $r->id }}][level]" value="{{ $k }}" @checked($r->level === $k)>
                        @else
                            {{ $r->level === $k ? '☑' : '☐' }}
                        @endif
                        {{ $text }}
                    </label>
                </div>
            @endforeach
        </div>
        <div class="row mt-2">
            <div class="col-md-3">
                <label class="font-weight-bold">Scale of Marking</label>
                @if($edit)
                    <select name="ratings[{{ $r->id }}][marks]" class="form-control" required>
                        <option value="">--</option>
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" @selected($r->marks == $i)>{{ sprintf('%02d', $i) }}</option>
                        @endfor
                    </select>
                @else
                    <div><strong>{{ $r->marks ? sprintf('%02d', $r->marks) : '-' }}</strong> / 10</div>
                @endif
            </div>
            <div class="col-md-9">
                <label class="font-weight-bold">Comments</label>
                @if($edit)
                    <textarea name="ratings[{{ $r->id }}][comment]" rows="2" class="form-control">{{ $r->comment }}</textarea>
                @else
                    <div>{{ $r->comment ?: '—' }}</div>
                @endif
            </div>
        </div>
    </div></div>
@endforeach
