@extends('layouts.app')
@section('title', 'Página Teste')
@section('description', 'Dsadsa sdfsdfds Teste')
@section('keywords', 'Página Teste')
@section('image', 'https://mda.wiki.br/site/@imgs_wiki_cp/imagem@logo_amigo.png')
@section('image-type', 'image/png')
@section('image-width', '150')
@section('image-height', '150')
@section('revised', '2024-04-10 10:06:33')

@section('content')
    <x-title>{{ $title }} <span class="badge bg-warning small">Edição</span></x-title>

    <div class="row" style="flex: auto;">
        <form class="col-12 d-flex flex-column" id="form" method="POST"
            action="{{ route('page.update', ['page' => $page]) }}">
            <div id="editor-container" class="editor" style="flex: auto;">
                <textarea id="editor" name="content" class="editor d-none">
                {{ $data->content_editor ?? '' }}
                </textarea>
            </div>

            <x-card body-class="p-2">
                <div class="row pb-2">
                    <x-input id="categories" label="Categorias" type="tags"
                        data-server="{{ route('json.categories_input', ['page' => $page]) }}">
                        @foreach ($data->page_categories as $category)
                            <option selected value="{{ $category->category_title }}">
                                {{ $category->category_title }}</option>
                        @endforeach
                    </x-input>
                </div>

                <div class="card-actions float-end">
                    @csrf
                    <input type="submit" class="btn btn-info" value="Salvar">
                    <input type="button" class="btn btn-success" value="Visualizar" data-bs-toggle="modal"
                        data-bs-target="#previewModal">
                    <a class="btn btn-warning" href="{{ route('page', ['page' => $page]) }}">Cancelar</a>
                </div>
            </x-card>
        </form>
    </div>

    <x-modal id="previewModal" title="Visualizar Página" script="loadModal();" body-class="m-0 p-0">
        <div id="loading-message" class="w-100 h-100 position-absolute bg-white" style="display: none;">
            <div class="d-flex align-items-center justify-content-center w-100 h-100">
                <div class="spinner-grow text-primary me-2" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
        </div>
        <iframe id="preview" class="w-100" style="height: calc(100vh - 120px);"></iframe>
        @push('scripts')
            <script>
                function loadModal() {
                    const url = '{{ route('page.preview') }}';
                    const data = {
                        _token: '{{ csrf_token() }}',
                        content: document.getElementById("editor").value
                    };
                    const loadingMessage = document.getElementById('loading-message');

                    loadingMessage.style.display = 'block';

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => response.text())
                        .then(html => {
                            const iframe = document.getElementById('preview');
                            iframe.contentDocument.open();
                            iframe.contentDocument.write(html);
                            iframe.contentDocument.close();

                            loadingMessage.style.display = 'none';
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            loadingMessage.style.display = 'none';
                        });
                }
            </script>
        @endpush
    </x-modal>

@endsection

