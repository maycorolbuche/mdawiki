<div class="d-flex">
    <div class="flex-shrink-0 d-flex align-items-center justify-content-center">
        <x-icon value="{{ $icon }}" />
    </div>
    <div class="flex-grow-1 ms-3">
        <strong>{{ $title }}</strong>
        <div class="text-muted">
            {{ $value }}
            {{ $slot }}
        </div>
    </div>
</div>
