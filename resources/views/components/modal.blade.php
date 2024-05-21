<div class="modal fade" id="{{ $id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body {{ $bodyClass }}">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @if ($script)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = document.getElementById('{{ $id }}').addEventListener('shown.bs.modal',
                    function() {
                        {{ $script }}
                    });
            });
        </script>
    @endif
@endpush
