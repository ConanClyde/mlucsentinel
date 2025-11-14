<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Models\Fee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['user_type' => UserType::GlobalAdministrator]);
    }

    /** @test */
    public function admin_can_view_fees(): void
    {
        Fee::factory()->create(['name' => 'sticker_fee', 'amount' => 15.00]);

        $this->actingAs($this->admin);

        $response = $this->getJson('/api/fees');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'display_name', 'amount', 'description'],
            ],
        ]);
    }

    /** @test */
    public function admin_can_update_fee_amount(): void
    {
        $fee = Fee::factory()->create([
            'name' => 'sticker_fee',
            'display_name' => 'Sticker Fee',
            'amount' => 15.00,
        ]);

        $this->actingAs($this->admin);

        $response = $this->putJson("/api/fees/{$fee->id}", [
            'amount' => 25.00,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Fee updated successfully',
        ]);

        $this->assertDatabaseHas('fees', [
            'id' => $fee->id,
            'amount' => 25.00,
        ]);
    }

    /** @test */
    public function fee_update_requires_valid_amount(): void
    {
        $fee = Fee::factory()->create(['amount' => 15.00]);

        $this->actingAs($this->admin);

        $response = $this->putJson("/api/fees/{$fee->id}", [
            'amount' => -10,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function fee_update_is_rate_limited(): void
    {
        $fee = Fee::factory()->create(['amount' => 15.00]);

        $this->actingAs($this->admin);

        // Make 11 requests (over the limit of 10 per minute)
        for ($i = 0; $i < 11; $i++) {
            $response = $this->putJson("/api/fees/{$fee->id}", ['amount' => 20.00]);
        }

        $response->assertStatus(429);
    }

    /** @test */
    public function fee_get_amount_returns_default_when_not_found(): void
    {
        $amount = Fee::getAmount('nonexistent_fee', 20.00);

        $this->assertEquals(20.00, $amount);
    }

    /** @test */
    public function fee_get_amount_returns_database_value_when_found(): void
    {
        Fee::factory()->create([
            'name' => 'sticker_fee',
            'amount' => 30.00,
        ]);

        $amount = Fee::getAmount('sticker_fee', 15.00);

        $this->assertEquals(30.00, $amount);
    }
}
