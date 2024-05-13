<div class="card">
    @if ($title != '')
        <div class="card-header w-100 mb-0 pb-0">
            <h1 class="pb-2 mb-2 border-bottom mb-0 pb-0">{{ $title }}</h1>
        </div>
    @endif
    <div class="card-body {{ $title ? 'pt-2' : '' }} {{ $bodyClass }}">
        {{ $slot }}
    </div>
</div>
