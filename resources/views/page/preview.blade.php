@extends('layouts.popup')
@section('title', 'Visualizar Página')

@section('content')
    {!! $content !!}
@endsection

@push('scripts')
    <script>
        window.onload = function() {
            var links = document.querySelectorAll('a');

            links.forEach(function(link) {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                });
            });

            var elementsWithOnClick = document.querySelectorAll('[onclick]');
            elementsWithOnClick.forEach(function(element) {
                element.onclick = function(event) {
                    event.preventDefault();
                };
            });
        };
    </script>
@endpush
