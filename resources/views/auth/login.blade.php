@extends('layouts.auth')
@section('title', 'Cadastre-se')

@section('content')

    <div class="text-center mt-4">
        <h1>Bem-vindo</h1>
        <p class="lead">
            Faça seu login para continuar
        </p>
    </div>


    <div class="card">
        <div class="card-body">
            <div class="m-sm-3">
                @include('layouts.partials.messages')
                <form method="POST" action="{{ route('login.auth') }}">
                    @csrf

                    <x-form-group>
                        <x-input name="email" label="E-mail" placeholder="Digite seu e-mail" />
                        <x-input type="password" name="password" label="Senha" placeholder="Escolha uma senha" />
                        <small class="m-0">
                            <a href="{{ route('password.request') }}">Esqueceu sua senha?</a>
                        </small>
                        <div>
                            <div class="form-check align-items-center">
                                <input id="customControlInline" type="checkbox" class="form-check-input" value="remember-me"
                                    name="remember-me" checked>
                                <label class="form-check-label text-small" for="customControlInline">Manter
                                    conectado</label>
                            </div>
                        </div>
                    </x-form-group>

                    <div class="d-grid gap-2 mt-3">
                        <input type="submit" class='btn btn-lg btn-primary' value='Cadastrar'>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="text-center mb-3">
        Não tem uma conta? <a href='{{ route('register') }}'>Cadastre-se</a>
        | <a href="{{ route('home') }}">Voltar para o site</a>
    </div>
@endsection
