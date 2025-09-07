<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Livewire\Settings\PasswordInput;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(PasswordInput::class)]
final class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function password_can_be_updated(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        // Act
        $this->actingAs($user);
        $response = Livewire::test('settings.password-input')
            ->set('current_password', 'password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword');

        // Assert
        $response->assertHasNoErrors();
        Assert::assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    #[Test]
    public function correct_password_must_be_provided_to_update_password(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        // Act
        $this->actingAs($user);
        $response = Livewire::test('settings.password-input')
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword');

        // Assert
        $response->assertHasErrors(['current_password']);
    }
}
