@extends('layouts.app')
@section('title', $title . ' (Imagem)')
@section('description', 'Imagem - ' . $title)
@if ($image)
    @section('image', $image->image_url)
    @section('image-type', $image->mime)
    @section('image-width', $image->width)
    @section('image-height', $image->height)
    @section('revised', $image->updated_at)
@endif

@section('content')
    <div class="d-flex align-items-center">
        <x-title>Imagem: {{ $title }}</x-title>
        <div class="ms-auto"></div>
        <a class="btn btn-sm btn-info m-1" href="{{ route('image.log', ['page' => $page]) }}">
            Histórico
        </a>
        @if ($image)
            <a class="btn btn-sm btn-primary m-1" href="{{ route('image.edit', ['page' => $page]) }}">
                Editar Imagem
            </a>
        @endif
    </div>

    @include('layouts.partials.messages')

    <div class="row">
        <div class="col-12 col-sm-6 col-md-8 col-lg-7 col-xl-8 col-xxl-9">
            <x-content>
                @if ($image)
                    <div class="text-center">
                        <img src="{{ $image->image_url }}" class="img-thumbnail rounded me-2 mb-2">
                    </div>
                @else
                    <p>Esta imagem não existe. Seja o primeiro a criar esta imagem!</p>

                    <div class='text-center'>
                        <a href="{{ route('image.edit', ['page' => $page]) }}" class="btn btn-primary btn-lg">
                            Adicionar Imagem
                        </a>
                    </div>
                @endif
            </x-content>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-5 col-xl-4 col-xxl-3">
            @if ($image)
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
                                            value="{{ $image->width }}px <b>x</b> {{ $image->height }}px" />
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </x-card>
            @endif

            @if (count($image_histories) > 0)
                <x-card title="Últimas Modificações" body-class="p-0">
                    <table class="table table-borderless my-0">
                        <tbody>
                            @foreach ($image_histories as $image)
                                <tr>
                                    <td class="bg-white">
                                        <x-change-card type="image" img="{{ $image->image_url }}"
                                            name="{{ $image->user->name }}" datetime="{{ $image->updated_at_formatted }}"
                                            avatar="{{ $image->user->avatar }}" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-card>
            @endif

        </div>
    </div>



@endsection