@push('scripts')
    <script>
        document.querySelector('.content').style.display = 'flex';
        document.querySelector('.content').style.alignItems = 'stretch';
        document.querySelector('.container-fluid').style.display = 'flex';
        document.querySelector('.container-fluid').style.flexDirection = 'column';

        var editor;
        document.addEventListener("DOMContentLoaded", () => {
            editor = new FroalaEditor('#editor', {
                language: 'pt_br',
                height: 100,
                pastePlain: true,

                toolbarButtons: [
                    [
                        'undo', 'redo',
                        '|', 'selectAll',
                        '|',
                    ],
                    [
                        'bold', 'italic', 'underline', 'strikeThrough',
                        '|', 'insertLink', 'convertLink',
                        '|', 'subscript', 'superscript',
                        '|', 'specialCharacters',
                        '|', 'clearFormatting',
                        '|',
                    ],
                    [
                        'paragraphFormat',
                        'fontSize',
                        '|', 'alignLeft', 'alignCenter', 'alignRight', 'alignJustify',
                        '|', 'quote',
                        '|', 'formatOL', 'formatUL',
                        '|', 'outdent', 'indent',
                        '|', 'insertHR',
                        '|',
                    ],
                    [
                        /*'table',*/
                        'insertTable',
                        '|', 'image',
                    ],
                    [
                        //'wirisEditor', 'wirisChemistry',
                        'components',
                        '|',
                    ],
                ],
                quickInsertEnabled: false,

                /* Lists - OL / UL */
                listAdvancedTypes: true,
                formatOLBeginOption: true,

                /* Font / Paraghaph */
                fontFamilySelection: true,
                fontSizeSelection: true,
                paragraphFormatSelection: true,

                /* Table */
                tableResizerOffset: 10,
                tableResizingLimit: 50,
                tableEditButtons: ['tableHeader',
                    '|', 'tableRows', 'tableColumns', 'tableStyle',
                    '-',
                    'tableCells', 'tableCellBackground', 'tableCellVerticalAlign',
                    'tableCellHorizontalAlign',
                    '|', 'tableRemove',
                ],
                tableStyles: {
                    table: 'Destacado',
                    dynamic: 'Dinâmica',
                    //table table-striped table-sm table-hover
                },
                tableMultipleStyles: false,
                tableCellStyles: {
                    border: 'Borda'
                },

                /* Image */
                imageEditButtons: [
                    'imageAlign',
                    'imageCaption',
                    'imageDisplay',
                    '|', 'imageRemove',
                ],
                imageManagerLoadURL: "{{ route('json.images') }}",
                imageManagerNewImage: "{{ route('images') }}",

                linkAutoPrefix: '',
                linkList: [],
                linkEditButtons: ['linkEdit', 'linkRemove'],
                linkConvertEmailAddress: false,

                //htmlAllowedTags: [' .* '],
                //htmlAllowedAttrs: [' .* '],
                //htmlAllowedEmptyTags: ['mprescripts', 'none'],

                events: {
                    contentChanged: function() {
                        //
                    },
                }
            });


            setTimeout(function() {
                resizeEditor();
            }, 100);
            console.log(editor);
        });




        FroalaEditor.DefineIcon('insertMore', {
            NAME: 'insertMore',
            SVG_KEY: "insertMore"
        })
        FroalaEditor.RegisterCommand('components', {
            title: 'Inserir Elementos',
            icon: 'insertMore',
            type: 'dropdown',
            focus: false,
            undo: false,
            refreshAfterCallback: true,
            options: {
                'v1': 'Especialidade de Desbravadores',
                'v2': 'Especialidade de Aventureiros',
                'v3': 'Redirecionamento'
            },
            callback: function(cmd, val) {
                console.log(val);
                if (val == "v1") {
                    const tableHTML = `
                        <div class="custom-table" style='background:red;'>
                            <div class="table-header">
                                <div contenteditable="false" class="table-header-cell">Header 1</div>
                                <div contenteditable="false" class="table-header-cell">Header 2</div>
                                <div contenteditable="false" class="table-header-cell">Header 3</div>
                                <div contenteditable="false" class="table-header-cell"><button class="delete-table-btn">Delete</button></div>
                            </div>
                            <div class="table-body">
                                <div class="table-row">
                                    <div class="table-cell">Header 1</div>
                                    <div class="table-cell">Header 2</div>
                                    <div class="table-cell">Header 3</div>
                                </div>
                                <!-- Mais linhas podem ser adicionadas aqui conforme necessário -->
                            </div>
                        </div>
                    `;
                    this.html.insert(tableHTML);
                }
            },
            refresh: function($btn) {
                console.log('do refresh');
            },
            refreshOnShow: function($btn, $dropdown) {
                console.log('do refresh when show');
            }
        })



        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('delete-table-btn')) {
                const table = event.target.closest('.custom-table');
                table.parentNode.removeChild(table);
            }
        });



        function resizeEditor() {
            editor.opts.height = document.getElementById('editor-container').offsetHeight - document
                .querySelector(
                    '.fr-toolbar').offsetHeight - 40;
            editor.destroy();
            editor = new FroalaEditor('#editor', editor.opts);
        }
    </script>
@endpush




@push('styles')
    <style>
        [id^="dropdown-menu-formatOLOptions-"] [data-param1="start"] {
            border-top: 1px solid #ebebeb;
        }

        .fr-link-insert-layer .fr-checkbox-line,
        button[data-cmd="linkList"] {
            display: none !important;
        }
    </style>
@endpush
