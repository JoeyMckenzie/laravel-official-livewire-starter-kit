<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(VerifyEmailController::class)]
final class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function email_verification_screen_can_be_rendered(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->unverified()->create();

        // Act
        $response = $this->actingAs($user)->get(route('verification.notice'));

        // Assert
        $response->assertStatus(200);
    }

    #[Test]
    public function email_can_be_verified(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Act
        $response = $this->actingAs($user)->get($verificationUrl);

        // Assert
        Event::assertDispatched(Verified::class);

        Assert::assertTrue($user->fresh()?->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    #[Test]
    public function email_is_not_verified_with_invalid_hash(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        // Act
        $this->actingAs($user)->get($verificationUrl);

        // Assert
        Assert::assertFalse($user->fresh()?->hasVerifiedEmail());
    }

    #[Test]
    public function already_verified_user_visiting_verification_link_is_redirected_without_firing_event_again(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        // Act
        $this->actingAs($user)
            ->get($verificationUrl)
            ->assertRedirect(route('dashboard', absolute: false).'?verified=1');

        $refreshed = $user->fresh();
        assert($refreshed !== null);

        // assert
        Assert::assertNotNull($refreshed->email_verified_at);
        Assert::assertTrue($refreshed->hasVerifiedEmail());
        Event::assertNotDispatched(Verified::class);
    }
}
