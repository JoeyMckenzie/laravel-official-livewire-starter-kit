<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Actions\Fortify\LogoutUser;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class DeleteUserForm extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(
        #[CurrentUser] User $user,
        LogoutUser $logout,
    ): void {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap($user, $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.settings.delete-user-form');
    }
}
