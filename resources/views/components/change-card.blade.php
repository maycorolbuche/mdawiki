@if ($type == 'image')
    <div class="d-flex">
        <div class="flex-shrink-0 d-flex align-items-center justify-content-center">
            <img src="{{ $avatar }}" class="avatar img-fluid rounded-circle" alt="{{ $name }}">
        </div>
        <div class="d-flex flex-column justify-content-center flex-grow-1 ms-3">
            <strong>{{ $name }}</strong>
            <div class="text-muted">
                {{ $datetime }}
            </div>
            @if (!$img)
                <div>
                    <span class="badge bg-danger">
                        Imagem Excluída
                    </span>
                </div>
            @endif
            {{ $slot }}
        </div>
        <div class="flex-shrink-0 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center bg-{{ $img ? 'light' : 'danger' }} text-white"
                style="width: 50px;height: 50px;">
                @if ($img)
                    <img src="{{ $img }}" style="width: 100%;height: 100%;object-fit: cover;" />
                @else
                    <x-icon value="trash-2" />
                @endif
            </div>
        </div>
    </div>
@endif
