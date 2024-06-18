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
        //document.querySelector('.content').style.display = 'flex';
        //document.querySelector('.content').style.alignItems = 'stretch';
        //document.querySelector('.container-fluid').style.display = 'flex';
        //document.querySelector('.container-fluid').style.flexDirection = 'column';

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
                    }
                }
            });



            const resizeObserver = new ResizeObserver(onResize);
            resizeObserver.observe(document.querySelector('.card'));


            document.querySelector("body").addEventListener("scroll", (event) => {
                onScroll();
            });
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
                @foreach ($components as $component)
                    '{{ $component['name'] }}': '{{ $component['title'] }}',
                @endforeach
            },
            callback: function(cmd, val) {
                console.log(val);
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

                    document.querySelectorAll('.custom-component').forEach(div => {
                        div.querySelectorAll('input').forEach(input => {
                            input.setAttribute('value', input.value);
                            input.setAttribute('data-value', input.value);
                        });
                    });
                } else {
                    this.componentsPlugin.hide();
                }
                console.log('do refresh', this.selection.element().closest('.custom-component'));
            },
            refreshOnShow: function($btn, $dropdown) {
                console.log('do refresh when show');
            }
        })


        /* POPUP - INÍCIO */
        Object.assign(FroalaEditor.POPUP_TEMPLATES, {
            'componentsPlugin.popup': '[_BUTTONS_]'
        });

        Object.assign(FroalaEditor.DEFAULTS, {
            popupButtons: ['removeComponent'],
        });

        FroalaEditor.PLUGINS.componentsPlugin = function(editor) {
            function initPopup() {
                var template = FroalaEditor.POPUP_TEMPLATES.customPopup;
                if (typeof template == 'function') template = template.apply(editor);

                var popup_buttons = '';

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


        FroalaEditor.DefineIcon('removeComponent', {
            NAME: 'trash',
            SVG_KEY: "removeTable"
        });
        FroalaEditor.RegisterCommand('removeComponent', {
            title: 'Remover Elemento',
            undo: false,
            focus: false,
            callback: function() {
                let element = this.selection.element().closest('.custom-component');
                element.parentNode.removeChild(element);
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


        .editor .custom-component {
            border: 2px dashed green !important;
            margin: 16px 0 5px 0;
            padding: 10px 5px 5px 5px;
        }

        .editor .custom-component .title-component {
            background: green;
            color: #FFF;
            padding: 0px 15px;
            border-radius: 5px;
            margin-top: -20px;
            position: absolute;
            font-size: 12px;
        }
    </style>
@endpush
