<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Register;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(Register::class)]
final class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function registration_screen_can_be_rendered(): void
    {
        // Arrange & Act
        $response = $this->get(route('register'));

        // Assert
        $response->assertStatus(200);
    }

    #[Test]
    public function new_users_can_register(): void
    {
        // Arrange & Act
        $response = Livewire::test('auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        // Assert
        $response->assertHasNoErrors();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
    }
}
