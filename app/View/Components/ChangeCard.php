<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ChangeCard extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $type = 'page',
        public string $img = '',
        public string $name,
        public string $avatar,
        public string $datetime,
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.change-card');
    }
}
