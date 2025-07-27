<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(Login::class)]
final class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_login_screen_can_be_rendered(): void
    {
        // Arrange & Act
        $response = $this->get('/login');

        // Assert
        $response->assertStatus(200);
    }

    #[Test]
    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = Livewire::test('auth.login')
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login');

        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    #[Test]
    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = Livewire::test('auth.login')
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('login');

        $response->assertHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');

        $this->assertGuest();
    }
}
