<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Livewire\Settings\ProfileUpdate;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(ProfileUpdate::class)]
final class ProfileUpdateTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $user = User::factory()->create();
        $this->user = $user;

        $this->actingAs($user);
    }

    #[Test]
    public function profile_page_is_displayed(): void
    {
        $this->get(route('profile.edit'))->assertOk();
    }

    #[Test]
    public function profile_information_can_be_updated(): void
    {
        $testable = Livewire::test(ProfileUpdate::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation');

        $testable->assertHasNoErrors();

        $this->user->refresh();

        Assert::assertSame('Test User', $this->user->name);
        Assert::assertSame('test@example.com', $this->user->email);
        Assert::assertNotInstanceOf(Carbon::class, $this->user->email_verified_at);
    }

    #[Test]
    public function email_verification_status_is_unchanged_when_email_address_is_unchanged(): void
    {
        $testable = Livewire::test(ProfileUpdate::class)
            ->set('name', 'Test User')
            ->set('email', $this->user->email)
            ->call('updateProfileInformation');

        $testable->assertHasNoErrors();

        Assert::assertInstanceOf(Carbon::class, $this->user->refresh()->email_verified_at);
    }

    #[Test]
    public function user_can_delete_their_account(): void
    {
        $this->actingAs($this->user);

        $testable = Livewire::test('settings.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $testable
            ->assertHasNoErrors()
            ->assertRedirect('/');

        Assert::assertNull($this->user->fresh());
        Assert::assertFalse(auth()->check());
    }

    #[Test]
    public function correct_password_must_be_provided_to_delete_account(): void
    {
        $this->actingAs($this->user);

        $testable = Livewire::test('settings.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $testable->assertHasErrors(['password']);

        Assert::assertNotNull($this->user->fresh());
    }
}
