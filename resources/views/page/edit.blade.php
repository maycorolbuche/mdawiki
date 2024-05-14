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
                    <input type="submit" class="btn btn-success" value="Visualizar">
                    <a class="btn btn-warning" href="{{ route('page', ['page' => $page]) }}">
                        Cancelar
                    </a>
                </div>
            </x-card>
        </form>
    </div>

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
                        '|', 'insertLink',
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
                        '|', 'insertImage',
                        '|', 'imageManager',
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
                //tableEditButtons: ['imageDisplay', 'imageAlign', 'imageInfo', 'imageRemove'],
                tableStyles: {
                    class1: 'Tabela listrada',
                    class2: 'Class 2'
                },
                tableCellStyles: {
                    class1: 'Célula Destacada',
                    class2: 'Class 2'
                },

                /* Image */
                imageEditButtons: [
                    'imageAlign',
                    'imageCaption',
                    'imageDisplay',
                    //'imageStyle',
                    '|', 'imageRemove',
                ],
                /*imageStyles: {
                    class1: 'Tabela listrada',
                    class2: 'Class 2'
                },*/
                imageManagerLoadURL: "{{ route('json.images') }}",
                imageManagerNewImage: "{{ route('images') }}",

                //imageEditButtons: ['wirisEditor', 'wirisChemistry'],
                //htmlAllowedTags: [' .* '],
                //htmlAllowedAttrs: [' .* '],
                //htmlAllowedEmptyTags: ['mprescripts', 'none'],
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
    </script>
@endpush




@push('styles')
    <style>
        [id^="dropdown-menu-formatOLOptions-"] [data-param1="start"] {
            border-top: 1px solid #ebebeb;
        }
    </style>
@endpush
