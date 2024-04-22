@extends('layouts.auth')
@section('title', 'Cadastre-se')

@section('content')

    <div class="text-center mt-4">
        <h1 class="h2">Recuperação de acesso</h1>
    </div>


    <div class="card">
        <div class="card-body">
            <div class="m-sm-3">
                @include('layouts.partials.messages')
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <x-form-group>
                        <x-input name="email" label="E-mail" placeholder="Digite seu e-mail"
                            value="{{ $email ?? (session('email') ?? old('email')) }}" />
                    </x-form-group>

                    Digite seu e-mail para receber as instruções de recuperação de senha.

                    <div class="d-grid gap-2 mt-3">
                        <input type="submit" class='btn btn-lg btn-primary' value='Confirmar'>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="text-center mb-3">
        Já tem a senha? <a href='{{ route('login') }}'>Acesse</a>
        | <a href="{{ route('home') }}">Voltar para o site</a>
    </div>
@endsection
