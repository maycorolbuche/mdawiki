<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <title>@yield('title')
        @if (!empty(trim($__env->yieldContent('title'))))
            ::
        @endif
        {{ $_ENV['APP_NAME'] }}
    </title>

    <link rel="shortcut icon" href="{{ asset('img/icons/favicon.png') }}" />
    <link rel="canonical" href="{{ url()->current() }}" />

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        html,
        body {
            background: #FFF !important;
        }
    </style>
</head>

<body>
    <main class="content">
        <div class="container-fluid p-0">
            <div class="page">
                @yield('content')
            </div>
        </div>
    </main>

    <script src="{{ asset('js/app.js') }}"></script>
</body>

</html>
