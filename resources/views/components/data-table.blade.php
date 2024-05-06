<div class="table-responsive">
    <table id="{{ $id }}" class="table table-striped table-condensed table-hover display" cellspacing="0"
        width="100%">
        <thead>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var table = document.getElementById('{{ $id }}');
            var columns = {!! html_entity_decode($columns) !!};
            var order = {!! html_entity_decode($order) ?: '[]' !!};

            var dataTable = new DataTable(table, {
                serverSide: true,
                processing: true,
                ajax: '{{ $src }}',
                columns: columns,
                order: order,
                createdRow: function(row, data, index) {
                    {!! html_entity_decode($createdRow) !!}
                }
            });
        });
    </script>
@endpush
