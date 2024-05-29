<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class Input extends Component
{
    public string $name, $id, $field, $type, $value;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public int $colXs = 0,
        public int $colSm = 0,
        public int $colMd = 0,
        public int $colLg = 0,
        public int $colXl = 0,
        public int $colXxl = 0,
        public $itemValue = 1,
        public string $label = '',
        public string $placeholder = '',
        public int $rows = 2,
        public string $dataServer = '',
        public $min = '',
        public $max = '',
        public bool $required = false,

        string $name = '',
        string $id = '',
        string $type = '',
        string $field = '',
        string $value = '',
    ) {
        if ($name == '') {
            $name = $id;
        } elseif ($id == '') {
            $id = $name;
        }

        if ($name == '') {
            $id = Str::random(8);
            $name = $id;
        }

        $id = str_replace('[', '_', $id);
        $id = str_replace(']', '_', $id);

        $type = $type ?: 'text';

        $this->id = $id;
        $this->name = $name;
        $this->field = $field ?: $name;
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        /* if ($this->hidden) {
            return '';
        } else {*/
        return view('components.input');
        /* }*/
    }
}
