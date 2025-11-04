<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Jobs\SendPaymentReceiptEmail;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PaymentProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $student;

    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create([
            'user_type' => UserType::GlobalAdministrator,
        ]);

        // Create student user
        $this->student = User::factory()->create([
            'user_type' => UserType::Student,
        ]);

        // Create vehicle type
        $vehicleType = VehicleType::factory()->create();

        // Create vehicle
        $this->vehicle = Vehicle::factory()->create([
            'user_id' => $this->student->id,
            'type_id' => $vehicleType->id,
        ]);
    }

    /**
     * Test that a pending payment can be created
     */
    public function test_payment_can_be_created(): void
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->student->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => 'pending',
            'amount' => \App\Models\Fee::getAmount('sticker_fee', 15.00),
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'user_id' => $this->student->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test that admin can confirm a payment
     */
    public function test_admin_can_confirm_payment(): void
    {
        Queue::fake();

        $payment = Payment::factory()->create([
            'user_id' => $this->student->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => 'pending',
            'amount' => \App\Models\Fee::getAmount('sticker_fee', 15.00),
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.stickers.markAsPaid', $payment));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Payment marked as paid!',
            ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
        ]);

        // Assert that email job was dispatched
        Queue::assertPushed(SendPaymentReceiptEmail::class);
    }

    /**
     * Test that admin can cancel a payment
     */
    public function test_admin_can_cancel_payment(): void
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->student->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => 'pending',
            'amount' => \App\Models\Fee::getAmount('sticker_fee', 15.00),
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.stickers.cancel', $payment));

        $response->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'cancelled',
        ]);
    }

    /**
     * Test that payment confirmation is logged
     */
    public function test_payment_confirmation_is_logged(): void
    {
        Queue::fake();
        Log::shouldReceive('channel')
            ->with('payments')
            ->andReturnSelf();
        Log::shouldReceive('info')
            ->once()
            ->with('Payment confirmed', \Mockery::type('array'));

        $payment = Payment::factory()->create([
            'user_id' => $this->student->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.stickers.markAsPaid', $payment));
    }

    /**
     * Test that non-admin cannot confirm payment
     */
    public function test_non_admin_cannot_confirm_payment(): void
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->student->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->student)
            ->postJson(route('admin.stickers.markAsPaid', $payment));

        $response->assertStatus(403);
    }

    /**
     * Test batch payment processing
     */
    public function test_batch_payment_can_be_processed(): void
    {
        Queue::fake();

        $batchId = uniqid('batch_');

        // Create multiple payments in a batch
        $payment1 = Payment::factory()->create([
            'user_id' => $this->student->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => 'pending',
            'batch_id' => $batchId,
        ]);

        $vehicle2 = Vehicle::factory()->create([
            'user_id' => $this->student->id,
        ]);

        $payment2 = Payment::factory()->create([
            'user_id' => $this->student->id,
            'vehicle_id' => $vehicle2->id,
            'status' => 'pending',
            'batch_id' => $batchId,
        ]);

        // Confirm the first payment in batch
        $this->actingAs($this->admin)
            ->postJson(route('admin.stickers.markAsPaid', $payment1));

        // Both payments should be marked as paid
        $this->assertDatabaseHas('payments', [
            'id' => $payment1->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment2->id,
            'status' => 'paid',
        ]);
    }
}
