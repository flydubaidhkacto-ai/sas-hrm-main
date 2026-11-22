<div class="mb-2">
    <div class="row">
        <div class="col-3">
            <img src="/uploaded_files/{{ $asset_request->employee?->picture_file }}" alt="" class="w-100">
        </div>
        <div class="col-9">
            <h4>{{ $asset_request->employee?->name }}</h4>
            <p class="text-muted mb-0">{{ $asset_request->employee?->email }}</p>
            <p class="text-muted mb-0">{{ $asset_request->employee?->phone }}</p>
            <p class="text-muted mb-0">{{ $asset_request->employee?->address }}</p>
        </div>
    </div>
</div>
<div class="mb-2">
    <b for="">Reason:</b>
    <h3>
        {{ $asset_request->reason }}
    </h3>
</div>


@if (isset($asset_request->start_dt))
    <b>Start Date: </b>
    {{ date('d, F Y', strtotime($asset_request->start_dt)) }}
@endif

@if (isset($asset_request->end_dt))
    <b class="ml-4"> End Date: </b>
    {{ date('d, F Y', strtotime($asset_request->end_dt)) }}
@endif


@include('layouts.approval', [
    'model' => $asset_request,
    'model_type' => 'asset_request',
    'employee' => $asset_request->employee,
    'have_form' => true,
])

