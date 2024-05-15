<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Button extends Component
{
    public $layout, $label, $icon, $target;
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $type,
        public string $route,
        string $layout = '',
        string $label = '',
        string $icon = '',
        string $target = '',
    ) {

        switch ($type) {
            case "edit":
                $layout = ($layout ?: "warning");
                $label = ($label ?: "Editar");
                $icon = ($icon ?: "edit-2");
                break;
            case "history":
                $layout = ($layout ?: "success");
                $label = ($label ?: "Histórico");
                $icon = ($icon ?: "clock");
                break;
            case "refresh":
                $layout = ($layout ?: "info");
                $label = ($label ?: "Atualizar");
                $icon = ($icon ?: "refresh-cw");
                break;
            case "print":
                $layout = ($layout ?: "danger");
                $label = ($label ?: "Imprimir");
                $icon = ($icon ?: "printer");
                $target = ($target ?: "_blank");
                break;
            case "cancel":
                $layout = ($layout ?: "warning");
                $label = ($label ?: "Cancelar");
                $icon = ($icon ?: "slash");
                break;
            default:
                $layout = ($layout ?: "primary");
                $label = ($label ?: "Botão");
                break;
        }

        $this->layout = $layout;
        $this->label = $label;
        $this->icon = $icon;
        $this->target = $target;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.button');
    }
}
