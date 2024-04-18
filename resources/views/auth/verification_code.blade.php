@extends('layouts.auth')
@section('title', 'Cadastre-se')

@section('content')

    <div class="text-center mt-4">
        <h1 class="h2">Valide seu e-mail</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="m-sm-3">
                @include('layouts.partials.messages')

                @if ($id_user_hash == '')
                    <form method="POST" action="{{ route('validar-email') }}">
                        @csrf
                        <x-input name="email" label="Enviaremos um código para o seu e-mail!"
                            placeholder="Digite seu e-mail" />
                        <div class="d-grid gap-2 mt-3">
                            <input type="submit" class='btn btn-lg btn-primary' value='Enviar'>
                        </div>
                    </form>
                @else
                    @push('styles')
                        <style>
                            [name=verification_code] {
                                text-align: center;
                                letter-spacing: 20px;
                                font-size: 30px;
                                font-weight: bold;
                            }
                        </style>
                    @endpush
                    Foi enviado um código para o seu e-mail! Digite o código no campo abaixo:
                    <form method="POST" action="{{ route('validar-email', ['id_user_hash' => $id_user_hash]) }}">
                        @csrf

                        <x-form-group>
                            <x-input name="verification_code" placeholder="000000" />
                        </x-form-group>

                        <div class="d-grid gap-2 mt-3">
                            <input type="submit" class='btn btn-lg btn-primary' value='Validar'>
                        </div>
                    </form>
                    <br>Ainda não recebeu o código? <a href='{{ route('validar-email') }}'>Enviar novamente</a>
                @endif
            </div>
        </div>
    </div>
    <div class="text-center mb-3">
        <a href='{{ route('login') }}'>Acessar minha conta</a>
        | <a href='{{ route('registro') }}'>Cadastre-se</a>
        | <a href="{{ route('inicio') }}">Voltar para o site</a>
    </div>
@endsection
