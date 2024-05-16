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
        {!! $data->content_pdf !!}
    @else
        Página não encontrada!
    @endif
</main>
