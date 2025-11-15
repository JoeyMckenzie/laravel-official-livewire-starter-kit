<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Volt\Volt;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->get(route('profile.edit'))->assertOk();
    }

    #[Test]
    public function profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $testable = Volt::test('settings.profile')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation');

        $testable->assertHasNoErrors();

        $user->refresh();

        Assert::assertEquals('Test User', $user->name);
        Assert::assertEquals('test@example.com', $user->email);
        Assert::assertNotInstanceOf(Carbon::class, $user->email_verified_at);
    }

    #[Test]
    public function email_verification_status_is_unchanged_when_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $testable = Volt::test('settings.profile')
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation');

        $testable->assertHasNoErrors();

        Assert::assertInstanceOf(Carbon::class, $user->refresh()->email_verified_at);
    }

    #[Test]
    public function user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $testable = Volt::test('settings.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $testable
            ->assertHasNoErrors()
            ->assertRedirect('/');

        Assert::assertNull($user->fresh());
        Assert::assertFalse(auth()->check());
    }

    #[Test]
    public function correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $testable = Volt::test('settings.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $testable->assertHasErrors(['password']);

        Assert::assertNotNull($user->fresh());
    }
}
