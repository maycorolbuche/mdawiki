@php
    $items = [
        ['title' => 'Conta', 'route' => 'settings.account'],
        ['title' => 'E-mail', 'route' => 'settings.email'],
        ['title' => 'Senha', 'route' => 'settings.password'],
        ['title' => 'Privacidade', 'route' => 'settings.privacy'],
        ['title' => 'Notificações', 'route' => 'settings.notifications'],
    ];
@endphp

<x-card title="Configurações" body-class="m-0 p-0">
    <div class="list-group list-group-flush">
        @foreach ($items as $item)
            <a class="list-group-item list-group-item-action {{ Route::is($item['route']) ? ' active' : '' }}"
                href="{{ route($item['route']) }}" role="tab">
                {{ $item['title'] }}
            </a>
        @endforeach
    </div>
</x-card>
