<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <title>@yield('title')
        @if (!empty(trim($__env->yieldContent('title'))))
            ::
        @endif
        {{ $_ENV['APP_NAME'] }}
    </title>

    @include('layouts.partials.meta')

    <link rel="shortcut icon" href="{{ asset('img/icons/favicon.png') }}" />
    <link rel="canonical" href="{{ url()->current() }}" />

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @include('layouts.partials.analytics')

    @stack('styles')
</head>

<body d_ata-sidebar-layout="compact">
    <div class="wrapper">

        @include('layouts.partials.sidebar')

        <div class="main">

            @include('layouts.partials.navbar')

            <main class="content">
                <div class="container-fluid p-0">

                    @yield('content')

                </div>
            </main>

            @include('layouts.partials.footer')

        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')

</body>

</html>
