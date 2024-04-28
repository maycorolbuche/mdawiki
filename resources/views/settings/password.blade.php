@extends('settings.layouts.default')
@section('title', 'Alteração de Senha')

@section('card')
    <x-card title="Alteração de Senha">
        @include('layouts.partials.messages')
        <form method="POST" action="{{ route('settings.password.update') }}">
            @csrf

            <div class="row">
                <div class="col-12">
                    <div class="mb-3">
                        <x-input type="password" name="current_password" label="Senha atual" placeholder="Digite a Senha Atual"
                            required />
                    </div>
                    <div class="mb-3">
                        <x-input type="password" name="password" label="Nova Senha" placeholder="Escolha uma senha"
                            required />
                    </div>
                    <div class="mb-3">
                        <x-input type="password" name="password_confirmation" label="Confirme a Nova Senha"
                            placeholder="Confirme sua senha" required />
                    </div>
                    <div class="mb-3">
                        <x-input type="captcha" name="captcha" label="Digite os caracteres da imagem" />
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </x-card>
@endsection
