@php
    $value = old(str_replace('[', '.', str_replace(']', '', $field))) ?: $value;
@endphp

<div
    class="{{ $colXs ? " col-$colXs " : '' }} {{ $colSm ? " col-sm-$colSm " : '' }}{{ $colMd ? " col-md-$colMd " : '' }}{{ $colLg ? " col-lg-$colLg " : '' }}{{ $colXl ? " col-xl-$colXl " : '' }}{{ $colXxl ? " col-xxl-$colXxl " : '' }}">


    @if ($type == 'captcha')
        <label for="{{ $id }}" class="form-label">{{ $label }}</label>
        <div class="d-flex">
            {!! Captcha::img() !!}
            <input type="text" id="{{ $id }}" name="{{ $name }}" class="form-control">
        </div>
    @elseif ($type == 'checkbox')
        <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}"
            value="{{ $itemValue }}" {{ $value == $itemValue ? 'checked' : '' }}
            class="{{ $type == 'checkbox' ? 'form-check-input' : 'form-control' }}">
        <label for="{{ $id }}" class="form-check-label">{{ $label }}</label>
    @else
        <label for="{{ $id }}" class="form-label">{{ $label }}</label>
        <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}"
            placeholder="{{ $placeholder }}" value="{{ $value }}"
            class="form-control {{ $errors->has($field) ? 'is-invalid' : '' }}">
    @endif


    @if ($errors->has($field))
        <div class="invalid-feedback">
            {{ $errors->first($field) }}
        </div>
    @endif

    {{ $slot }}
</div>

@php
    /*

    <div class="col-md-4">
        <label for="validationServer01" class="form-label">First name</label>
        <input type="text" class="form-control is-valid" id="validationServer01" value="Mark" required="">
        <div class="valid-feedback">
            Looks good!
        </div>
    </div>


    <div class="col-md-4">
        <label for="validationServer01" class="form-label">First name</label>
        <input type="text" class="form-control is-valid" id="validationServer01" value="Mark" required="">
        <div class="valid-feedback">
            Looks good!
        </div>
    </div>


    <div class="col-md-4">
        <label for="validationServer01" class="form-label">First name</label>
        <input type="text" class="form-control is-valid" id="validationServer01" value="Mark" required="">
        <div class="valid-feedback">
            Looks good!
        </div>
    </div>
*/
@endphp
