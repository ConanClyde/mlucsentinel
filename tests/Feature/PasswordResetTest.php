<?php

namespace Tests\Feature;

use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('oldpassword')
        ]);

        // Request password reset
        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com'
        ]);

        // Should redirect to reset password form
        $response->assertRedirect('/reset-password?email=test%40example.com');
        $response->assertSessionHas('status', 'Password reset code has been sent to your email!');

        // Should create a reset code in database
        $this->assertDatabaseHas('password_reset_codes', [
            'email' => 'test@example.com',
            'is_used' => false
        ]);

        // Reset code should expire in 5 minutes
        $resetCode = PasswordResetCode::where('email', 'test@example.com')->first();
        $this->assertTrue($resetCode->expires_at->isAfter(now()->addMinutes(4)));
        $this->assertTrue($resetCode->expires_at->isBefore(now()->addMinutes(6)));
    }

    public function test_user_cannot_request_reset_with_invalid_email()
    {
        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com'
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseMissing('password_reset_codes', [
            'email' => 'nonexistent@example.com'
        ]);
    }

    public function test_reset_code_validation_works()
    {
        // Create a user
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Create a valid reset code
        $resetCode = PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'is_used' => false
        ]);

        // Test valid code validation
        $response = $this->post('/validate-reset-code', [
            'email' => 'test@example.com',
            'code' => '123456'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'message' => 'Code is valid!'
        ]);
    }

    public function test_reset_code_validation_rejects_invalid_code()
    {
        // Create a user
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Create a valid reset code
        PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'is_used' => false
        ]);

        // Test invalid code validation
        $response = $this->post('/validate-reset-code', [
            'email' => 'test@example.com',
            'code' => '999999'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => false,
            'message' => 'Invalid or expired reset code.'
        ]);
    }

    public function test_reset_code_validation_rejects_expired_code()
    {
        // Create a user
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Create an expired reset code
        PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'expires_at' => now()->subMinutes(1), // Expired 1 minute ago
            'is_used' => false
        ]);

        // Test expired code validation
        $response = $this->post('/validate-reset-code', [
            'email' => 'test@example.com',
            'code' => '123456'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => false,
            'message' => 'Invalid or expired reset code.'
        ]);
    }

    public function test_reset_code_validation_rejects_used_code()
    {
        // Create a user
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Create a used reset code
        PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'is_used' => true // Already used
        ]);

        // Test used code validation
        $response = $this->post('/validate-reset-code', [
            'email' => 'test@example.com',
            'code' => '123456'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => false,
            'message' => 'Invalid or expired reset code.'
        ]);
    }

    public function test_user_can_reset_password_with_valid_code()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('oldpassword')
        ]);

        // Create a valid reset code
        $resetCode = PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'is_used' => false
        ]);

        // Reset password
        $response = $this->post('/reset-password', [
            'email' => 'test@example.com',
            'code' => '123456',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        // Should redirect to login
        $response->assertRedirect('/login');
        $response->assertSessionHas('status', 'Password has been reset successfully! You can now log in with your new password.');

        // User should be able to login with new password
        $this->assertTrue(\Hash::check('newpassword123', $user->fresh()->password));

        // All reset codes for this email should be deleted (they are deleted after successful reset)
        $this->assertDatabaseMissing('password_reset_codes', [
            'email' => 'test@example.com'
        ]);
    }

    public function test_user_cannot_reset_password_with_invalid_code()
    {
        // Create a user
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Try to reset with invalid code
        $response = $this->post('/reset-password', [
            'email' => 'test@example.com',
            'code' => '999999',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertSessionHasErrors(['code']);
        $errors = $response->getSession()->get('errors');
        $this->assertStringContainsString('Invalid or expired reset code', $errors->first('code'));
    }

    public function test_old_codes_are_deleted_when_new_code_is_requested()
    {
        // Create a user
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Create an old reset code
        PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '111111',
            'expires_at' => now()->addMinutes(5),
            'is_used' => false
        ]);

        // Request new reset code
        $this->post('/forgot-password', [
            'email' => 'test@example.com'
        ]);

        // Old code should be deleted
        $this->assertDatabaseMissing('password_reset_codes', [
            'email' => 'test@example.com',
            'code' => '111111'
        ]);

        // New code should exist
        $this->assertDatabaseHas('password_reset_codes', [
            'email' => 'test@example.com',
            'is_used' => false
        ]);
    }

    public function test_reset_code_is_6_digits()
    {
        // Create a user
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Request password reset
        $this->post('/forgot-password', [
            'email' => 'test@example.com'
        ]);

        // Get the created reset code
        $resetCode = PasswordResetCode::where('email', 'test@example.com')->first();

        // Code should be exactly 6 digits
        $this->assertEquals(6, strlen($resetCode->code));
        $this->assertTrue(ctype_digit($resetCode->code));
    }

    public function test_reset_code_expires_after_5_minutes()
    {
        // Create a user
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Create a reset code
        $resetCode = PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'is_used' => false
        ]);

        // Code should be valid now
        $this->assertTrue($resetCode->expires_at->isFuture());

        // Travel 6 minutes into the future
        $this->travel(6)->minutes();

        // Code should now be expired
        $this->assertTrue($resetCode->fresh()->expires_at->isPast());
    }
}