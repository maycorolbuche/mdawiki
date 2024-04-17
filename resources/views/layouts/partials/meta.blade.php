<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="author" content="Mayco Rolbuche, mayco_rolbuche@hotmail.com">
<meta name="robots" content="index,follow" />
<meta name="language" content="pt-BR">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="reply-to" content="{{ $_ENV['MAIL_REPLY_TO'] }}">

<meta name="url" content="{{ url()->current() }}">
<meta name="identifier-URL" content="{{ url()->current() }}">

<meta name="keywords" content="@yield('keywords')">
<meta name="description" content="@yield('description')">
<meta name="subject" content="Tudo sobre Desbravadores e Aventureiros">
<meta name="revised" content="@yield('revised')" />

<meta name="og:title" content="@yield('title')" />
<meta name="og:type" content="website" />
<meta name="og:url" content="{{ url()->current() }}" />
<meta name="og:site_name" content="{{ $_ENV['APP_NAME'] }}" />
<meta name="og:description" content="@yield('description')">
<meta name="og:email" content="{{ $_ENV['MAIL_REPLY_TO'] }}" />
<meta name="og:image" content="@yield('image')" />
<meta property="og:image:type" content="@yield('image-type')">
<meta property="og:image:width" content="@yield('image-width')">
<meta property="og:image:height" content="@yield('image-height')">

<meta name="fb:page_id" content="{{ $_ENV['FACEBOOK_PAGE_ID'] }}" />

<meta name="twitter:title" content="@yield('title')" />
<meta name="twitter:card" content="summary">
<meta name="twitter:url" content="{{ url()->current() }}" />
<meta name="twitter:description" content="@yield('description')">
<meta name="twitter:image" content="@yield('image')" />
