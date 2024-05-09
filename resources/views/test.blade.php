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

    <div class="row" style="flex: auto;">
        <div class="col-12" style="display: flex;">
            <div id="editor-container" class="editor" style="flex: auto;">
                <div id="editor" class="editor">
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.querySelector('.content').style.display = 'flex';
        document.querySelector('.content').style.alignItems = 'stretch';
        document.querySelector('.container-fluid').style.display = 'flex';
        document.querySelector('.container-fluid').style.flexDirection = 'column';


        document.addEventListener("DOMContentLoaded", () => {
            const editor = new FroalaEditor('#editor', {
                language: 'pt_br',
                height: document.getElementById('editor-container').offsetHeight - 190,

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

                /* Image Manager */
                imageManagerLoadURL: "{{ route('json.images') }}",
                //imageManagerLoadMethod: "get",
                //imageManagerLoadParams: {},
                //imageManagerPreloader: null,
                imageManagerDeleteURL: "",
                //imageManagerDeleteMethod: "post",
                //imageManagerDeleteParams: {},
                imageManagerPageSize: 12,
                //imageManagerScrollOffset: 20,
                imageManagerToggleTags: false,

                //imageEditButtons: ['wirisEditor', 'wirisChemistry'],
                //htmlAllowedTags: [' .* '],
                //htmlAllowedAttrs: [' .* '],
                //htmlAllowedEmptyTags: ['mprescripts', 'none'],
            });

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
    </script>
@endpush




@push('styles')
    <style>
    </style>
@endpush

@push('scripts')
    <script>
        FroalaEditor.PLUGINS.customManageImagePlugin = function(editor) {
            var url = editor.opts.imageManagerLoadURL;
            var id = 'imageModal';
            var el = document.getElementById(id);
            var el_body = null;
            var el_loading = null;

            function initPopup() {

                let modalContainer = document.createElement('div');
                modalContainer.classList.add('modal', 'fade');
                modalContainer.id = id;
                modalContainer.setAttribute('tabindex', '-1');
                modalContainer.setAttribute('role', 'dialog');
                modalContainer.setAttribute('aria-hidden', 'true');

                let modalDialog = document.createElement('div');
                modalDialog.classList.add('modal-dialog', 'modal-xl', 'modal-dialog-centered',
                    'modal-dialog-scrollable');
                modalDialog.setAttribute('role', 'document');

                let modalContent = document.createElement('div');
                modalContent.classList.add('modal-content');

                let modalHeader = document.createElement('div');
                modalHeader.classList.add('modal-header');

                let modalTitle = document.createElement('h5');
                modalTitle.classList.add('modal-title');
                modalTitle.textContent = 'Biblioteca de Imagens';

                let modalCloseButton = document.createElement('button');
                modalCloseButton.classList.add('btn-close');
                modalCloseButton.setAttribute('type', 'button');
                modalCloseButton.setAttribute('data-bs-dismiss', 'modal');
                modalCloseButton.setAttribute('aria-label', 'Close');

                modalHeader.appendChild(modalTitle);
                modalHeader.appendChild(modalCloseButton);

                let modalBody = document.createElement('div');
                modalBody.classList.add('modal-body', 'm-3');
                modalBody.classList.add('d-flex');
                modalBody.classList.add('flex-wrap');
                modalBody.classList.add('justify-content-center');
                modalBody.appendChild(elementLoading());

                let modalFooter = document.createElement('div');
                modalFooter.classList.add('modal-footer');

                let closeButton = document.createElement('button');
                closeButton.classList.add('btn', 'btn-secondary');
                closeButton.setAttribute('type', 'button');
                closeButton.setAttribute('data-bs-dismiss', 'modal');
                closeButton.textContent = 'Close';

                let saveButton = document.createElement('button');
                saveButton.classList.add('btn', 'btn-primary');
                saveButton.setAttribute('type', 'button');
                saveButton.textContent = 'Save changes';

                modalFooter.appendChild(closeButton);
                modalFooter.appendChild(saveButton);

                modalContent.appendChild(modalHeader);
                modalContent.appendChild(modalBody);
                modalContent.appendChild(modalFooter);

                modalDialog.appendChild(modalContent);
                modalContainer.appendChild(modalDialog);

                document.body.appendChild(modalContainer);
            }

            function elementLoading() {
                let loadingDiv = document.createElement('div');
                loadingDiv.classList.add('loading');
                loadingDiv.classList.add('d-flex');
                loadingDiv.classList.add('align-items-center');
                loadingDiv.classList.add('justify-content-center');

                let spinnerDiv = document.createElement('div');
                spinnerDiv.classList.add('spinner-border', 'text-secondary', 'me-2');
                spinnerDiv.setAttribute('role', 'status');

                let loadingSpan = document.createElement('span');
                loadingSpan.classList.add('visually-hidden');
                loadingSpan.textContent = 'Carregando...';

                spinnerDiv.appendChild(loadingSpan);

                loadingDiv.appendChild(spinnerDiv);

                el_loading = loadingDiv;
                return loadingDiv;
            }

            function removeElementLoading() {
                if (el_loading) {
                    el_loading.remove();
                }
            }

            function elementImage(data) {
                let imageDiv = document.createElement('div');
                imageDiv.classList.add('p-2');
                imageDiv.style.width = '150px';
                //imageDiv.style.height = '300px';

                imageDiv.innerHTML = '<a href="javascript:">' +
                    '<div class="card p-0 m-0">' +
                    '<img class="card-img" src="' + data.full_url + '" alt="' + data.title +
                    '" style="height:140px;object-fit: cover;">' +
                    //    '<div class="card-header">' +
                    //    '<h5 class="card-title mb-0 text-truncate">' + data.title + 's</h5>' +
                    //    '</div>' +
                    '</div>' +
                    '</a>';
                return imageDiv;
            }

            function loadImages() {
                let page = el_body.getAttribute("data-page");
                let stop = el_body.getAttribute("data-break");

                if (stop == 1) {
                    console.log("STOP");
                    removeElementLoading();
                    el_body.setAttribute("data-load", 0);
                    return;
                }

                page++;
                el_body.setAttribute("data-load", 1);

                fetch(url + "?page=" + page, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            el_body.setAttribute("data-load", 0);
                            showError('Ocorreu um erro ao carregar os dados.');
                            throw new Error('Ocorreu um erro ao carregar os dados.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        removeElementLoading();
                        data.data.map(item => {
                            el_body.appendChild(elementImage(item));
                        });

                        console.log("LOAD", page);
                        el_body.setAttribute("data-load", 0);
                        el_body.setAttribute("data-page", page);
                        if (data.to < data.total) {
                            checkDataLoad();
                        } else {
                            el_body.setAttribute("data-break", 1);
                        }

                        //el_body.appendChild(elementImage());
                    })
                    .catch(error => {
                        showError(error);
                        console.error(error);
                        el_body.setAttribute("data-load", 0);
                    });

            }

            function showError(message) {
                clearParams();
                el_body.innerHTML = '<div class="alert alert-danger alert-dismissible p-3" role="alert">' +
                    '<div class="alert-message">' +
                    message +
                    '</div>' +
                    '</div>';
            }

            function clearParams() {
                el_body.setAttribute("data-page", 0);
                el_body.setAttribute("data-break", 0);
            }

            function checkDataLoad() {
                let load = el_body.getAttribute("data-load");
                let stop = el_body.getAttribute("data-break");

                console.log("SCROLL", load);

                if (load != 1 && stop != 1) {
                    if (el_body.scrollHeight - el_body.scrollTop - 100 <= el_body.clientHeight) {
                        el_body.setAttribute("data-load", 1);
                        el_body.appendChild(elementLoading());
                        loadImages();
                    }
                }
            }

            function showPopup() {
                if (!document.getElementById(id)) {
                    console.log("CRIAR")
                    initPopup();
                    el_body = document.getElementById(id).querySelector('.modal-body');
                    clearParams();
                    loadImages();

                    el_body.addEventListener('scroll', function() {
                        checkDataLoad();
                    });
                }

                /*alert("teste");
                // Get the popup object defined above.
                var $popup = editor.popups.get('customManageImagePlugin.popup');

                // If popup doesn't exist then create it.
                // To improve performance it is best to create the popup when it is first needed
                // and not when the editor is initialized.
                if (!$popup) $popup = initPopup();

                // Set the editor toolbar as the popup's container.
                editor.popups.setContainer('customManageImagePlugin.popup', editor.$tb);

                // This will trigger the refresh event assigned to the popup.
                // editor.popups.refresh('customManageImagePlugin.popup');

                // This custom popup is opened by pressing a button from the editor's toolbar.
                // Get the button's object in order to place the popup relative to it.
                var $btn = editor.$tb.find('.fr-command[data-cmd="myButton"]');

                // Set the popup's position.
                var left = $btn.offset().left + $btn.outerWidth() / 2;
                var top = $btn.offset().top + (editor.opts.toolbarBottom ? 10 : $btn.outerHeight() - 10);

                // Show the custom popup.
                // The button's outerHeight is required in case the popup needs to be displayed above it.
                editor.popups.show('customManageImagePlugin.popup', left, top, $btn.outerHeight());*/

                var imageModal = new bootstrap.Modal(document.getElementById(id), {
                    keyboard: false
                });
                imageModal.show();
            }

            // Hide the custom popup.
            function hidePopup() {
                editor.popups.hide('customManageImagePlugin.popup');
            }

            // Methods visible outside the plugin.
            return {
                showPopup: showPopup,
                hidePopup: hidePopup
            }
        }

        FroalaEditor.RegisterCommand('image', {
            title: 'Inserir imagem',
            icon: 'insertImage',
            undo: false,
            focus: false,
            plugin: 'customManageImagePlugin',
            callback: function() {
                this.customManageImagePlugin.showPopup();
            }
        });
    </script>
@endpush
