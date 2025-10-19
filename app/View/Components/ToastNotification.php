<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ToastNotification extends Component
{
    public string $message;

    public string $type;

    public function __construct(string $message, string $type = 'info')
    {
        $this->message = $message;
        $this->type    = $type;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        //dd($this->message);
        return view('components.toast-notification');
    }
}
