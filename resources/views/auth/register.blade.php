@extends('layouts.auth')
@section('title', 'Cadastre-se')

@section('content')

    <div class="text-center mt-4">
        <h1 class="h2">Faça parte da comunidade</h1>
        <p class="lead">
            Registre-se agora e comece a contribuir para o nosso site!
        </p>
    </div>


    <div class="card">
        <div class="card-body">
            <div class="m-sm-3">
                @include('layouts.partials.messages')
                <form method="POST" action="{{ route('registro') }}">
                    @csrf

                    <x-form-group>
                        <x-input name="name" label="Nome completo" placeholder="Digite seu nome" />
                        <x-input name="email" label="E-mail" placeholder="Digite seu e-mail" />
                        <x-input type="password" name="password" label="Senha" placeholder="Escolha uma senha" />
                        <x-input type="password" name="password_confirmation" label="Confirme a Senha"
                            placeholder="Confirme sua senha" />
                        <x-input type="captcha" name="captcha" label="Digite os caracteres da imagem" />
                    </x-form-group>

                    <div class="d-grid gap-2 mt-3">
                        <input type="submit" class='btn btn-lg btn-primary' value='Cadastrar'>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="text-center mb-3">
        Já tem uma conta? <a href='{{ route('login') }}'>Acesse</a>
        | <a href="{{ route('validar-email') }}">Tenho um código</a>
        | <a href="{{ route('inicio') }}">Voltar para o site</a>
    </div>
@endsection
