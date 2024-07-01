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
            action="{{ route('page.update', ['page' => $page]) }}" onsubmit="return beforeSaving()">
            <x-card body-class="p-2">

                <div id="editor-container" class="editor" style="flex: auto;">
                    <textarea id="editor" name="content" class="editor d-none">
                {{ $data->content_editor ?? '' }}
                </textarea>
                </div>


                <div class="row py-2">
                    <x-input id="categories" label="Categorias" type="tags"
                        data-server="{{ route('json.categories_input') }}"
                        value="{{ json_encode($data->page_categories->pluck('category_title')->toArray()) }}">
                        @foreach ($data->page_categories as $category)
                            <option value="{{ $category->category_title }}">
                                {{ $category->category_title }}</option>
                        @endforeach
                    </x-input>
                </div>

                <div class="card-actions float-end editor-card-actions editor-card-actions-sticky">
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
        var editor;
        document.addEventListener("DOMContentLoaded", () => {
            editor = new FroalaEditor('#editor', {
                language: 'pt_br',
                pastePlain: true,
                toolbarSticky: false,
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
                    'initialized': function() {

                        const toolbarObserver = new MutationObserver((mutationList) => {
                            for (const mutation of mutationList) {
                                if (mutation.type === 'attributes' && mutation.attributeName ===
                                    'style') {
                                    onResize();
                                }
                            }
                        });
                        toolbarObserver.observe(document.querySelector('.fr-toolbar'), {
                            subtree: true,
                            attributeOldValue: true
                        });

                        onResize();
                        onScroll();
                    }
                }
            });



            const resizeObserver = new ResizeObserver(onResize);
            resizeObserver.observe(document.querySelector('.card'));


            document.querySelector("body").addEventListener("scroll", (event) => {
                onScroll();
            });


            document.getElementById('editor-container').addEventListener('keypress', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    return false;
                }
            });
        });


        FroalaEditor.DefineIcon('insertMore', {
            //NAME: 'insertMore',
            //SVG_KEY: "insertMore"
            PATH: 'M264.5 5.2c14.9-6.9 32.1-6.9 47 0l218.6 101c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 149.8C37.4 145.8 32 137.3 32 128s5.4-17.9 13.9-21.8L264.5 5.2zM476.9 209.6l53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 277.8C37.4 273.8 32 265.3 32 256s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2c23.4 10.8 50.4 10.8 73.8 0l152-70.2zm-152 198.2l152-70.2 53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 405.8C37.4 401.8 32 393.3 32 384s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2c23.4 10.8 50.4 10.8 73.8 0z',
            template: 'svg512',
        })
        FroalaEditor.RegisterCommand('components', {
            title: 'Inserir Elementos',
            icon: 'insertMore',
            type: 'dropdown',
            focus: false,
            undo: false,
            refreshAfterCallback: true,
            options: {
                @foreach ($components as $component)
                    '{{ $component['name'] }}': '{{ $component['title'] }}',
                @endforeach
            },
            callback: function(cmd, val) {
                @foreach ($components as $component)
                    if (val == "{{ $component['name'] }}") {
                        const tableHTML = `{!! $component['component'] !!}`;
                        this.html.insert(tableHTML);
                    }
                @endforeach
            },
            refresh: function($btn) {
                if (this.selection.element().closest('.custom-component')) {
                    this.componentsPlugin.show();
                    if (this.selection.element().closest('.input-list-component-multi-element')) {
                        document.querySelector('.buttons-multi').style.display = 'block';
                    } else {
                        document.querySelector('.buttons-multi').style.display = 'none';
                    }
                } else {
                    this.componentsPlugin.hide();
                }
            },
        })


        /* POPUP - INÍCIO */
        Object.assign(FroalaEditor.POPUP_TEMPLATES, {
            'componentsPlugin.popup': '[_BUTTONS_]'
        });

        Object.assign(FroalaEditor.DEFAULTS, {
            popupButtons: ['spaceTop', 'spaceBottom', '|', 'removeComponent'],
            popupButtonsMulti: ['addBefore', /*'addAfter',*/ '|', 'moveUp', 'moveDown', '|', 'removeElement'],
        });

        FroalaEditor.PLUGINS.componentsPlugin = function(editor) {
            function initPopup() {
                var template = FroalaEditor.POPUP_TEMPLATES.customPopup;
                if (typeof template == 'function') template = template.apply(editor);

                var popup_buttons = '';

                popup_buttons += '<div class="fr-buttons buttons-multi">';
                popup_buttons += editor.button.buildList(editor.opts.popupButtonsMulti);
                popup_buttons += '</div>';
                popup_buttons += '<div class="fr-buttons">';
                popup_buttons += editor.button.buildList(editor.opts.popupButtons);
                popup_buttons += '</div>';

                var template = {
                    buttons: popup_buttons
                };

                var $popup = editor.popups.create('componentsPlugin.popup', template);

                return $popup;
            }

            function show() {
                var $popup = editor.popups.get('componentsPlugin.popup');

                if (!$popup) $popup = initPopup();

                editor.popups.setContainer('componentsPlugin.popup', editor.$tb);

                var rect = editor.selection.element().getBoundingClientRect();

                var left = rect.left + rect.width / 2;
                var top = rect.top + rect.height - 10;

                editor.popups.show('componentsPlugin.popup', left, top, 10);
            }

            function hide() {
                editor.popups.hide('componentsPlugin.popup');
            }

            return {
                show: show,
                hide: hide
            }
        }


        FroalaEditor.ICON_TEMPLATES = Object.assign(FroalaEditor.ICON_TEMPLATES, {
            svg512: "<svg class=\"fr-svg\" focusable=\"false\" viewBox=\"-100 -100 712 712\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"[PATH]\"/></svg>"
        });

        FroalaEditor.DefineIcon('spaceTop', {
            SVG_KEY: "tableHeader"
        });
        FroalaEditor.RegisterCommand('spaceTop', {
            title: 'Adicionar Linha Acima',
            undo: false,
            focus: false,
            callback: function() {
                let element = this.selection.element().closest('.custom-component');
                let br = document.createElement('br');
                element.parentNode.insertBefore(br, element);
            }
        });
        FroalaEditor.DefineIcon('spaceBottom', {
            SVG_KEY: "tableFooter"
        });
        FroalaEditor.RegisterCommand('spaceBottom', {
            title: 'Adicionar Linha Abaixo',
            undo: false,
            focus: false,
            callback: function() {
                let element = this.selection.element().closest('.custom-component');
                let br = document.createElement('br');
                element.insertAdjacentElement('afterend', br);
            }
        });
        FroalaEditor.DefineIcon('removeComponent', {
            SVG_KEY: "removeTable"
        });
        FroalaEditor.RegisterCommand('removeComponent', {
            title: 'Remover Elemento',
            undo: false,
            focus: false,
            callback: function() {
                let element = this.selection.element().closest('.custom-component');
                element.parentNode.removeChild(element);
                this.componentsPlugin.hide();
            }
        });
        FroalaEditor.DefineIcon('addBefore', {
            SVG_KEY: 'add'
        });
        FroalaEditor.RegisterCommand('addBefore', {
            title: 'Adicionar Acima',
            undo: false,
            focus: false,
            callback: function() {
                let element = this.selection.element().closest('.input-list-component-multi-element');
                let clonedElement = element.cloneNode(true);
                clonedElement.querySelectorAll('input, textarea, select').forEach(field => {
                    field.value = '';
                });
                element.parentNode.insertBefore(clonedElement, element);
                this.componentsPlugin.hide();
            }
        });
        /*  FroalaEditor.DefineIcon('addAfter', {
              SVG_KEY: "removeTable"
          });
          FroalaEditor.RegisterCommand('addAfter', {
              title: 'Adicionar Abaixo',
              undo: false,
              focus: false,
              callback: function() {
                  let element = this.selection.element().closest('.input-list-component-multi-element');
                  let clonedElement = element.cloneNode(true);
                  clonedElement.querySelectorAll('input, textarea, select').forEach(field => {
                      field.value = '';
                  });
                  element.insertAdjacentElement('afterend', clonedElement);
                  this.componentsPlugin.hide();
              }
          });*/
        FroalaEditor.DefineIcon('removeElement', {
            PATH: 'M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z',
            template: 'svg512'
        });
        FroalaEditor.RegisterCommand('removeElement', {
            title: 'Remover',
            undo: false,
            focus: false,
            callback: function() {
                let element_container = this.selection.element().closest('.input-list-component-multi');
                let count = element_container.querySelectorAll('.input-list-component-multi-element').length;
                if (count <= 1) {
                    let element = this.selection.element().closest('.input-list-component-multi-element');
                    let clonedElement = element.cloneNode(true);
                    clonedElement.querySelectorAll('input, textarea, select').forEach(field => {
                        field.value = '';
                    });
                    element.insertAdjacentElement('afterend', clonedElement);
                    element.parentNode.removeChild(element);
                } else {
                    let element = this.selection.element().closest('.input-list-component-multi-element');
                    element.parentNode.removeChild(element);
                }
                this.componentsPlugin.hide();
            }
        });
        FroalaEditor.DefineIcon('moveUp', {
            PATH: 'M233.4 105.4c12.5-12.5 32.8-12.5 45.3 0l192 192c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L256 173.3 86.6 342.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l192-192z',
            template: 'svg512',
        });
        FroalaEditor.RegisterCommand('moveUp', {
            title: 'Mover para Cima',
            undo: false,
            focus: false,
            callback: function() {
                let element = this.selection.element().closest('.input-list-component-multi-element');
                let sibling_element = element.previousElementSibling;
                if (sibling_element) {
                    sibling_element.parentNode.insertBefore(element, sibling_element);
                }

                this.componentsPlugin.hide();
            }
        });
        FroalaEditor.DefineIcon('moveDown', {
            PATH: 'M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z',
            template: 'svg512',
        });
        FroalaEditor.RegisterCommand('moveDown', {
            title: 'Mover para Baixo',
            undo: false,
            focus: false,
            callback: function() {
                let element = this.selection.element().closest('.input-list-component-multi-element');
                let sibling_element = element.nextElementSibling;
                if (sibling_element) {
                    element.parentNode.insertBefore(sibling_element, element);
                }

                this.componentsPlugin.hide();
            }
        });

        /* POPUP - FIM */



        function onResize() {
            let toolbar = document.querySelector('.fr-toolbar');
            if (toolbar) {
                toolbar.style.position = 'sticky';
                toolbar.style.top = document.querySelector("nav.navbar").offsetHeight + 'px';
                toolbar.style.zIndex = '10';
            }
            let actions = document.querySelector('.editor-card-actions');
            if (actions) {
                actions.style.position = 'sticky';
                actions.style.bottom = 0;
                actions.style.zIndex = '10';
            }
        }


        function onScroll() {
            let toolbar = document.querySelector('.fr-toolbar');
            let navbar = document.querySelector("nav.navbar");
            if (toolbar) {
                if (toolbar.getBoundingClientRect().top <= document.querySelector("nav.navbar").offsetHeight) {
                    toolbar.classList.add('toolbar-sticky')
                } else {
                    toolbar.classList.remove('toolbar-sticky')
                }
            }

            let actions = document.querySelector('.editor-card-actions');
            if (actions) {
                if (window.innerHeight - actions.getBoundingClientRect().bottom <= 0) {
                    actions.classList.add('editor-card-actions-sticky')
                } else {
                    actions.classList.remove('editor-card-actions-sticky')
                }
            }
        }


        function beforeSaving() {

            document.querySelectorAll('.custom-component').forEach(div => {
                div.querySelectorAll('input, select').forEach(element => {
                    if (element.tagName.toLowerCase() === 'input') {
                        element.setAttribute('value', element.value);
                        element.setAttribute('data-value', element.value);
                    } else if (element.tagName.toLowerCase() === 'select') {
                        const selectedOption = element.options[element.selectedIndex];
                        element.setAttribute('value', selectedOption.value);
                        element.setAttribute('data-value', selectedOption.value);
                        element.querySelectorAll('option').forEach(option => {
                            if (option === selectedOption) {
                                option.setAttribute('selected', 'selected');
                            } else {
                                option.removeAttribute('selected');
                            }
                        });
                    }
                });
            });

            return true;
        }
    </script>

    <script>
        const Component = {
            addBefore: function() {
                console.log("addBefore");
            },
            addAfter: function() {
                console.log("addAfter");
            },

            remove: function() {
                alert("b");
            }
        };
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

        .form-select.is-loading {
            background: #EEE;
        }

        .form-select.is-loading button,
        .form-select.is-loading input,
        .form-select.is-loading .dropdown-menu {
            display: none;
        }

        .tags-badge {
            padding: 10px !important;
        }

        .toolbar-sticky {
            border-radius: 0 !important;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        .editor-card-actions-sticky {
            box-shadow: 0px 0 12px 4px rgba(0, 0, 0, 0.2);
            background: #FFF;
            padding: 5px;
            border-radius: 5px;
        }

        /* ********************************* */

    </style>
@endpush
