@extends('layouts.app')
@section('title', $title . ' (Histórico de Imagem)')
@section('description', 'Imagem - ' . $title)
@section('image', $image->full_url)
@section('image-type', $image->mime)
@section('image-width', $image->width)
@section('image-height', $image->height)
@section('revised', $image->updated_at)
@section('robots', 'noindex,nofollow')

@section('content')
    <x-title>Imagem: {{ $title }} <span class="badge bg-warning">Histórico</span></x-title>


    <div class="row">
        <div class="col-12">
            <x-content>

                <div class="card-actions float-end mb-3">
                    <a class="btn btn-sm btn-warning" href="{{ route('image', ['page' => $page]) }}">
                        Cancelar
                    </a>
                </div>

                <div style="clear: both"></div>

                @include('layouts.partials.messages')

                HISTPRICO


            </x-content>
        </div>
    </div>
@endsection
