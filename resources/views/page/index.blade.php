@extends('layouts.app')
@section('title', 'Página Teste')
@section('description', 'Dsadsa sdfsdfds Teste')
@section('keywords', 'Página Teste')
@section('image', 'https://mda.wiki.br/site/@imgs_wiki_cp/imagem@logo_amigo.png')
@section('image-type', 'image/png')
@section('image-width', '150')
@section('image-height', '150')
@section('revised', '2024-04-10 10:06:33')

@section('content')
    <x-title>{{ $title }}</x-title>

    @include('layouts.partials.messages')

    <div class="row">
        <div class="col-12">
            <x-content>
                <div class="card-actions float-end mb-3">
                    <a class="btn btn-sm btn-info" href="{{ route('page.log', ['page' => $page]) }}">
                        Histórico
                    </a>
                    @if ($data)
                        <a class="btn btn-sm btn-primary" href="{{ route('page.edit', ['page' => $page]) }}">
                            Editar Página
                        </a>
                    @endif
                </div>

                @if (!$data)
                    <p>Esta página não existe. Seja o primeiro a criar esta página!</p>

                    <div class='text-center'>
                        <a href="{{ route('page.edit', ['page' => $page]) }}" class="btn btn-primary btn-lg">
                            Adicionar Página
                        </a>
                    </div>
                @else
                    {!! $data->content_preview !!}
                @endif
            </x-content>
        </div>
    </div>
@endsection
