<?php

namespace Tests\Feature\Auth;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InactiveUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that inactive user cannot login
     */
    public function test_inactive_user_cannot_login(): void
    {
        // Create an inactive user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'is_active' => false,
            'user_type' => UserType::Reporter,
        ]);

        // Attempt to login
        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Assert user is redirected back with error
        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('deactivated', session('errors')->first('email'));

        // Assert user is not authenticated
        $this->assertGuest();
    }

    /**
     * Test that active user can login
     */
    public function test_active_user_can_login(): void
    {
        // Create an active user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'user_type' => UserType::Reporter,
            'two_factor_enabled' => false,
        ]);

        // Attempt to login
        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Assert user is redirected to home
        $response->assertRedirect(route('home'));

        // Assert user is authenticated
        $this->assertAuthenticated();
    }

    /**
     * Test that authenticated inactive user is automatically logged out
     */
    public function test_authenticated_inactive_user_is_logged_out(): void
    {
        // Create and authenticate a user
        $user = User::factory()->create([
            'is_active' => true,
            'user_type' => UserType::Reporter,
        ]);

        $this->actingAs($user);

        // Verify user is authenticated
        $this->assertAuthenticated();

        // Deactivate the user
        $user->update(['is_active' => false]);

        // Make a request to any protected route
        $response = $this->get(route('home'));

        // Assert user is redirected to login with error
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('deactivated', session('errors')->first('email'));

        // Assert user is no longer authenticated
        $this->assertGuest();
    }

    /**
     * Test that inactive user cannot complete 2FA verification
     */
    public function test_inactive_user_cannot_complete_2fa_verification(): void
    {
        // Create an inactive user with 2FA enabled
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'is_active' => false,
            'user_type' => UserType::Reporter,
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
        ]);

        // Simulate user has passed initial login and is in 2FA verification
        session(['2fa:user:id' => $user->id]);

        // Attempt to verify 2FA code
        $response = $this->post(route('2fa.verify'), [
            'code' => '123456',
        ]);

        // Assert user is redirected to login with error
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('deactivated', session('errors')->first('email'));

        // Assert user is not authenticated
        $this->assertGuest();

        // Assert 2FA session is cleared
        $this->assertNull(session('2fa:user:id'));
    }
}
