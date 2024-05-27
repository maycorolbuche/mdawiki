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

                <div class="card-actions float-end">
                    @csrf
                    <input type="submit" class="btn btn-info" value="Salvar">
                    <input type="button" class="btn btn-success" value="Visualizar" data-bs-toggle="modal"
                        data-bs-target="#previewModal">
                    <a class="btn btn-warning" href="{{ route('page', ['page' => $page]) }}">
                        Cancelar
                    </a>
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
                        'custom_insert', 'myButton',
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

                //imageEditButtons: ['wirisEditor', 'wirisChemistry'],
                //htmlAllowedTags: [' .* '],
                //htmlAllowedAttrs: [' .* '],
                //htmlAllowedEmptyTags: ['mprescripts', 'none'],

                events: {
                    contentChanged: function() {
                        /* let html = editor.html.get();
                         console.log(html);
                         html_transform = transformLinks(html);
                         if (html != html_transform) {
                             editor.html.set(html_transform);
                         }*/
                    },
                }
            });


            setTimeout(function() {
                resizeEditor();
            }, 100);
            console.log(editor);
        });





        FroalaEditor.POPUP_TEMPLATES["customPlugin.popup"] = '[_BUTTONS_][_CUSTOM_LAYER_]';
        FroalaEditor.PLUGINS.customPlugin = function(editor) {
            // Create custom popup.
            function initPopup() {
                // Popup buttons.
                var popup_buttons = '';

                // Create the list of buttons.
                popup_buttons += '<div class="fr-buttons">';
                popup_buttons += editor.button.buildList(['custom_insert', '|', 'custom_insert']);
                popup_buttons += '</div>';

                return editor.popups.create('customPlugin.popup', {
                    buttons: popup_buttons,
                    custom_layer: '<div class="custom-layer">Hello World!</div>'
                });
            }

            // Show the popup
            function showPopup() {
                // Get the popup object defined above.
                var $popup = editor.popups.get('customPlugin.popup');

                // If popup doesn't exist then create it.
                // To improve performance it is best to create the popup when it is first needed
                // and not when the editor is initialized.
                if (!$popup) $popup = initPopup();

                // Set the editor toolbar as the popup's container.
                editor.popups.setContainer('customPlugin.popup', editor.$tb);

                // This will trigger the refresh event assigned to the popup.
                // editor.popups.refresh('customPlugin.popup');

                // This custom popup is opened by pressing a button from the editor's toolbar.
                // Get the button's object in order to place the popup relative to it.
                var $btn = editor.$tb.find('.fr-command[data-cmd="myButton"]');

                // Set the popup's position.
                var left = $btn.offset().left + $btn.outerWidth() / 2;
                var top = $btn.offset().top + (editor.opts.toolbarBottom ? 10 : $btn.outerHeight() - 10);

                // Show the custom popup.
                // The button's outerHeight is required in case the popup needs to be displayed above it.
                editor.popups.show('customPlugin.popup', left, top, $btn.outerHeight());
            }

            // Hide the custom popup.
            function hidePopup() {
                editor.popups.hide('customPlugin.popup');
            }

            // Methods visible outside the plugin.
            return {
                showPopup: showPopup,
                hidePopup: hidePopup
            }
        }

        FroalaEditor.DefineIcon('buttonIcon', {
            NAME: 'star',
            SVG_KEY: 'star'
        })
        FroalaEditor.RegisterCommand('myButton', {
            title: 'Show Popup',
            icon: 'buttonIcon',
            undo: false,
            focus: false,
            plugin: 'customPlugin',
            callback: function() {
                this.customPlugin.showPopup();
            }
        });

        FroalaEditor.DefineIcon('custom_insert', {
            NAME: 'plus',
            SVG_KEY: 'add'
        });
        FroalaEditor.RegisterCommand('custom_insert', {
            title: 'Insert HTML',
            focus: true,
            undo: true,
            refreshAfterCallback: true,
            callback: function() {
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
                                    <div class="table-cell"><button class="delete-row-btn">Delete</button></div>
                                </div>
                                <!-- Mais linhas podem ser adicionadas aqui conforme necessário -->
                            </div>
                        </div>
                    `;
                this.html.insert(tableHTML);
            }
        });



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

        function transformLinks(content) {
            const urlRegex = /https?:\/\/[^\s]+/g;

            // Função para criar um link HTML a partir de uma URL
            function createLink(url) {
                return `<a href="${url}" target="_blank">${url}</a>`;
            }

            // Criar um contêiner temporário para manipular o HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = content;

            // Função para verificar e transformar links
            function checkAndTransform(node) {
                if (node.nodeType === Node.TEXT_NODE) {
                    const urls = node.textContent.match(urlRegex);
                    if (urls) {
                        const parent = node.parentNode;
                        let remainingText = node.textContent;
                        urls.forEach(url => {
                            const urlIndex = remainingText.indexOf(url);
                            const beforeUrl = remainingText.slice(0, urlIndex);
                            const afterUrl = remainingText.slice(urlIndex + url.length);

                            if (beforeUrl) {
                                parent.insertBefore(document.createTextNode(beforeUrl), node);
                            }

                            parent.insertBefore(createLinkElement(url), node);
                            remainingText = afterUrl;
                        });

                        if (remainingText) {
                            parent.insertBefore(document.createTextNode(remainingText), node);
                        }

                        parent.removeChild(node);
                    }
                } else if (node.nodeType === Node.ELEMENT_NODE && node.nodeName !== 'A') {
                    Array.from(node.childNodes).forEach(child => checkAndTransform(child));
                }
            }

            // Função para criar um elemento de link
            function createLinkElement(url) {
                const link = document.createElement('a');
                link.href = url;
                link.textContent = url;
                link.target = '_blank';
                return link;
            }

            // Percorrer e transformar links no conteúdo do contêiner temporário
            Array.from(tempDiv.childNodes).forEach(child => checkAndTransform(child));

            // Retornar o conteúdo HTML transformado
            return tempDiv.innerHTML;
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
