@extends('layouts.pdf')
@section('title', $title)

<footer>
    <b>{{ $_ENV['APP_NAME'] }}</b> |
    Conteúdo retirado do site
    <a href="{{ route('page', ['page' => $slug]) }}" target="_blank">
        {{ route('page', ['page' => $slug]) }}
    </a>
    |
    {{ date('d/m/Y H:i:s') }}
</footer>


<main>
    @if ($data)
        <h1 class="text-center" style="font-size: 26px !important;border-bottom:1px solid #000 !important;">
            {{ $title }}
        </h1>
        {!! $data->content_pdf !!}
    @else
        Página não encontrada!
    @endif
</main>
