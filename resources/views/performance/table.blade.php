<div style="overflow: scroll">
    <div class="row">
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-4 col-sm-4">
                    <img src="/uploaded_files/{{ $data->emp?->picture_file }}" alt="" class="w-100">
                </div>
                <div class="col-md-8 col-sm-8">
                    <b>Code: </b><br>
                    {{ $data->emp?->id }}
                    <br>
                    <b>Employee ID: </b><br>
                    {{ $data->emp?->employee_id }}
                    <br>
                    <b>Name: </b><br>
                    {{ $data->emp?->name }}
                    <br>
                    <b>Super Department: </b><br>
                    {{ $data->emp?->department?->super_department?->name }}
                    <br>
                    <b>Department: </b><br>
                    {{ $data->emp?->department?->name }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-6">
                    <p>
                        Present: {{ $data->total_data['total_worked_days'] ?? 0 }}
                    </p>
                    <p>
                        Absent: {{ $data->total_data['total_absent_days'] ?? 0 }}
                    </p>
                    <p>
                        Leave: {{ $data->total_data['total_leave_days'] ?? 0 }}
                    </p>
                </div>
                <div class="col-md-6">
                    <p>
                        Total Late Days: {{ $data->total_data['total_late_days'] ?? 0 }}
                    </p>
                    <p>
                        Total Late Time: {{ seconds_to_hh_mm($data->total_data['total_late_seconds']) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="container">
                <label for="">Note:</label>
                <textarea name="note" rows="5" class="form-control mt-2"
                    @if($editable ?? false)
                        readonly
                    @else
                        _id="{{ $data->performance['month']->id }}"
                        onkeyup="update_note(this)"
                        onmouseup="update_note(this)"
                        onchange="update_note(this)"
                    @endif
                >{{ $data->performance['month']->notes }}</textarea>
            </div>
        </div>
        <h4 class="col-12 mt-2">
            Month: {{ $data->date->format('F Y') }}
        </h4>
    </div>
    @if($editable ?? false) @else
        <div class="mt-2 mb-1">
            <button class="btn btn-info" onclick="$(this).next().removeClass('d-none');this.remove()">
                <i class="fa fa-plus"></i> Add new category
            </button>
            <div class="add d-none">
                <form action="{{ route('performance.categories.assign', $data->performance['month']->id) }}" method="post">
                    @csrf
                    <div class="mb-2" style="max-width: 500px">
                        <label for="">Category:</label>
                        <select name="category" id="" required class="form-control">
                            <option value="">Select Category</option>
                            @foreach (\App\Models\PerformanceCategory::active()->get() as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add</button>
                </form>
            </div>
        </div>
    @endif
    <div class="mt-2" style="@if($editable ?? false) @else width: 2400px; @endif overflow: auto">
        <table class="table">
            <thead>
                <tr>
                    <th>S.L</th>
                    <th>Name</th>
                    @php
                        $curr = $data->date->copy();
                        $total = $data->date->copy()->endOfMonth()->day;
                    @endphp
                    @for ($i = 1; $i <= $total; $i++)
                        <th class="small">
                            {{ $data->date->copy()->setDay($i)->format('d') }}
                        </th>
                    @endfor
                    <th>
                        Average
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data->performance['categories'] as $item)
                    <tr>
                        <td>
                            {{ $loop->iteration }}
                        </td>
                        <td>
                            {{ $item->name }}
                        </td>
                        {{-- @if ($data->performance['categorized'][$item->id]['days'] ?? false) --}}
                        @foreach ($data->performance['categorized'][$item->id]['days'] ?? [] as $day)
                            <td class="small">
                                @if ($day->category?->from_system)
                                    {{ $day->value }}
                                @else
                                    @if($editable ?? false)
                                        {{ $day->value }}
                                    @else
                                        <input type="text" value="{{ $day->value }}" style="width: 40px" class=""
                                            performance="{{ $day->id }}"
                                            onkeyup="update_performance(this)"
                                            onmouseup="update_performance(this)"
                                            onchange="update_performance(this);$('.chart-refresh').removeClass('d-none')"
                                            data-toggle="tooltip" title="Max value is {{ $day->category?->max_value }}"
                                        >
                                    @endif
                                @endif
                            </td>
                        @endforeach
                        <td>
                            {{ $data->performance['categorized'][$item->id]['percentage'] ?? '0' }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<style>
    th.small,
    td.small
    {
        padding-left: 0;
        padding-right: 0;
    }
</style>
<script>
    function update_note(el) {
        const id = el.getAttribute('_id')
        const value = el.value
        const url = "{{ route('performance.update-notes', ':id') }}".replace(':id', id)
        $.post(url, {
            value, id
        })
    }
    function update_performance(el) {
        const id = el.getAttribute('performance')
        const value = el.value
        const url = "{{ route('performance.update-day') }}"
        $.post(url, {
            value, id
        })
        .then(res => {
            if(res.too_big) {
                el.value = res.too_big
            }
        })
    }
</script>

