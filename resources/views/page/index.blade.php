@extends('layouts.app')
@section('title', 'Página Teste')
@section('description', $description)
@if ($data)
    @section('image', 'https://mda.wiki.br/site/@imgs_wiki_cp/imagem@logo_amigo.png')
    @section('image-type', 'image/png')
    @section('image-width', '150')
    @section('image-height', '150')
    @section('revised', $data->updated_at)
@endif

@section('content')
    <div class="d-flex align-items-center">
        <x-title>{{ $title }}</x-title>
        <div class="ms-auto"></div>
        @if ($data)
            <x-button type="edit" route="{{ route('page.edit', ['page' => $page]) }}" />
        @endif
        <x-button type="history" route="{{ route('page.log', ['page' => $page]) }}" />
        @if ($data)
            <x-button type="refresh" route="{{ route('page.refresh', ['page' => $page]) }}" />
            <x-button type="print" route="{{ route('page.pdf', ['page' => $page]) }}" />
        @endif

    </div>

    <div class="row">
        <div class="col-12">

            <x-content body-class="page">
                @include('layouts.partials.messages')

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


@push('styles')
    <style>
        
    </style>
@endpush

@push('scripts')
    <script></script>
@endpush
