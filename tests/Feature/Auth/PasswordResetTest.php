<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Livewire\Auth\ForgotPassword;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(ForgotPassword::class)]
final class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        // Arrange & Act
        $response = $this->get('/forgot-password');

        // Assert
        $response->assertStatus(200);
    }

    #[Test]
    public function test_reset_password_link_can_be_requested(): void
    {
        // Arrange
        Notification::fake();

        /** @var User $user */
        $user = User::factory()->create();

        // Act
        Livewire::test('auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        // Assert
        Notification::assertSentTo($user, ResetPassword::class);
    }

    #[Test]
    public function test_reset_password_screen_can_be_rendered(): void
    {
        // Arrange
        Notification::fake();

        /** @var User $user */
        $user = User::factory()->create();

        // Act
        Livewire::test('auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        // Assert
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification): true {
            $response = $this->get('/reset-password/'.$notification->token);
            $response->assertStatus(200);

            return true;
        });
    }

    #[Test]
    public function test_password_can_be_reset_with_valid_token(): void
    {
        // Arrange
        Notification::fake();

        /** @var User $user */
        $user = User::factory()->create();

        // Act
        Livewire::test('auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        // Assert
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): true {
            $response = Livewire::test('auth.reset-password', ['token' => $notification->token])
                ->set('email', $user->email)
                ->set('password', 'password')
                ->set('password_confirmation', 'password')
                ->call('resetPassword');

            $response->assertHasNoErrors();
            $response->assertRedirect(route('login', absolute: false));

            return true;
        });
    }
}
