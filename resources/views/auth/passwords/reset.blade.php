@extends('layouts.auth')

@section('content')
    <div class="text-center mt-4">
        <h1 class="h2">Recuperação de acesso</h1>
    </div>


    <div class="card">
        <div class="card-body">
            <div class="m-sm-3">
                @include('layouts.partials.messages')
                <form method="post" action="{{ route('password.reset') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <x-form-group>
                        <x-input name="email" label="E-mail" placeholder="Confirme seu e-mail" />
                        <x-input type="password" name="password" label="Senha" placeholder="Escolha uma nova senha" />
                        <x-input type="password" name="password_confirmation" label="Confirme a nova Senha"
                            placeholder="Confirme sua senha" />
                    </x-form-group>

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
