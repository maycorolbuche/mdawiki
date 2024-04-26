@extends('settings.layouts.default')
@section('title', 'Cadastro')
@section('subtitle', 'Cadastroaa')

@section('card')
    <x-card title="Informações Pessoais">
        @include('layouts.partials.messages')
        <form>
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <x-input name="name" label="Nome completo" value="{{ $user->name }}"
                            placeholder="Digite seu nome" />
                    </div>
                    <div class="mb-3">
                        <x-input name="nickname" label="Apelido" value="{{ $user->nickname }}"
                            placeholder="Como gostaria de ser chamado?">
                            <small>É a forma como seu nome será exibido publicamente</small>
                        </x-input>
                    </div>
                    <div class="mb-3">
                        <x-input type="textarea" rows=4 name="biografy" label="Biografia" value="{{ $user->biografy }}"
                            placeholder="Conte um pouco sobre você" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <img alt="Charles Hall" src="img/avatars/avatar.jpg" class="rounded-circle img-responsive mt-2"
                            width="128" height="128" />
                        <div class="mt-2">
                            <span class="btn btn-primary"><i class="fas fa-upload"></i> Upload</span>
                        </div>
                        <small>For best results, use an image at least 128px by
                            128px in .jpg format</small>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save changes</button>
        </form>
    </x-card>
@endsection
