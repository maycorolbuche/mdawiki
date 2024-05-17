<html>

<head>
    <title>@yield('title')
        @if (!empty(trim($__env->yieldContent('title'))))
            ::
        @endif
        {{ $_ENV['APP_NAME'] }}
    </title>

    <link href="css/pdf.css" rel="stylesheet">

    <style>
        @page {
            margin: 1cm 1cm !important;
        }

        html,
        body {
            font-family: "Open Sans", Helvetica, Arial, sans-serif !important;
        }

        /*header {
            position: fixed !important;
            top: -1cm !important;
            left: 0px !important;
            right: 0px !important;
            height: 50px !important;
        }*/

        footer {
            border-top: 1px solid #CCC;
            position: fixed !important;
            bottom: -1cm !important;
            left: 0px !important;
            right: 0px !important;
            height: 50px !important;
        }

        .pagenum:before {
            content: counter(page) !important;
        }

        html,
        body {
            font-size: 11px !important;
            font-weight: 400 !important;
            line-height: 1.5 !important;
            color: #000 !important;
            background: #FFF !important;
        }


        h1,
        .h1,
        h2,
        .h2,
        h3,
        .h3,
        h4,
        .h4,
        h5,
        .h5,
        h6,
        .h6,
        {
        font-weight: 0;
        }

        h1,
        .h1 {
            font-size: 22px !important;
            border-bottom: 1px solid #EEE !important;
            margin-top: 20px !important;
        }

        h2,
        .h2 {
            font-size: 20px !important;
            margin-bottom: 10px !important;
            margin-top: 20px !important;
        }

        h3,
        .h3 {
            font-size: 18px !important;
            line-height: 1.1 !important;
            margin-bottom: 10px !important;
            margin-top: 20px !important;
        }

        h4,
        .h4 {
            font-size: 16px !important;
            line-height: 1.1 !important;
            margin-bottom: 10px !important;
            margin-top: 20px !important;
        }

        h5,
        .h5 {
            font-size: 15px !important;
            line-height: 1.1 !important;
            margin-bottom: 10px !important;
            margin-top: 20px !important;
        }

        h6,
        .h6 {
            font-size: 14px !important;
            line-height: 1.1 !important;
            margin-bottom: 10px !important;
            margin-top: 20px !important;
        }


        .table,
        .table td {
            background: #FFF;
        }

        .table,
        .table th,
        .table td {
            padding: .25rem !important;
        }

        .table th,
        .table tfoot td {
            background: #f9f6f6 !important;
        }

        .table tbody,
        .table td,
        .table tfoot,
        .table th,
        .table thead,
        .table tr,
        .table>:not(:last-child)>:last-child>* {
            border-bottom:1px solid #dee2e6;
        }
    </style>
</head>


<body>
    @yield('content')
</body>

</html>
