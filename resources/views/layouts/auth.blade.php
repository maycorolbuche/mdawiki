<!DOCTYPE html>
<html lang="en">

<head>
    <title>@yield('title')
        @if (!empty(trim($__env->yieldContent('title'))))
            ::
        @endif
        {{ $_ENV['APP_NAME'] }}
    </title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex,nofollow" />

    <link rel="shortcut icon" href="{{ asset('img/icons/favicon.png') }}" />
    <link rel="canonical" href="{{ url()->current() }}" />

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @include('layouts.partials.analytics')

    @stack('styles')
</head>

<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
    <main class="d-flex w-100 h-100">
        <div class="container d-flex flex-column p-0 m-0">
            <div class="row vh-100 p-0 m-0">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">

                        @yield('content')

                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')

</body>

</html>
