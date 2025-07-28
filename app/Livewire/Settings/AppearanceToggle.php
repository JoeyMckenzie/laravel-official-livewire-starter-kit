<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use Illuminate\Contracts\View\View;
use Livewire\Component;

final class AppearanceToggle extends Component
{
    public function render(): View
    {
        return view('livewire.settings.appearance');
    }
}
