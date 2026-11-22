<tr class="location-row" data-location-id="{{ $location->id }}" data-level="{{ $level }}">
    <td style="padding-left: {{ $level * 20 }}px;">
        @if($level > 0)
            @for($i = 0; $i < $level; $i++)
                <span class="tree-indent">│&nbsp;&nbsp;&nbsp;</span>
            @endfor
            <span class="tree-branch">├─ </span>
        @endif
        <strong>{{ $location->name }}</strong>
    </td>
    <td>
        {{ ($location->phone) }}
    </td>
    <td>
        <span class="badge badge-info">{{ ucfirst($location->type) }}</span>
    </td>
    <td>
        @if($location->hasCoordinates())
            <span class="badge badge-success">
                <i class="fa fa-map-marker"></i>
                {{ number_format($location->latitude, 6) }}, {{ number_format($location->longitude, 6) }}
            </span>
        @else
            <span class="badge badge-secondary">No coordinates</span>
        @endif
    </td>
    <td>
        @if($location->radius)
            <span class="badge badge-warning">{{ $location->radius }}m</span>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td class="text-right">

        <div class="btn-group" role="group">
            @if($location->type == 'area')
            <button type="button" class="btn btn-success btn-sm" onclick="addChild({{ $location->id }}, '{{ $location->name }}')" title="Add Point Location">
                <i class="fa fa-plus"></i> Add Point
            </button>
            @endif
            <button type="button" class="btn btn-info btn-sm" onclick="editLocation({{ $location->id }})" title="Edit Location">
                <i class="fa fa-edit"></i>
            </button>
            @if($location->hasCoordinates())
                <a href="https://maps.google.com/?q={{ $location->latitude . ',' . $location->longitude }}" target="_blank" class="btn btn-warning btn-sm" title="View on Map">
                    <i class="fa fa-map-marker"></i>
                </a>
            @endif
            @if(role_can('locations') || role_can('locations.location'))
                {{-- @if($location->children->count() == 0 && $location->users->count() == 0) --}}
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteLocation({{ $location->id }}, '{{ $location->name }}')" title="Delete Location">
                        <i class="fa fa-trash"></i>
                    </button>
                {{-- @endif --}}
            @endif
        </div>
    </td>
</tr>

@if($location->children->count() > 0)
    @foreach($location->children as $child)
        @include('locations.partials.location-row', ['location' => $child, 'level' => $level + 1])
    @endforeach
@endif
