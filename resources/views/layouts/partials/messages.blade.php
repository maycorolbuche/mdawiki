@php
    $messages = [];
    if (isset($errors) && count($errors) > 0) {
        $messages['danger'] = $errors->all();
    }

    if (Session::get('error', false)) {
        if (is_array(Session::get('error'))) {
            foreach (Session::get('error') as $msg) {
                $messages['danger'][] = $msg;
            }
        } else {
            $messages['danger'][] = Session::get('error');
        }
    }
    if (isset($error)) {
        $messages['danger'][] = $error;
    }

    if (Session::get('success', false)) {
        if (is_array(Session::get('success'))) {
            foreach (Session::get('success') as $msg) {
                $messages['success'][] = $msg;
            }
        } else {
            $messages['success'][] = Session::get('success');
        }
    }
    if (isset($success)) {
        $messages['success'][] = $success;
    }

    if (Session::get('warning', false)) {
        if (is_array(Session::get('warning'))) {
            foreach (Session::get('warning') as $msg) {
                $messages['warning'][] = $msg;
            }
        } else {
            $messages['warning'][] = Session::get('warning');
        }
    }
    if (isset($warning)) {
        $messages['warning'][] = $warning;
    }

    if (Session::get('info', false)) {
        if (is_array(Session::get('info'))) {
            foreach (Session::get('info') as $msg) {
                $messages['info'][] = $msg;
            }
        } else {
            $messages['info'][] = Session::get('info');
        }
    }
    if (isset($info)) {
        $messages['info'][] = $info;
    }
@endphp


@foreach ($messages as $type => $message)
    @if (count($message) > 0)
        <div class="alert alert-{{ $type }} alert-dismissible" role="alert">
            @if ($type == 'danger' || $type == 'success' || $type == 'warning' || $type == 'info')
                <div class="alert-icon d-flex align-items-center">
                    @if ($type == 'danger')
                        <x-icon value="alert-octagon" />
                    @elseif ($type == 'success')
                        <x-icon value="info" />
                    @elseif ($type == 'warning')
                        <x-icon value="alert-triangle" />
                    @elseif ($type == 'info')
                        <x-icon value="info" />
                    @endif
                </div>
            @endif
            <div class="alert-message d-flex align-items-center flex-fill p-3">
                <div>
                    @if (count($message) == 1)
                        {!! $message[0] !!}
                    @else
                        @if ($type == 'danger')
                            <strong>Alertas:</strong>
                        @endif
                        <ul class="m-0">
                            @foreach ($message as $msg)
                                <li>{!! $msg !!}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
            <div class="d-flex align-items-center">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                    style="position: initial !important;"></button>
            </div>
        </div>
    @endif
@endforeach
