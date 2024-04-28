@extends('settings.layouts.default')
@section('title', 'Cadastro')

@section('card')
    <x-card title="Informações Pessoais">
        @include('layouts.partials.messages')
        <form method="POST" action="{{ route('settings.account.update') }}">
            @csrf

            <div class="row">
                <div class="col-a">
                    <div class="mb-3">
                        <x-input name="name" label="Nome completo" value="{{ $user->name }}" placeholder="Digite seu nome"
                            required />
                    </div>
                    <div class="mb-3">
                        <x-input name="nickname" label="Apelido" value="{{ $user->nickname }}"
                            placeholder="Como gostaria de ser chamado?" max=20 required>
                            <small>É a forma como seu nome será exibido publicamente</small>
                        </x-input>
                    </div>
                    <div class="mb-3">
                        <x-input type="date" name="birth_date" label="Data de Nascimento"
                            value="{{ $user->birth_date }}" />
                    </div>
                    <div class="mb-3">
                        <x-input type="textarea" rows=4 name="biography" label="Biografia" value="{{ $user->biography }}"
                            placeholder="Conte um pouco sobre você" />
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-12 col-sm-9">
                                <x-input name="city" label="Cidade" value="{{ $user->city }}"
                                    placeholder="Cidade onde moro" />
                            </div>
                            <div class="col-12 col-sm-3">
                                <x-input name="state" label="Estado" max=2 value="{{ $user->state }}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-b">
                    <x-input type="photo" name="photo" label="Foto de Perfil" value="{{ $user->photo }}" />
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <h5 class="card-title mb-4 text-truncate">Informações sobre meu clube</h5>
                    <div class="mb-3">
                        <x-input name="association" label="Associação/Missão" value="{{ $user->association }}"
                            placeholder="Digite o campo de atuação" />
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-12 col-md-4 col-xl-3 d-flex align-items-center">
                                <x-input type="checkbox" name="is_pathfinder" label="Sou Desbravador"
                                    value="{{ $user->is_pathfinder }}" />
                            </div>
                            <div class="col-12 col-md-8 col-xl-9">
                                <x-input name="pathfinder_club" label="Clube de Desbravadores"
                                    value="{{ $user->pathfinder_club }}"
                                    placeholder="Digite o nome do seu clube de desbravadores" />
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-12 col-md-4 col-xl-3 d-flex align-items-center">
                                <x-input type="checkbox" name="is_adventurer" label="Sou Aventureiro"
                                    value="{{ $user->is_adventurer }}" />
                            </div>
                            <div class="col-12 col-md-8 col-xl-9">
                                <x-input name="adventurer_club" label="Clube de Aventureiros"
                                    value="{{ $user->adventurer_club }}"
                                    placeholder="Digite o nome do seu clube de aventureiros" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </x-card>
@endsection

@push('scripts')
    <script>
        document.getElementById('pathfinder_club').onchange = function() {
            club_change(this, document.getElementById('is_pathfinder'))
        };
        document.getElementById('adventurer_club').onchange = function() {
            club_change(this, document.getElementById('is_adventurer'))
        };
        document.getElementById('is_pathfinder').onchange = function() {
            is_club_change(this, document.getElementById('pathfinder_club'))
        };
        document.getElementById('is_adventurer').onchange = function() {
            is_club_change(this, document.getElementById('adventurer_club'))
        };

        function club_change(input, checkbox) {
            if (input.value === '') {
                checkbox.checked = false;
            } else {
                checkbox.checked = true;
            }
        }

        function is_club_change(checkbox, input) {
            if (!checkbox.checked) {
                input.value = '';
            }
        }
    </script>
@endpush

@push('styles')
    <style>
        @media screen and (min-width: 790px) {
            .col-a {
                width: calc(100% - 220px);
            }

            .col-b {
                width: 220px;
            }
        }
    </style>
@endpush
