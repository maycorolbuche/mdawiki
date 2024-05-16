<html>

<head>
    <title>@yield('title')
        @if (!empty(trim($__env->yieldContent('title'))))
            ::
        @endif
        {{ $_ENV['APP_NAME'] }}
    </title>

    <style>
        @page {
            margin: 1cm 1cm;
        }

        header {
            position: fixed;
            top: -1cm;
            left: 0px;
            right: 0px;
            height: 50px;
        }

        footer {
            position: fixed;
            bottom: -1cm;
            left: 0px;
            right: 0px;
            height: 50px;
        }

        .pagenum:before {
            content: counter(page);
        }

        body {
            font-family: "Open Sans", Helvetica, Arial, sans-serif;
            font-size: 11px;
            font-weight: 400;
            line-height: 1.5;
            color: #000;
            background: $FFF;
        }

        a {
            text-decoration: none;
        }
    </style>
</head>


<body>
    @yield('content')
</body>

</html>
