<?php

namespace Tests\Unit;

use App\Models\PasswordResetCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_password_reset_code()
    {
        $resetCode = PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'is_used' => false
        ]);

        $this->assertInstanceOf(PasswordResetCode::class, $resetCode);
        $this->assertEquals('test@example.com', $resetCode->email);
        $this->assertEquals('123456', $resetCode->code);
        $this->assertFalse($resetCode->is_used);
    }

    public function test_expires_at_is_casted_to_datetime()
    {
        $resetCode = PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'is_used' => false
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $resetCode->expires_at);
    }

    public function test_is_used_is_casted_to_boolean()
    {
        $resetCode = PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'is_used' => true
        ]);

        $this->assertTrue($resetCode->is_used);
        $this->assertIsBool($resetCode->is_used);
    }

    public function test_can_find_valid_reset_code()
    {
        // Create a valid reset code
        PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'is_used' => false
        ]);

        // Find the valid code
        $validCode = PasswordResetCode::where('email', 'test@example.com')
            ->where('code', '123456')
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        $this->assertNotNull($validCode);
        $this->assertEquals('123456', $validCode->code);
    }

    public function test_cannot_find_expired_reset_code()
    {
        // Create an expired reset code
        PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'expires_at' => now()->subMinutes(1), // Expired
            'is_used' => false
        ]);

        // Try to find the expired code
        $expiredCode = PasswordResetCode::where('email', 'test@example.com')
            ->where('code', '123456')
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        $this->assertNull($expiredCode);
    }

    public function test_cannot_find_used_reset_code()
    {
        // Create a used reset code
        PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'is_used' => true // Already used
        ]);

        // Try to find the used code
        $usedCode = PasswordResetCode::where('email', 'test@example.com')
            ->where('code', '123456')
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        $this->assertNull($usedCode);
    }

    public function test_can_mark_reset_code_as_used()
    {
        $resetCode = PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'is_used' => false
        ]);

        // Mark as used
        $resetCode->is_used = true;
        $resetCode->save();

        $this->assertTrue($resetCode->fresh()->is_used);
    }

    public function test_can_delete_all_codes_for_email()
    {
        // Create multiple codes for the same email
        PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '111111',
            'expires_at' => now()->addMinutes(5),
            'is_used' => false
        ]);

        PasswordResetCode::create([
            'email' => 'test@example.com',
            'code' => '222222',
            'expires_at' => now()->addMinutes(5),
            'is_used' => true
        ]);

        // Delete all codes for this email
        PasswordResetCode::where('email', 'test@example.com')->delete();

        // No codes should exist for this email
        $this->assertDatabaseMissing('password_reset_codes', [
            'email' => 'test@example.com'
        ]);
    }

    public function test_reset_code_has_correct_fillable_attributes()
    {
        $resetCode = new PasswordResetCode();
        $fillable = $resetCode->getFillable();

        $expectedFillable = ['email', 'code', 'expires_at', 'is_used'];
        
        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }
}