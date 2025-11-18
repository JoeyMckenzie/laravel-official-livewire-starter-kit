<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final class LogoutUser
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): Redirector|RedirectResponse // @phpstan-ignore-line
    {
        /** @var StatefulGuard $guard */
        $guard = Auth::guard('web');
        $guard->logout();

        Session::invalidate();
        Session::regenerateToken();

        return redirect('/');
    }
}
