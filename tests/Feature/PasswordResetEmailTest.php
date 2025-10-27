<?php

namespace Tests\Feature;

use App\Mail\PasswordResetMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_email_is_sent_when_requesting_password_reset()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Request password reset
        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        // Assert email was sent
        Mail::assertSent(PasswordResetMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        // Assert response is correct
        $response->assertRedirect('/reset-password?email=test%40example.com');
        $response->assertSessionHas('status', 'Password reset code has been sent to your email!');
    }

    public function test_email_contains_correct_reset_code()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Request password reset
        $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        // Get the created reset code
        $resetCode = PasswordResetCode::where('email', 'test@example.com')->first();

        // Assert email was sent with correct code
        Mail::assertSent(PasswordResetMail::class, function ($mail) use ($resetCode) {
            return $mail->code === $resetCode->code;
        });
    }

    public function test_email_has_correct_subject()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Request password reset
        $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        // Assert email has correct subject
        Mail::assertSent(PasswordResetMail::class, function ($mail) {
            return $mail->envelope()->subject === 'MLUC Sentinel - Password Reset Code';
        });
    }

    public function test_email_is_not_sent_for_invalid_email()
    {
        // Try to request password reset with invalid email
        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        // Assert no email was sent
        Mail::assertNothingSent();

        // Assert validation error
        $response->assertSessionHasErrors(['email']);
    }

    public function test_email_sending_failure_is_handled_gracefully()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Mock Mail to throw an exception
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new \Exception('SMTP connection failed'));

        // Request password reset
        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        // Should still redirect successfully even if email fails
        $response->assertRedirect('/reset-password?email=test%40example.com');
        $response->assertSessionHas('status', 'Password reset code has been sent to your email!');

        // Reset code should still be created
        $this->assertDatabaseHas('password_reset_codes', [
            'email' => 'test@example.com',
            'is_used' => false,
        ]);
    }

    public function test_multiple_emails_are_sent_for_multiple_requests()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Request password reset multiple times
        $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        // Should have sent 2 emails
        Mail::assertSent(PasswordResetMail::class, 2);
    }

    public function test_email_contains_reset_link()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Request password reset
        $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        // Assert email was sent
        Mail::assertSent(PasswordResetMail::class, function ($mail) {
            // Check if the email content contains the reset link
            $content = $mail->render();

            return str_contains($content, route('password.reset', ['email' => 'test@example.com']));
        });
    }

    public function test_email_contains_expiration_warning()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Request password reset
        $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        // Assert email was sent
        Mail::assertSent(PasswordResetMail::class, function ($mail) {
            $content = $mail->render();

            return str_contains($content, '5 minutes') && str_contains($content, 'expire');
        });
    }

    public function test_email_contains_mluc_sentinel_branding()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Request password reset
        $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        // Assert email was sent
        Mail::assertSent(PasswordResetMail::class, function ($mail) {
            $content = $mail->render();

            return str_contains($content, 'MLUC Sentinel') &&
                   str_contains($content, 'Digital Parking Management System');
        });
    }
}
