<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">{{ $title }}</h3>
            <ul class="breadcrumb">
                @foreach ($breadcrumbs as $breadcrumb)
                    @if (!empty($breadcrumb['route']))
                        <li class="breadcrumb-item">
                            <a href="{{ $breadcrumb['route'] }}">{{ $breadcrumb['name'] }}</a>
                        </li>
                    @else
                        <li class="breadcrumb-item {{ $breadcrumb['active'] ? 'active' : '' }}">
                            {{ $breadcrumb['name'] }}
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
</div>
