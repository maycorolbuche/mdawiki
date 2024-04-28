@extends('settings.layouts.default')
@section('title', 'Alteração de E-mail')

@section('card')
    <x-card title="Alteração de E-mail">
        @include('layouts.partials.messages')

        <div class="row">
            <div class="col-12">
                <div class="mb-3">
                    <span>
                        Você está registrado em nosso site com o e-mail:
                        <br><br><a href='mailto:{{ auth()->user()->email }}'>{{ auth()->user()->email }}</a>
                        <br><br>Para fazer a alteração do seu e-mail de cadastro, entre em contato conosco através do e-mail
                        <a href="mailto:{{ env('MAIL_REPLY_TO') }}">{{ env('MAIL_REPLY_TO') }}</a>.
                    </span>
                </div>
            </div>
        </div>

    </x-card>
@endsection
