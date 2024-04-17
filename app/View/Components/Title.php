<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Title extends Component
{
    public int $level;

    /**
     * Create a new component instance.
     */
    public function __construct(
        int $level = 0,
    ) {
        $level = $level ?: 1;

        $this->level = $level;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.title');
    }
}
