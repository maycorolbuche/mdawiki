<div class="card">
    @if ($title != '')
        <div class="card-header">
            <h5 class="card-title mb-0 text-truncate">{{ $title }}</h5>
        </div>
    @endif
    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
</div>
