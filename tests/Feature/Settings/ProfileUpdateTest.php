<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Livewire\Settings\ProfileUpdate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(ProfileUpdate::class)]
final class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_profile_page_is_displayed(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        // Act
        $this->actingAs($user);

        // Assert
        $this->get('/settings/profile')->assertOk();
    }

    #[Test]
    public function test_profile_information_can_be_updated(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        // Act
        $this->actingAs($user);
        $response = Livewire::test('settings.profile-update')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation');

        // Assert
        $response->assertHasNoErrors();
        $user->refresh();

        Assert::assertSame('Test User', $user->name);
        Assert::assertSame('test@example.com', $user->email);
        Assert::assertNull($user->email_verified_at);
    }

    #[Test]
    public function test_email_verification_status_is_unchanged_when_email_address_is_unchanged(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        // Act
        $this->actingAs($user);
        $response = Livewire::test('settings.profile-update')
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation');

        // Assert
        $response->assertHasNoErrors();

        Assert::assertNotNull($user->refresh()->email_verified_at);
    }

    #[Test]
    public function test_user_can_delete_their_account(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        // Act
        $this->actingAs($user);
        $response = Livewire::test('settings.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        // Assert
        $response->assertHasNoErrors();
        $response->assertRedirect('/');
        Assert::assertNull($user->fresh());
        Assert::assertFalse(auth()->check()); // @phpstan-ignore-line method.notFound
    }

    #[Test]
    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        // Act
        $this->actingAs($user);
        $response = Livewire::test('settings.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        // Assert
        $response->assertHasErrors(['password']);
        Assert::assertNotNull($user->fresh());
    }
}
