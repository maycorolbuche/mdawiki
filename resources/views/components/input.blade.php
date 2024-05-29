@php
    $value = old(str_replace('[', '.', str_replace(']', '', $field))) ?: $value;
    $label = $label . ($required ? ' <span class="text-danger">*</span>' : '');
@endphp

<div
    class="{{ $colXs ? " col-$colXs " : '' }} {{ $colSm ? " col-sm-$colSm " : '' }}{{ $colMd ? " col-md-$colMd " : '' }}{{ $colLg ? " col-lg-$colLg " : '' }}{{ $colXl ? " col-xl-$colXl " : '' }}{{ $colXxl ? " col-xxl-$colXxl " : '' }}">

    @if ($type == 'captcha')
        <label for="{{ $id }}" class="form-label">{!! $label !!}</label>
        <div class="d-flex">
            {!! Captcha::img() !!}
            <input type="text" id="{{ $id }}" name="{{ $name }}" class="form-control">
        </div>
    @elseif ($type == 'checkbox')
        <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}"
            value="{{ $itemValue }}" {{ $value == $itemValue ? 'checked' : '' }}
            class="{{ $type == 'checkbox' ? 'form-check-input' : 'form-control' }}">
        <label for="{{ $id }}" class="form-check-label">{!! $label !!}</label>
    @elseif ($type == 'textarea')
        <label for="{{ $id }}" class="form-label">{!! $label !!}</label>
        <textarea rows="{{ $rows }}" id="{{ $id }}" name="{{ $name }}" class="form-control"
            placeholder="{{ $placeholder }}">{{ $value }}</textarea>
    @elseif ($type == 'photo')
        <label for="{{ $id }}" class="form-label">{!! $label !!}</label>
        <div class="col-b d-flex flex-column align-items-center">

            <div id="preview_container_{{ $id }}"
                style="width: 200px; height: 200px; overflow: hidden; padding: 0;">
                <div id="preview_{{ $id }}">
                </div>
                {!! $value != '' ? "<img id='current_image_" . $id . "' src='" . asset('storage/' . $value) . "'>" : '' !!}
            </div>
            <div class="mt-2">
                <a href="javascript:" id="add_image_button_{{ $id }}" class="btn btn-primary">
                    <i class="fas fa-upload"></i>
                    Enviar Imagem
                </a>
            </div>
            <div class="mt-2 d-flex flex-column align-items-center">
                <a href="javascript:" id="remove_image_button_{{ $id }}" class="text-danger small"
                    style="display: none">
                    <i class="align-middle" data-feather="trash-2"></i> Remover Imagem
                </a>
                <small class='text-center px-3' id="drag_image_info_{{ $id }}" style="display: none">
                    Arraste a imagem para reposicionar
                </small>
            </div>

            <input type="file" id="file_input_{{ $id }}" class="d-none" accept="image/*">
            <input type="hidden" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}">

            @push('scripts')
                <script>
                    const el_{{ $id }} = {
                        imageInput: document.getElementById('{{ $id }}'),
                        fileInput: document.getElementById('file_input_{{ $id }}'),
                        previewContainer: document.getElementById('preview_container_{{ $id }}'),
                        preview: document.getElementById('preview_{{ $id }}'),
                        addImageButton: document.getElementById('add_image_button_{{ $id }}'),
                        removeImageButton: document.getElementById('remove_image_button_{{ $id }}'),
                        currentImage: document.getElementById('current_image_{{ $id }}'),
                        dragImageInfo: document.getElementById('drag_image_info_{{ $id }}'),

                        init: function() {
                            const self = this;
                            el_{{ $id }}.imageContent();

                            this.addImageButton.onclick = function() {
                                self.fileInput.click();
                            };
                            this.removeImageButton.onclick = function() {
                                self.removeImage();
                            };

                            interact(this.fileInput, this.preview, this.previewContainer, function(base64) {
                                self.imageInput.value = base64;
                                self.imageContent();
                                if (self.currentImage !== null) {
                                    self.currentImage.remove();
                                }
                                if (base64) {
                                    self.dragImageInfo.style.display = 'block';
                                } else {
                                    self.dragImageInfo.style.display = 'none';
                                }
                            });
                        },
                        imageContent: function() {
                            if (this.imageInput.value != '') {
                                this.removeImageButton.style.display = 'block';
                                this.previewContainer.style.backgroundImage = '';
                            } else {
                                this.removeImageButton.style.display = 'none';
                                this.previewContainer.style.backgroundImage =
                                    "url('{{ asset('img/avatars/default.png') }}')";
                            }
                        },
                        removeImage: function() {
                            this.preview.innerHTML = '';
                            this.imageInput.value = '';
                            this.fileInput.value = '';
                            this.dragImageInfo.style.display = 'none';
                            if (this.currentImage !== null) {
                                this.currentImage.remove();
                            }
                            this.imageContent();
                        }
                    }
                    el_{{ $id }}.init();
                </script>
            @endpush

        </div>
    @elseif($type == 'tags')
        <label for="{{ $id }}" class="form-label">{!! $label !!}</label>
        <select id="{{ $id }}" name="{{ $name }}[]" class="form-select" id="validationTags" multiple
            data-not-found-message="Aperte ENTER para adicionar" data-allow-new="true"
            data-server="{{ $dataServer }}" multiple>
            <option selected disabled hidden value="">Escolha um item...</option>
        </select>

        @push('scripts')
            <script>
                Tags.init("#{{ $id }}", {
                    allowClear: true,
                });
            </script>
        @endpush
    @else
        <label for="{{ $id }}" class="form-label">{!! $label !!}</label>
        <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}"
            placeholder="{{ $placeholder }}" value="{{ $value }}"
            {{ $min ? 'min' . ($type != 'number' ? 'length' : '') . '=' . $min : '' }}
            {{ $max ? 'max' . ($type != 'number' ? 'length' : '') . '=' . $max : '' }}
            {{ $required ? 'required' : '' }} class="form-control {{ $errors->has($field) ? 'is-invalid' : '' }}">
    @endif


    @if ($errors->has($field))
        <div class="invalid-feedback">
            {{ $errors->first($field) }}
        </div>
    @endif

    {{ $slot }}
</div>
