<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Livewire\Auth\ConfirmPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(ConfirmPassword::class)]
final class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_confirm_password_screen_can_be_rendered(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get('/confirm-password');

        // Assert
        $response->assertStatus(200);
    }

    #[Test]
    public function test_password_can_be_confirmed(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        // Act
        $this->actingAs($user);
        $response = Livewire::test('auth.confirm-password')
            ->set('password', 'password')
            ->call('confirmPassword');

        // Assert
        $response->assertHasNoErrors();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    #[Test]
    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        // Act
        $this->actingAs($user);
        $response = Livewire::test('auth.confirm-password')
            ->set('password', 'wrong-password')
            ->call('confirmPassword');

        // Assert
        $response->assertHasErrors(['password']);
    }
}
