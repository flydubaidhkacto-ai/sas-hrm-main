@php
    $suggest = \App\Support\Scoring::band((float) ($review->marks ?? $appraisal->total_percent ?? 0));
    $p = $prefix ?? '';
    $n = fn ($f) => $p ? "{$p}[{$f}]" : $f;
@endphp
<div class="row">
    <div class="col-md-4 form-group">
        <label>Marks (0-100)</label>
        <input type="number" step="0.01" min="0" max="100" name="{{ $n('marks') }}" class="form-control"
               value="{{ old($n('marks'), $review->marks ?? $appraisal->total_percent) }}">
        <small class="text-muted">Auto total from ratings: {{ $appraisal->total_percent ?? '-' }} ({{ $suggest['label'] }})</small>
    </div>
    <div class="col-md-4 form-group">
        <label>Recommendation</label>
        <select name="{{ $n('recommendation') }}" class="form-control">
            @foreach(\App\Support\Scoring::RECOMMENDATIONS as $opt)
                <option @selected(($review->recommendation ?? $suggest['recommendation']) === $opt)>{{ $opt }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 form-group">
        <label>Increment</label>
        <select name="{{ $n('increment_steps') }}" class="form-control">
            @for($i = 1; $i <= 4; $i++)
                <option value="{{ $i }}" @selected(($review->increment_steps ?? $suggest['steps']) == $i)>{{ $i }}-Step{{ $i > 1 ? 's' : '' }}</option>
            @endfor
        </select>
    </div>
</div>
<div class="form-group">
    <label>Comments</label>
    <textarea name="{{ $n('comment') }}" rows="3" class="form-control">{{ $review->comment ?? '' }}</textarea>
</div>
