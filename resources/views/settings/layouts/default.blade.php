@extends('layouts.app')

@section('content')
    <x-title>@yield('title')</x-title>

    <div class="row">
        <div class="col-sm-4 col-md-3 col-xxl-2">
            @include('settings.partials.sidebar')
        </div>

        <div class="col-sm-8 col-md-9 col-xxl-10">
            @yield('card')
        </div>
    </div>
@endsection
