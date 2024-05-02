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
    <x-title>Título da Página</x-title>

    <div class="row">
        <div class="col-12">
            <div id="example"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const editor = new FroalaEditor('#example', {
                language: 'pt_br',
                toolbarButtons: [
                    [
                        'undo', 'redo',
                        '|', 'selectAll',
                        '|',
                    ],
                    [
                        'bold', 'italic', 'underline', 'strikeThrough',
                        '|', 'subscript', 'superscript',
                        '|', 'clearFormatting',
                        '|',
                    ],
                    [
                        'paragraphFormat', 'align',
                        '|', 'insertLink',
                        '|', 'formatOL', 'formatUL',
                        '|', 'outdent', 'indent',
                        '|', 'insertHR',
                        '|',
                    ],
                    [
                        //'wirisEditor', 'wirisChemistry',
                        'custom_insert', 'myButton',
                        '|',
                    ]
                ],
                listAdvancedTypes: true,
                formatOLBeginOption: true,

                events: {
                    initialized: function() {
                        addOLStartIn();
                    },
                    'buttons.refresh': function() {
                        formatOLRefresh();
                    }
                }
                //imageEditButtons: ['wirisEditor', 'wirisChemistry'],
                //htmlAllowedTags: [' .* '],
                //htmlAllowedAttrs: [' .* '],
                //htmlAllowedEmptyTags: ['mprescripts', 'none'],
            });


            function formatOLRefresh() {
                if (editor.selection.element().parentNode.tagName === "OL") {
                    let num = editor.selection.element().parentNode.getAttribute('start') ??
                        1;
                    document.getElementById("formatOLNumber").innerHTML = num;
                    document.getElementById('formatOLMinus-1').classList.remove(
                        'fr-disabled');
                    document.getElementById('formatOLPlus-1').classList.remove(
                        'fr-disabled');
                } else {
                    document.getElementById("formatOLNumber").innerHTML = 1;
                    document.getElementById('formatOLMinus-1').classList.add('fr-disabled');
                    document.getElementById('formatOLPlus-1').classList.add('fr-disabled');
                }
            }

            function addOLStartIn() {
                FroalaEditor.DefineIcon('minus', {
                    NAME: 'minus',
                    SVG_KEY: 'minimize'
                })
                FroalaEditor.DefineIcon('plus', {
                    NAME: 'plus',
                    SVG_KEY: 'add'
                })
                FroalaEditor.RegisterCommand('formatOLMinus', {
                    title: 'Diminuir valor inicial',
                    icon: 'minus',
                    undo: true,
                    focus: true,
                    showOnMobile: true,
                    refreshAfterCallback: true,
                })
                FroalaEditor.RegisterCommand('formatOLPlus', {
                    title: 'Aumentar valor inicial',
                    icon: 'plus',
                    undo: true,
                    focus: true,
                    showOnMobile: true,
                    refreshAfterCallback: true,
                })

                let divContainer = document.createElement('div');
                divContainer.style.borderTop = '1px solid #ebebeb';

                let divTitle = document.createElement('div');
                divTitle.style.padding = '8px 15px 0px 15px';
                divTitle.style.fontSize = '12px';
                divTitle.innerHTML = 'Iniciar numeração em:';
                divContainer.appendChild(divTitle);

                let divContent = document.createElement('div');
                divContent.style.display = 'flex';
                divContent.style.flexWrap = 'nowrap';
                divContent.style.justifyContent = 'space-between';
                divContent.style.alignItems = 'center';

                divContent.innerHTML = '';
                divContent.innerHTML += editor.button.buildList(['formatOLMinus']).replace(
                    "fr-command", "fr-command-custom fr-disabled");
                divContent.innerHTML += '<div id="formatOLNumber">1</div>';
                divContent.innerHTML += editor.button.buildList(['formatOLPlus']).replace(
                    "fr-command", "fr-command-custom fr-disabled");

                divContainer.appendChild(divContent);

                let liElement = document.createElement('li');
                liElement.appendChild(divContainer);

                document.getElementById('dropdown-menu-formatOLOptions-1').querySelector(
                    '.fr-dropdown-list').appendChild(liElement);

                document.getElementById('formatOLMinus-1').onclick = function() {
                    if (editor.selection.element().parentNode.tagName === "OL") {
                        let num = +editor.selection.element().parentNode.getAttribute(
                                'start') ??
                            1;
                        if (num > 1) {
                            num--;
                            editor.selection.element().parentNode.setAttribute(
                                'start', num)
                        }
                    }
                    formatOLRefresh();
                };
                document.getElementById('formatOLPlus-1').onclick = function() {
                    if (editor.selection.element().parentNode.tagName === "OL") {
                        let num = +editor.selection.element().parentNode.getAttribute(
                                'start') ??
                            1;
                        if (num < 1) {
                            num = 1;
                        }
                        num++
                        editor.selection.element().parentNode.setAttribute(
                            'start', num)
                    }
                    formatOLRefresh();
                };
            }
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
                        <table class="custom-table" style='background:red;'>
                            <thead>
                                <tr>
                                    <th contenteditable="false">Header 1</th>
                                    <th contenteditable="false">Header 2</th>
                                    <th contenteditable="false">Header 3</th>
                                    <th><button class="delete-table-btn">Delete</button></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td contenteditable="false">|</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
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
    </script>
@endpush

@push('styles')
    <style>
        .fr-command-custom {
            background: transparent;
            color: #333333;
            outline: none;
            border: 0;
            cursor: pointer;
            text-align: left;
            margin: 4px 2px;
            border-radius: 4px;
            padding: 8px;
            background-clip: padding-box;
            box-sizing: border-box;
            text-decoration: none;
            height: 40px;
            width: 40px;
        }

        .fr-command-custom.fr-disabled {
            color: #bdbdbd;
            cursor: default;
            opacity: 0.3;
        }

        .fr-command-custom:hover {
            outline: 0;
            color: #333333;
            background: #ebebeb;
        }
    </style>
@endpush()
