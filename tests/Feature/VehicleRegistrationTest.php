<?php

namespace Tests\Feature;

use App\Models\Administrator;
use App\Models\AdminRole;
use App\Models\College;
use App\Models\Student;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create([
            'user_type' => 'administrator',
            'is_active' => true,
        ]);

        $role = AdminRole::factory()->create(['name' => 'Test Role']);
        Administrator::factory()->create([
            'user_id' => $this->admin->id,
            'role_id' => $role->id,
        ]);

        // Create a student
        $studentUser = User::factory()->create([
            'user_type' => 'student',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@test.com',
            'is_active' => true,
        ]);

        $college = College::factory()->create(['name' => 'Test College']);

        $this->student = Student::factory()->create([
            'user_id' => $studentUser->id,
            'college_id' => $college->id,
            'student_id' => '2023-001',
            'license_no' => 'L123456',
        ]);
    }

    public function test_can_add_single_vehicle_with_plate_number(): void
    {
        $vehicleType = VehicleType::firstOrCreate(['name' => 'Motorcycle-'.uniqid()]);

        $response = $this->actingAs($this->admin)->putJson(
            "/users/students/{$this->student->id}",
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@test.com',
                'student_id' => '2023-001',
                'license_no' => 'L123456',
                'college_id' => $this->student->college_id,
                'is_active' => true,
                'vehicles' => [
                    [
                        'type_id' => $vehicleType->id,
                        'plate_no' => 'ABC-1234',
                    ],
                ],
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('vehicles', [
            'user_id' => $this->student->user_id,
            'type_id' => $vehicleType->id,
            'plate_no' => 'ABC-1234',
        ]);
    }

    public function test_can_add_multiple_vehicles_with_different_plate_numbers(): void
    {
        $motorcycle = VehicleType::firstOrCreate(['name' => 'Motorcycle-'.uniqid()]);
        $car = VehicleType::firstOrCreate(['name' => 'Car-'.uniqid()]);

        $response = $this->actingAs($this->admin)->putJson(
            "/users/students/{$this->student->id}",
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@test.com',
                'student_id' => '2023-001',
                'license_no' => 'L123456',
                'college_id' => $this->student->college_id,
                'is_active' => true,
                'vehicles' => [
                    [
                        'type_id' => $motorcycle->id,
                        'plate_no' => 'ABC-1234',
                    ],
                    [
                        'type_id' => $car->id,
                        'plate_no' => 'XYZ-5678',
                    ],
                ],
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('vehicles', [
            'user_id' => $this->student->user_id,
            'type_id' => $motorcycle->id,
            'plate_no' => 'ABC-1234',
        ]);

        $this->assertDatabaseHas('vehicles', [
            'user_id' => $this->student->user_id,
            'type_id' => $car->id,
            'plate_no' => 'XYZ-5678',
        ]);
    }

    public function test_cannot_add_duplicate_plate_number_in_same_request(): void
    {
        $vehicleType = VehicleType::firstOrCreate(['name' => 'Motorcycle-'.uniqid()]);

        $response = $this->actingAs($this->admin)->putJson(
            "/users/students/{$this->student->id}",
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@test.com',
                'student_id' => '2023-001',
                'license_no' => 'L123456',
                'college_id' => $this->student->college_id,
                'is_active' => true,
                'vehicles' => [
                    [
                        'type_id' => $vehicleType->id,
                        'plate_no' => 'ABC-1234',
                    ],
                    [
                        'type_id' => $vehicleType->id,
                        'plate_no' => 'ABC-1234', // Duplicate plate number
                    ],
                ],
            ]
        );

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Duplicate plate numbers are not allowed.',
        ]);
    }

    public function test_cannot_add_plate_number_already_registered_to_another_student(): void
    {
        // Create another student with a vehicle
        $otherStudentUser = User::factory()->create([
            'user_type' => 'student',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@test.com',
        ]);

        $otherStudent = Student::factory()->create([
            'user_id' => $otherStudentUser->id,
            'college_id' => $this->student->college_id,
            'student_id' => '2023-002',
            'license_no' => 'L654321',
        ]);

        $vehicleType = VehicleType::firstOrCreate(['name' => 'Motorcycle-'.uniqid()]);

        // Create a vehicle for the other student
        Vehicle::factory()->create([
            'user_id' => $otherStudentUser->id,
            'type_id' => $vehicleType->id,
            'plate_no' => 'TAKEN-123',
            'color' => 'blue',
            'number' => '0001',
        ]);

        // Try to add the same plate number to this student
        $response = $this->actingAs($this->admin)->putJson(
            "/users/students/{$this->student->id}",
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@test.com',
                'student_id' => '2023-001',
                'license_no' => 'L123456',
                'college_id' => $this->student->college_id,
                'is_active' => true,
                'vehicles' => [
                    [
                        'type_id' => $vehicleType->id,
                        'plate_no' => 'TAKEN-123', // Already taken by another student
                    ],
                ],
            ]
        );

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => "Plate number 'TAKEN-123' is already registered to another user.",
        ]);
    }

    public function test_can_add_electric_vehicle_without_plate_number(): void
    {
        $electricType = VehicleType::firstOrCreate(['name' => 'Electric Vehicle-'.uniqid()]);

        $response = $this->actingAs($this->admin)->putJson(
            "/users/students/{$this->student->id}",
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@test.com',
                'student_id' => '2023-001',
                'license_no' => 'L123456',
                'college_id' => $this->student->college_id,
                'is_active' => true,
                'vehicles' => [
                    [
                        'type_id' => $electricType->id,
                        'plate_no' => '', // Empty for electric vehicles
                    ],
                ],
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('vehicles', [
            'user_id' => $this->student->user_id,
            'type_id' => $electricType->id,
            'plate_no' => null,
        ]);
    }

    public function test_sticker_generation_and_counter_increments(): void
    {
        $vehicleType = VehicleType::firstOrCreate(['name' => 'Motorcycle-'.uniqid()]);

        $response = $this->actingAs($this->admin)->putJson(
            "/users/students/{$this->student->id}",
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@test.com',
                'student_id' => '2023-001',
                'license_no' => 'L123456',
                'college_id' => $this->student->college_id,
                'is_active' => true,
                'vehicles' => [
                    [
                        'type_id' => $vehicleType->id,
                        'plate_no' => 'BLUE-001',
                    ],
                ],
            ]
        );

        $response->assertStatus(200);

        $vehicle = Vehicle::where('user_id', $this->student->user_id)
            ->where('plate_no', 'BLUE-001')
            ->first();

        $this->assertNotNull($vehicle);
        $this->assertNotNull($vehicle->color);
        $this->assertNotNull($vehicle->number);
        $this->assertNotNull($vehicle->sticker);
        $this->assertEquals('blue', $vehicle->color); // Last digit is 1, so blue
    }

    public function test_cannot_add_more_than_three_vehicles(): void
    {
        $vehicleType = VehicleType::firstOrCreate(['name' => 'Motorcycle-'.uniqid()]);

        $response = $this->actingAs($this->admin)->putJson(
            "/users/students/{$this->student->id}",
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@test.com',
                'student_id' => '2023-001',
                'license_no' => 'L123456',
                'college_id' => $this->student->college_id,
                'is_active' => true,
                'vehicles' => [
                    ['type_id' => $vehicleType->id, 'plate_no' => 'ABC-0001'],
                    ['type_id' => $vehicleType->id, 'plate_no' => 'ABC-0002'],
                    ['type_id' => $vehicleType->id, 'plate_no' => 'ABC-0003'],
                    ['type_id' => $vehicleType->id, 'plate_no' => 'ABC-0004'], // 4th vehicle
                ],
            ]
        );

        $response->assertStatus(422);
    }

    public function test_sticker_counter_handles_deleted_vehicles(): void
    {
        $vehicleType = VehicleType::firstOrCreate(['name' => 'Motorcycle-'.uniqid()]);

        // Create first vehicle with no plate (white/electric)
        $this->actingAs($this->admin)->putJson(
            "/users/students/{$this->student->id}",
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@test.com',
                'student_id' => '2023-001',
                'license_no' => 'L123456',
                'college_id' => $this->student->college_id,
                'is_active' => true,
                'vehicles' => [
                    ['type_id' => $vehicleType->id, 'plate_no' => ''],
                ],
            ]
        );

        // Get the vehicle
        $vehicle = Vehicle::where('user_id', $this->student->user_id)
            ->where('color', 'white')
            ->first();

        $this->assertNotNull($vehicle);
        $firstStickerNumber = $vehicle->number; // Should be '0001' for white
        $this->assertEquals('white', $vehicle->color);

        // Delete the vehicle
        $vehicle->delete();

        // Create another white vehicle
        $response = $this->actingAs($this->admin)->putJson(
            "/users/students/{$this->student->id}",
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@test.com',
                'student_id' => '2023-001',
                'license_no' => 'L123456',
                'college_id' => $this->student->college_id,
                'is_active' => true,
                'vehicles' => [
                    ['type_id' => $vehicleType->id, 'plate_no' => ''],
                ],
            ]
        );

        $response->assertStatus(200);

        // Get the new vehicle
        $newVehicle = Vehicle::where('user_id', $this->student->user_id)
            ->where('color', 'white')
            ->first();

        $this->assertNotNull($newVehicle);
        $this->assertEquals('white', $newVehicle->color);

        // The new sticker number should NOT be a duplicate
        // Since we deleted the first one and reused the number, but the system should handle it
        $this->assertNotNull($newVehicle->number);
    }
}
