@extends('layouts.app')
@section('title', $title . ' (Edição de Imagem)')
@section('description', 'Imagem - ' . $title)
@section('robots', 'noindex,nofollow')
@if ($image)
    @section('image', $image->full_url)
    @section('image-type', $image->mime)
    @section('image-width', $image->width)
    @section('image-height', $image->height)
    @section('revised', $image->updated_at)
@endif

@section('content')
    <x-title>Imagem: {{ $title }} <span class="badge bg-warning">Edição</span></x-title>

    @if ($image)
        <div class="row">
            <div class="col-12 col-sm-8 col-md-8 col-lg-8 col-xl-9 col-xxl-10">
                <x-content>

                    <div class="card-actions float-end mb-3">
                        <a class="btn btn-sm btn-warning" href="{{ route('image', ['page' => $page]) }}">
                            Cancelar
                        </a>
                    </div>

                    <div style="clear: both"></div>

                    @include('layouts.partials.messages')

                    <div id="image-preview" class='text-center mb-4'>
                        <span class="drag">Você pode arrastar a imagem e soltar nesta área</span>
                        <span class="drop">Solte a imagem</span>
                    </div>

                    <div class='text-center'>
                        <button class="btn btn-primary btn-lg" id="send-image">
                            Nova Imagem
                        </button>
                    </div>
                    <input type="file" id="input-image" class="d-none" accept="image/*">
                    <form id="form" method="POST" action="{{ route('image.update', ['page' => $page]) }}"
                        class="d-none">
                        @csrf
                        <input type="text" id="image" name="image" class="d-none">
                    </form>


                </x-content>
            </div>
            <div class="col-12 col-sm-4 col-md-4 col-lg-4 col-xl-3 col-xxl-2">
                <x-card title="Imagem Atual" img="{{ $image->full_url }}">
                    <div class='text-center'>
                        <form id="form" method="POST" action="{{ route('image.delete', ['page' => $page]) }}">
                            @csrf
                            @method('DELETE')
                            <input type="submit" class="btn btn-danger" value="Remover Imagem">
                        </form>
                    </div>
                </x-card>
            </div>
        </div>
    @else
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

                    <div id="image-preview" class='text-center mb-4'>
                        <span class="drag">Você pode arrastar a imagem e soltar nesta área</span>
                        <span class="drop">Solte a imagem</span>
                    </div>

                    <div class='text-center'>
                        <button class="btn btn-primary btn-lg" id="send-image">
                            Adicionar Imagem
                        </button>
                    </div>
                    <input type="file" id="input-image" class="d-none" accept="image/*">
                    <form id="form" method="POST" action="{{ route('image.update', ['page' => $page]) }}"
                        class="d-none">
                        @csrf
                        <input type="text" id="image" name="image" class="d-none">
                    </form>


                </x-content>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.getElementById('image-preview').addEventListener('click', function(event) {
                document.getElementById('input-image').click();
            });
            document.getElementById('send-image').addEventListener('click', function(event) {
                document.getElementById('input-image').click();
            });

            document.getElementById('input-image').addEventListener('change', function() {
                const file = document.getElementById('input-image').files[0];
                handleFile(file);
            });

            document.getElementById('image-preview').addEventListener('drop', function(event) {
                event.preventDefault();
                event.stopPropagation();

                const file = event.dataTransfer.files[0];
                handleFile(file);
            });

            document.getElementById('image-preview').addEventListener('dragover', function(event) {
                event.preventDefault();
                event.stopPropagation();
            });

            document.getElementById('image-preview').addEventListener('dragenter', function(event) {
                document.getElementById('image-preview').classList.add('dragenter');
            });

            document.getElementById('image-preview').addEventListener('dragleave', function(event) {
                document.getElementById('image-preview').classList.remove('dragenter');
            });

            function handleFile(file) {
                const reader = new FileReader();
                const max_size = {{ $max_size }};

                reader.onload = function(e) {
                    const img = new Image();
                    img.src = e.target.result;

                    img.onload = function() {
                        if (file.type === 'image/svg+xml') {
                            document.getElementById('image-preview').innerHTML =
                                `<img src="${img.src}" style='max-width: 100%'>`;
                            document.getElementById('image').value = img.src;
                        } else {
                            let width = 0;
                            let height = 0;

                            if (img.width > img.height) {
                                height = max_size;
                                width = height / img.height * img.width;
                            } else {
                                width = max_size;
                                height = width / img.width * img.height;
                            }

                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');
                            canvas.width = width;
                            canvas.height = height;

                            ctx.drawImage(img, 0, 0, width, height);
                            let base64 = canvas.toDataURL();

                            document.getElementById('image-preview').innerHTML =
                                `<img src="${base64}" style='max-width: 100%'>`;
                            document.getElementById('image').value = base64;
                        }

                        document.getElementById('send-image').disabled = true;
                        document.getElementById('send-image').innerText =
                            "Aguarde... Enviando Imagem";

                        document.getElementById('form').submit();
                    };
                };

                reader.readAsDataURL(file);
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        #image-preview {
            cursor: pointer;
            min-height: 200px;
            background: #EEE;
            border: 3px dashed #BBB;
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        #image-preview.dragenter {
            border-color: #fcb92c;
            background: #feffe3;
        }

        #image-preview span {
            color: #888;
            font-size: 20px;
        }

        #image-preview span.drag {
            display: block;
        }

        #image-preview span.drop {
            display: none;
        }

        #image-preview.dragenter span {
            color: #fcb92c;
        }

        #image-preview.dragenter span.drag {
            display: none;
        }

        #image-preview.dragenter span.drop {
            display: block;
        }
    </style>
@endpush
