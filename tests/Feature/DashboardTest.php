<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DashboardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_guests_are_redirected_to_the_login_page(): void
    {
        // Arrange & Act
        $response = $this->get('/dashboard');

        // Assert
        $response->assertRedirect('/login');
    }

    #[Test]
    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        // Act
        $this->actingAs($user);
        $response = $this->get('/dashboard');

        // Assert
        $response->assertStatus(200);
    }
}
