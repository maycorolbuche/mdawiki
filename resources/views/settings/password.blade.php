@extends('layouts.app')
@section('title', 'Alterar Senha')

@section('content')
    <x-title>Alterar Senha</x-title>

    <div class="row">
        <div class="col-md-3 col-xl-2">
            @include('settings.partials.sidebar')
        </div>

        <div class="col-md-9 col-xl-10">

            <x-card title="Password">
                <form>
                    <div class="mb-3">
                        <label class="form-label" for="inputPasswordCurrent">Current
                            password</label>
                        <input type="password" class="form-control" id="inputPasswordCurrent">
                        <small><a href="#">Forgot your password?</a></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="inputPasswordNew">New
                            password</label>
                        <input type="password" class="form-control" id="inputPasswordNew">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="inputPasswordNew2">Verify
                            password</label>
                        <input type="password" class="form-control" id="inputPasswordNew2">
                    </div>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </form>

            </x-card>

        </div>
    </div>
@endsection
