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
                        <x-input type="password" name="password" label="Senha" placeholder="Escolha uma senha">
                            <small>
                                <a href="{{ route('password.request') }}">Esqueceu sua senha?</a>
                            </small>
                        </x-input>
                        <x-input type="checkbox" name="remember" label="Manter conectado" />
                    </x-form-group>

                    <div class="d-grid gap-2 mt-3">
                        <input type="submit" class='btn btn-lg btn-primary' value='Entrar'>
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
