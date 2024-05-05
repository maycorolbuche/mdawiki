@extends('layouts.app')
@section('title', $title . ' (Imagem)')
@section('description', 'Dsadsa sdfsdfds Teste')
@section('keywords', 'Página Teste')
@section('image', 'https://mda.wiki.br/site/@imgs_wiki_cp/imagem@logo_amigo.png')
@section('image-type', 'image/png')
@section('image-width', '150')
@section('image-height', '150')
@section('revised', '2024-04-10 10:06:33')

@section('content')
    <x-title>Imagem: {{ $title }}</x-title>

    <div class="row">
        <div class="col-12">
            <x-content>

                <!--
                        <div class="card">
                        <img class="card-img-top" src="https://demo.adminkit.io/img/photos/unsplash-2.jpg" alt="Unsplash">
                        <div class="card-header">
                        <h5 class="card-title mb-0">Card with image and button</h5>
                        </div>
                        <div class="card-body">
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the
                        card's content.</p>
                        <a href="#" class="btn btn-primary">Go somewhere</a>
                        </div>
                        </div>
                        -->
                <p>Esta imagem não existe. Seja o primeiro a criar esta imagem!</p>

                @include('layouts.partials.messages')

                @auth
                    <div id="image-preview" class='text-center mb-4'></div>

                    <div class='text-center'>
                        <button class="btn btn-primary btn-lg" id="send-image">
                            Adicionar Imagem
                        </button>
                    </div>
                    <input type="file" id="input-image" class="d-none" accept="image/*">
                    <form id="form" method="POST" action="{{ route('image.update', ['page' => $page]) }}" class="d-none">
                        @csrf
                        <input type="text" id="image" name="image" class="d-none">
                    </form>
                @endauth

            </x-content>
        </div>
    </div>
@endsection

@push('scripts')
    @auth
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                document.getElementById('send-image').addEventListener('click', function(event) {
                    document.getElementById('input-image').click();
                });

                document.getElementById('input-image').addEventListener('change', function() {
                    const file = document.getElementById('input-image').files[0];
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
                });
            });
        </script>
    @endauth
@endpush
