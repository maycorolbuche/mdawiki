@extends('layouts.app')
@section('title', $title . ' (Histórico de Imagem)')
@section('description', 'Imagem - ' . $title)
@if ($image)
    @section('image', $image->image_url)
    @section('image-type', $image->mime)
    @section('image-width', $image->width)
    @section('image-height', $image->height)
    @section('revised', $image->updated_at)
@endif
@section('robots', 'noindex,nofollow')

@section('content')
    <div class="d-flex align-items-center">
        <x-title>Imagem: {{ $title }} <span class="badge bg-warning small">Histórico</span></x-title>
        <div class="ms-auto"></div>
        <a class="btn btn-sm btn-warning" href="{{ route('image', ['page' => $page]) }}">
            Cancelar
        </a>
    </div>


    <div class="row">
        <div class="col-12">
            <x-content>
                @include('layouts.partials.messages')

                <x-data-table src="{{ route('image.datatable', ['page' => $page]) }}" order="updated_at" order-dir="desc"
                    columns="{{ json_encode([
                        [
                            'title' => 'Data/Hora',
                            'data' => 'updated_at',
                            'className' => 'text-center',
                        ],
                        [
                            'title' => 'Imagem',
                            'data' => 'image',
                            'className' => 'text-center',
                            'orderable' => false,
                        ],
                        [
                            'title' => 'Usuário',
                            'data' => 'user.name',
                        ],
                        [
                            'data' => 'actions',
                            'width' => '20px',
                            'className' => 'text-center',
                            'orderable' => false,
                        ],
                    ]) }}" />



            </x-content>
        </div>
    </div>
@endsection
