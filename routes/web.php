<?php

declare(strict_types=1);

use App\Livewire\Settings\AppearanceToggle;
use App\Livewire\Settings\PasswordInput;
use App\Livewire\Settings\ProfileUpdate;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function (): void {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', ProfileUpdate::class)->name('settings.profile');
    Route::get('settings/password', PasswordInput::class)->name('settings.password');
    Route::get('settings/appearance', AppearanceToggle::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';
