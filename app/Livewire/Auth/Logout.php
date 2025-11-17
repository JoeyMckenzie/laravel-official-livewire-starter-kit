<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): Redirector|RedirectResponse // @php
    {
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        return redirect('/');
    }
}
