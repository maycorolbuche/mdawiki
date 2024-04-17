<div class="card">
    @if ($title != '')
        <div class="card-header w-100 mb-0 pb-0">
            <h2 class="border-bottom mb-0 pb-0">{{ $title }}</h2>
        </div>
    @endif
    <div class="card-body {{ $title ? 'pt-2' : '' }}">
        {{ $slot }}
    </div>
</div>
