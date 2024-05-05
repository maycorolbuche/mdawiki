@extends('layouts.app')
@section('title', $title . ' (Imagem)')
@section('description', 'Imagem - ' . $title)
@section('image', $image->full_url)
@section('image-type', $image->mime)
@section('image-width', $image->width)
@section('image-height', $image->height)
@section('revised', $image->updated_at)

@section('content')
    <x-title>Imagem: {{ $title }}</x-title>



    @include('layouts.partials.messages')

    @if ($image)
        <div class="row">
            <div class="col-12 col-sm-6 col-md-8 col-lg-7 col-xl-8 col-xxl-9">
                <x-content>

                    <div class="card-actions float-end mb-3">
                        <a class="btn btn-sm btn-info" href="{{ route('image.log', ['page' => $page]) }}">
                            Histórico
                        </a>
                        <a class="btn btn-sm btn-primary" href="{{ route('image.edit', ['page' => $page]) }}">
                            Editar Imagem
                        </a>
                    </div>

                    <div class="text-center">
                        <img src="{{ $image->full_url }}" class="img-thumbnail rounded me-2 mb-2">
                    </div>
                </x-content>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-5 col-xl-4 col-xxl-3">
                <x-card title="Informações da Imagem" body-class="p-0">
                    <table class="table table-borderless my-0">
                        <tbody>
                            <tr>
                                <td class="bg-white">
                                    <x-info-card icon="file" title="Tipo" value="{{ $image->mime }}" />
                                </td>
                            </tr>
                            @if ($image->width && $image->height)
                                <tr>
                                    <td class="bg-white">
                                        <x-info-card icon="move" title="Dimensões"
                                            value="{{ $image->width }} x {{ $image->height }}" />
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </x-card>

                <x-card title="Últimas Modificações" body-class="p-0">
                    <table class="table table-borderless my-0">
                        <tbody>
                            @foreach ($image_histories as $image)
                                <tr>
                                    <td class="bg-white">
                                        <x-change-card type="image" img="{{ $image->full_url }}"
                                            name="{{ $image->user->name }}" datetime="{{ $image->updated_at_formatted }}"
                                            avatar="{{ $image->user->avatar }}" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-card>

            </div>
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <x-content>

                    <div class="card-actions float-end mb-3">
                        <a class="btn btn-sm btn-info" href="{{ route('image.log', ['page' => $page]) }}">
                            Histórico
                        </a>
                    </div>

                    <p>Esta imagem não existe. Seja o primeiro a criar esta imagem!</p>

                    <div class='text-center'>
                        <a href="{{ route('image.edit', ['page' => $page]) }}" class="btn btn-primary btn-lg">
                            Adicionar Imagem
                        </a>
                    </div>
                </x-content>
            </div>
        </div>
    @endif


@endsection
