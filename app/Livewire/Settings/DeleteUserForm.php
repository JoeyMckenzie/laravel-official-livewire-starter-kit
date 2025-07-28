<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

final class DeleteUserForm extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        tap($user, function () {
            Auth::guard('web')->logout(); // @phpstan-ignore-line method.notFound

            Session::invalidate();
            Session::regenerateToken();
        })->delete();

        $this->redirect('/', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.settings.delete-user-form');
    }
}
