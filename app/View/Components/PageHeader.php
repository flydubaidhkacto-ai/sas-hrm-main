<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PageHeader extends Component
{
    public array $breadcrumbs;

    /**
     * Create a new component instance.
     */
    public function __construct(public string $title, array $breadcrumbs = [])
    {
        // Always prepend the Dashboard breadcrumb
        $this->breadcrumbs = array_merge([
            ['name' => 'Dashboard', 'route' => route('home')],
        ], $breadcrumbs);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.page-header');
    }
}
