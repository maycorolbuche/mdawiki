<div class="card card-group">
    @if ($title != '')
        <div class="card-header w-100 m-0 p-0 mb-2">
            <h2 class="border-bottom mb-0 pb-0">{{ $title }}</h2>
        </div>
    @endif
    <div class="card-body m-0 p-0">
        {{ $slot }}
    </div>
</div>
