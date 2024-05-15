<a class="btn btn-sm btn-{{ $layout }} m-1 d-flex align-items-center" href="{{ $route }}">
    @if ($icon)
        <div class="me-1 d-flex align-items-center">
            <x-icon value="{{ $icon }}" />
        </div>
    @endif
    <span>
        {{ $label }}{{ $slot }}
    </span>
</a>
