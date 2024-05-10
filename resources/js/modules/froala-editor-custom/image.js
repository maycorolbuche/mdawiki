FroalaEditor.PLUGINS.customManageImagePlugin = function (editor) {
    var modal = null;
    var url = editor.opts.imageManagerLoadURL;
    var id = 'imageModal';
    var el = null;
    var el_body = null;
    var el_loading = null;

    function init() {

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
        modalContent.classList.add('modal-content', 'h-100');

        let modalHeader = document.createElement('div');
        modalHeader.classList.add('modal-header');

        let modalTitle = document.createElement('h5');
        modalTitle.classList.add('modal-title', 'text-nowrap');
        modalTitle.textContent = 'Biblioteca de Imagens';

        let modalSearch = document.createElement('div');
        modalSearch.classList.add('input-group', 'px-4');
        modalSearch.style.flex = 'auto';

        let inputSearch = document.createElement('input');
        inputSearch.classList.add('form-control', 'border-end-0', 'border', 'rounded-pill', 'search');
        inputSearch.style.width = '100%';
        inputSearch.setAttribute('type', 'search');
        inputSearch.setAttribute('placeholder', 'Faça uma pesquisa...');
        inputSearch.addEventListener('change', function (event) {
            clearParams();
            clearBody();
            loadImages();
        });

        modalSearch.appendChild(inputSearch);

        let modalCloseButton = document.createElement('button');
        modalCloseButton.classList.add('btn-close');
        modalCloseButton.setAttribute('type', 'button');
        modalCloseButton.setAttribute('data-bs-dismiss', 'modal');
        modalCloseButton.setAttribute('aria-label', 'Close');

        modalHeader.appendChild(modalTitle);
        modalHeader.appendChild(modalSearch);
        modalHeader.appendChild(modalCloseButton);

        let modalBody = document.createElement('div');
        modalBody.classList.add('modal-body', 'm-3');
        modalBody.classList.add('d-flex');
        modalBody.classList.add('flex-wrap');
        modalBody.classList.add('justify-content-center');
        modalBody.appendChild(elementLoading());

        let modalFooter = document.createElement('div');
        modalFooter.classList.add('modal-footer');

        let textInfo = document.createElement('span');
        textInfo.textContent = "Não encontrou a imagem?";

        let newImageButton = document.createElement('a');
        newImageButton.classList.add('btn', 'btn-primary');
        newImageButton.setAttribute('href', editor.opts.imageManagerNewImage);
        newImageButton.setAttribute('target', '_blank');
        newImageButton.textContent = 'Nova Imagem';

        modalFooter.appendChild(textInfo);
        modalFooter.appendChild(newImageButton);

        modalContent.appendChild(modalHeader);
        modalContent.appendChild(modalBody);
        modalContent.appendChild(modalFooter);

        modalDialog.appendChild(modalContent);
        modalContainer.appendChild(modalDialog);

        document.body.appendChild(modalContainer);

        el = document.getElementById(id);
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

        let a = document.createElement('a');
        a.setAttribute('href', 'javascript:');
        a.addEventListener('click', function (event) {
            event.preventDefault();
            let max_width = 400;
            let width = (data.width > max_width ? max_width : data.width);

            editor.html.insert('<img ' +
                'src="' + data.url + '" ' +
                'data-slug="' + data.slug + '" ' +
                'alt="' + data.title + '"' +
                'style="width:' + width + 'px;max-width:' + max_width + 'px"' +
                '>');

            hide();
        });

        a.innerHTML = '<div class="card p-0 m-0">' +
            '<img class="card-img" src="' + data.url + '" alt="' + data.title +
            '" style="height:140px;object-fit: cover;">' +
            '</div>';

        imageDiv.appendChild(a);
        return imageDiv;
    }

    function loadImages() {
        let page = el_body.getAttribute("data-page");
        let stop = el_body.getAttribute("data-break");
        let search = el.querySelector('.search').value;

        if (stop == 1) {
            removeElementLoading();
            el_body.setAttribute("data-load", 0);
            return;
        }

        page++;
        el_body.setAttribute("data-load", 1);

        fetch(url + "?page=" + page + '&search=' + search, {
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

    function clearBody() {
        el_body.innerHTML = "";
    }

    function checkDataLoad() {
        let load = el_body.getAttribute("data-load");
        let stop = el_body.getAttribute("data-break");

        if (load != 1 && stop != 1) {
            if (el_body.scrollHeight - el_body.scrollTop - 400 <= el_body.clientHeight) {
                el_body.setAttribute("data-load", 1);
                //el_body.appendChild(elementLoading());
                loadImages();
            }
        }
    }

    function show() {
        if (!document.getElementById(id)) {
            init();
            el_body = document.getElementById(id).querySelector('.modal-body');
            clearParams();
            loadImages();

            el_body.addEventListener('scroll', function () {
                checkDataLoad();
            });
        }

        modal = new bootstrap.Modal(document.getElementById(id), {
            keyboard: false
        });
        modal.show();
    }

    function hide() {
        modal.hide();
    }

    return {
        show,
    }
}

FroalaEditor.RegisterCommand('image', {
    title: 'Inserir imagem',
    icon: 'insertImage',
    undo: false,
    focus: false,
    plugin: 'customManageImagePlugin',
    callback: function () {
        this.customManageImagePlugin.show();
    }
});