<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class Input extends Component
{
    public string $name, $id, $field, $type, $value; //, $class, $rows;
    //public bool $required, $disabled, $readonly, $hidden;

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

        string $name = '',
        string $id = '',
        string $type = '',
        string $field = '',
        string $value = ''
        /*
        public int $width = 400,
        public $min = '',
        public $max = '',
        
        
        public string $pre_type = '',
        string $required = 'false',
        string $disabled = 'false',
        string $readonly = 'false',
        string $hidden = 'false',
        

        
        string $class = '',
        public string $tip = '',
        public string $list = '[]',
        public string $listValue = '',
        public string $listText = '',
        string $rows = '',
        public string $onchange = '',*/
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
        /*
        $this->pre_type = $type;
        if ($type == 'slug') {
            $type = 'text';
            $class .= ' slug ';
        } elseif ($type == 'cpf_cnpj') {
            $type = 'text';
            $class .= ' cpf_cnpj ';
        } elseif ($type == 'rg') {
            $type = 'text';
            $class .= ' rg ';
        } elseif ($type == 'numeric') {
            $type = 'text';
            $class .= ' numeric ';
        } elseif ($type == 'pis') {
            $type = 'text';
            $class .= ' pis ';
        } elseif ($type == 'zip_code') {
            $type = 'text';
            $class .= ' zip_code ';
        } elseif ($type == 'date') {
            if ($value <> "") {
                $value = date("Y-m-d", strtotime($value));
            }
        } elseif ($type == 'phone') {
            $type = 'text';
            $class .= ' phone ';
        } elseif ($type == 'money') {
            $type = 'text';
            $class .= ' money text-right ';
        }
*/
        $this->id = $id;
        $this->name = $name;
        $this->field = $field ?: $name;
        $this->type = $type;
        $this->value = $value;

        /*
        $this->class = $class;
        
        $this->rows = $rows ?: 5;

        $this->required = $required && $required != "false";
        $this->disabled = $disabled && $disabled != "false";
        $this->readonly = $readonly && $readonly != "false";
        $this->hidden = $hidden && $hidden != "false";*/
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
