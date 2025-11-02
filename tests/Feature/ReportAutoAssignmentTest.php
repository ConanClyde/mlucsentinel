<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Models\Administrator;
use App\Models\AdminRole;
use App\Models\College;
use App\Models\Report;
use App\Models\Security;
use App\Models\Student;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\ViolationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAutoAssignmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_assigns_student_violations_to_sas_admin(): void
    {
        // Create SAS Admin
        $sasRole = AdminRole::factory()->create(['name' => 'SAS (Student Affairs & Services)']);
        $sasAdmin = User::factory()->create(['user_type' => UserType::Administrator]);
        Administrator::factory()->create([
            'user_id' => $sasAdmin->id,
            'role_id' => $sasRole->id,
        ]);

        // Test the auto-assignment logic
        $assignment = Report::getAutoAssignedAdmin('student');

        $this->assertNotNull($assignment);
        $this->assertEquals($sasAdmin->id, $assignment['assigned_to']);
        $this->assertEquals('administrator', $assignment['assigned_to_user_type']);
    }

    /** @test */
    public function it_assigns_staff_violations_to_chancellor_admin(): void
    {
        // Create Chancellor Admin
        $chancellorRole = AdminRole::factory()->create(['name' => 'Chancellor']);
        $chancellorAdmin = User::factory()->create(['user_type' => UserType::Administrator]);
        Administrator::factory()->create([
            'user_id' => $chancellorAdmin->id,
            'role_id' => $chancellorRole->id,
        ]);

        // Test the auto-assignment logic
        $assignment = Report::getAutoAssignedAdmin('staff');

        $this->assertNotNull($assignment);
        $this->assertEquals($chancellorAdmin->id, $assignment['assigned_to']);
        $this->assertEquals('administrator', $assignment['assigned_to_user_type']);
    }

    /** @test */
    public function it_assigns_security_violations_to_chancellor_admin(): void
    {
        // Create Chancellor Admin
        $chancellorRole = AdminRole::factory()->create(['name' => 'Chancellor']);
        $chancellorAdmin = User::factory()->create(['user_type' => UserType::Administrator]);
        Administrator::factory()->create([
            'user_id' => $chancellorAdmin->id,
            'role_id' => $chancellorRole->id,
        ]);

        // Test the auto-assignment logic
        $assignment = Report::getAutoAssignedAdmin('security');

        $this->assertNotNull($assignment);
        $this->assertEquals($chancellorAdmin->id, $assignment['assigned_to']);
        $this->assertEquals('administrator', $assignment['assigned_to_user_type']);
    }

    /** @test */
    public function it_assigns_stakeholder_violations_to_chancellor_admin(): void
    {
        // Create Chancellor Admin
        $chancellorRole = AdminRole::factory()->create(['name' => 'Chancellor']);
        $chancellorAdmin = User::factory()->create(['user_type' => UserType::Administrator]);
        Administrator::factory()->create([
            'user_id' => $chancellorAdmin->id,
            'role_id' => $chancellorRole->id,
        ]);

        // Test the auto-assignment logic
        $assignment = Report::getAutoAssignedAdmin('stakeholder');

        $this->assertNotNull($assignment);
        $this->assertEquals($chancellorAdmin->id, $assignment['assigned_to']);
        $this->assertEquals('administrator', $assignment['assigned_to_user_type']);
    }

    /** @test */
    public function it_returns_null_when_no_sas_admin_exists_for_student_violations(): void
    {
        // Create a different admin role, but not SAS
        $marketingRole = AdminRole::factory()->create(['name' => 'Marketing']);
        $marketingAdmin = User::factory()->create(['user_type' => UserType::Administrator]);
        Administrator::factory()->create([
            'user_id' => $marketingAdmin->id,
            'role_id' => $marketingRole->id,
        ]);

        $assignment = Report::getAutoAssignedAdmin('student');

        $this->assertNull($assignment);
    }

    /** @test */
    public function it_returns_null_when_no_chancellor_admin_exists(): void
    {
        // Create a different admin role, but not Chancellor
        $sasRole = AdminRole::factory()->create(['name' => 'SAS (Student Affairs & Services)']);
        $sasAdmin = User::factory()->create(['user_type' => UserType::Administrator]);
        Administrator::factory()->create([
            'user_id' => $sasAdmin->id,
            'role_id' => $sasRole->id,
        ]);

        $assignment = Report::getAutoAssignedAdmin('staff');

        $this->assertNull($assignment);
    }

    /** @test */
    public function it_prefers_first_admin_when_multiple_sas_admins_exist(): void
    {
        // Create multiple SAS Admins
        $sasRole = AdminRole::factory()->create(['name' => 'SAS (Student Affairs & Services)']);

        $sasAdmin1 = User::factory()->create(['user_type' => UserType::Administrator]);
        Administrator::factory()->create([
            'user_id' => $sasAdmin1->id,
            'role_id' => $sasRole->id,
        ]);

        $sasAdmin2 = User::factory()->create(['user_type' => UserType::Administrator]);
        Administrator::factory()->create([
            'user_id' => $sasAdmin2->id,
            'role_id' => $sasRole->id,
        ]);

        $assignment = Report::getAutoAssignedAdmin('student');

        $this->assertNotNull($assignment);
        // Should return the first one created
        $this->assertEquals($sasAdmin1->id, $assignment['assigned_to']);
    }

    /** @test */
    public function report_relationships_work_correctly(): void
    {
        $college = College::factory()->create();
        $violationType = ViolationType::factory()->create();
        $vehicleType = VehicleType::factory()->create();

        // Create reporter (security)
        $reporter = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $reporter->id]);

        // Create violator (student)
        $violator = User::factory()->create(['user_type' => UserType::Student]);
        Student::factory()->create([
            'user_id' => $violator->id,
            'college_id' => $college->id,
        ]);
        $violatorVehicle = Vehicle::factory()->create([
            'user_id' => $violator->id,
            'type_id' => $vehicleType->id,
        ]);

        // Create admin
        $adminRole = AdminRole::factory()->create(['name' => 'SAS (Student Affairs & Services)']);
        $admin = User::factory()->create(['user_type' => UserType::Administrator]);
        Administrator::factory()->create([
            'user_id' => $admin->id,
            'role_id' => $adminRole->id,
        ]);

        // Create report
        $report = Report::create([
            'reported_by' => $reporter->id,
            'violator_vehicle_id' => $violatorVehicle->id,
            'violation_type_id' => $violationType->id,
            'description' => 'Illegal parking',
            'location' => 'Parking Lot A',
            'assigned_to' => $admin->id,
            'assigned_to_user_type' => 'administrator',
            'status' => 'pending',
            'reported_at' => now(),
        ]);

        // Test relationships
        $this->assertInstanceOf(User::class, $report->reportedBy);
        $this->assertEquals($reporter->id, $report->reportedBy->id);

        $this->assertInstanceOf(Vehicle::class, $report->violatorVehicle);
        $this->assertEquals($violatorVehicle->id, $report->violatorVehicle->id);

        $this->assertInstanceOf(ViolationType::class, $report->violationType);
        $this->assertEquals($violationType->id, $report->violationType->id);

        $this->assertInstanceOf(User::class, $report->assignedTo);
        $this->assertEquals($admin->id, $report->assignedTo->id);
    }

    /** @test */
    public function user_can_have_multiple_reports(): void
    {
        $college = College::factory()->create();
        $violationType = ViolationType::factory()->create();
        $vehicleType = VehicleType::factory()->create();

        $reporter = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $reporter->id]);

        $violator1 = User::factory()->create(['user_type' => UserType::Student]);
        Student::factory()->create([
            'user_id' => $violator1->id,
            'college_id' => $college->id,
        ]);
        $vehicle1 = Vehicle::factory()->create([
            'user_id' => $violator1->id,
            'type_id' => $vehicleType->id,
        ]);

        $violator2 = User::factory()->create(['user_type' => UserType::Student]);
        Student::factory()->create([
            'user_id' => $violator2->id,
            'college_id' => $college->id,
        ]);
        $vehicle2 = Vehicle::factory()->create([
            'user_id' => $violator2->id,
            'type_id' => $vehicleType->id,
        ]);

        Report::create([
            'reported_by' => $reporter->id,
            'violator_vehicle_id' => $vehicle1->id,
            'violation_type_id' => $violationType->id,
            'description' => 'First violation',
            'location' => 'Lot A',
            'status' => 'pending',
            'reported_at' => now(),
        ]);

        Report::create([
            'reported_by' => $reporter->id,
            'violator_vehicle_id' => $vehicle2->id,
            'violation_type_id' => $violationType->id,
            'description' => 'Second violation',
            'location' => 'Lot B',
            'status' => 'pending',
            'reported_at' => now(),
        ]);

        $this->assertCount(2, $reporter->reports);
    }

    /** @test */
    public function vehicle_can_have_multiple_violation_reports(): void
    {
        $college = College::factory()->create();
        $violationType1 = ViolationType::factory()->create(['name' => 'Illegal Parking']);
        $violationType2 = ViolationType::factory()->create(['name' => 'Speeding']);
        $vehicleType = VehicleType::factory()->create();

        $reporter = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $reporter->id]);

        $violator = User::factory()->create(['user_type' => UserType::Student]);
        Student::factory()->create([
            'user_id' => $violator->id,
            'college_id' => $college->id,
        ]);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $violator->id,
            'type_id' => $vehicleType->id,
        ]);

        Report::create([
            'reported_by' => $reporter->id,
            'violator_vehicle_id' => $vehicle->id,
            'violation_type_id' => $violationType1->id,
            'description' => 'Parked illegally',
            'location' => 'Lot A',
            'status' => 'pending',
            'reported_at' => now(),
        ]);

        Report::create([
            'reported_by' => $reporter->id,
            'violator_vehicle_id' => $vehicle->id,
            'violation_type_id' => $violationType2->id,
            'description' => 'Driving too fast',
            'location' => 'Main Road',
            'status' => 'pending',
            'reported_at' => now()->addHour(),
        ]);

        $this->assertCount(2, $vehicle->violatorReports);
    }

    /** @test */
    public function report_can_be_created_with_map_coordinates(): void
    {
        $college = College::factory()->create();
        $violationType = ViolationType::factory()->create();
        $vehicleType = VehicleType::factory()->create();

        $reporter = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $reporter->id]);

        $violator = User::factory()->create(['user_type' => UserType::Student]);
        Student::factory()->create([
            'user_id' => $violator->id,
            'college_id' => $college->id,
        ]);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $violator->id,
            'type_id' => $vehicleType->id,
        ]);

        $report = Report::create([
            'reported_by' => $reporter->id,
            'violator_vehicle_id' => $vehicle->id,
            'violation_type_id' => $violationType->id,
            'description' => 'Violation with map pin',
            'location' => 'Building A',
            'pin_x' => 123.45,
            'pin_y' => 678.90,
            'status' => 'pending',
            'reported_at' => now(),
        ]);

        $this->assertEquals(123.45, $report->pin_x);
        $this->assertEquals(678.90, $report->pin_y);
    }
}
