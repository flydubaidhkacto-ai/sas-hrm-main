<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ActionButton extends Component
{

    public $id;
    public $viewRoute;
    public $editRoute;
    public $deleteRoute;


    /**
     * Create a new component instance.
     */
    public function __construct($id, $viewRoute = null, $editRoute = null, $deleteRoute = null)
    {
        $this->id = $id;
        $this->viewRoute = $viewRoute;
        $this->editRoute = $editRoute;
        $this->deleteRoute = $deleteRoute;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.action-button');
    }
}
