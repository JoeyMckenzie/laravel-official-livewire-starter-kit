<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
final class ForgotPassword extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        /** @var array{email: string} $email */
        $email = $this->only('email');

        Password::sendResetLink($email);

        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password');
    }
}
