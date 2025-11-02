<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Models\MapLocation;
use App\Models\MapLocationType;
use App\Models\PatrolLog;
use App\Models\Security;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatrolCheckinTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function security_user_can_check_in_at_location(): void
    {
        $security = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $security->id]);

        $locationType = MapLocationType::factory()->create(['name' => 'Patrol Point']);
        $location = MapLocation::factory()->create([
            'type_id' => $locationType->id,
            'name' => 'Main Gate',
            'is_active' => true,
        ]);

        $this->actingAs($security);

        $response = $this->post(route('security.patrol-checkin.store'), [
            'map_location_id' => $location->id,
            'notes' => 'All clear',
            'latitude' => 10.3157,
            'longitude' => 123.8854,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('patrol_logs', [
            'security_user_id' => $security->id,
            'map_location_id' => $location->id,
            'notes' => 'All clear',
        ]);
    }

    /** @test */
    public function patrol_log_stores_gps_coordinates(): void
    {
        $security = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $security->id]);

        $locationType = MapLocationType::factory()->create();
        $location = MapLocation::factory()->create([
            'type_id' => $locationType->id,
            'is_active' => true,
        ]);

        $patrolLog = PatrolLog::create([
            'security_user_id' => $security->id,
            'map_location_id' => $location->id,
            'checked_in_at' => now(),
            'notes' => 'Regular patrol',
            'latitude' => 10.3157,
            'longitude' => 123.8854,
        ]);

        $this->assertEquals('10.3157', $patrolLog->latitude);
        $this->assertEquals('123.8854', $patrolLog->longitude);
    }

    /** @test */
    public function patrol_log_belongs_to_security_user(): void
    {
        $security = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $security->id]);

        $locationType = MapLocationType::factory()->create();
        $location = MapLocation::factory()->create([
            'type_id' => $locationType->id,
            'is_active' => true,
        ]);

        $patrolLog = PatrolLog::create([
            'security_user_id' => $security->id,
            'map_location_id' => $location->id,
            'checked_in_at' => now(),
        ]);

        $this->assertInstanceOf(User::class, $patrolLog->securityUser);
        $this->assertEquals($security->id, $patrolLog->securityUser->id);
    }

    /** @test */
    public function patrol_log_belongs_to_map_location(): void
    {
        $security = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $security->id]);

        $locationType = MapLocationType::factory()->create();
        $location = MapLocation::factory()->create([
            'type_id' => $locationType->id,
            'name' => 'East Gate',
            'is_active' => true,
        ]);

        $patrolLog = PatrolLog::create([
            'security_user_id' => $security->id,
            'map_location_id' => $location->id,
            'checked_in_at' => now(),
        ]);

        $this->assertInstanceOf(MapLocation::class, $patrolLog->mapLocation);
        $this->assertEquals('East Gate', $patrolLog->mapLocation->name);
    }

    /** @test */
    public function security_user_can_have_multiple_patrol_logs(): void
    {
        $security = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $security->id]);

        $locationType = MapLocationType::factory()->create();
        $location1 = MapLocation::factory()->create([
            'type_id' => $locationType->id,
            'is_active' => true,
        ]);
        $location2 = MapLocation::factory()->create([
            'type_id' => $locationType->id,
            'is_active' => true,
        ]);

        PatrolLog::create([
            'security_user_id' => $security->id,
            'map_location_id' => $location1->id,
            'checked_in_at' => now(),
        ]);

        PatrolLog::create([
            'security_user_id' => $security->id,
            'map_location_id' => $location2->id,
            'checked_in_at' => now()->addHour(),
        ]);

        $this->assertCount(2, PatrolLog::where('security_user_id', $security->id)->get());
    }

    /** @test */
    public function map_location_can_have_multiple_patrol_logs(): void
    {
        $security1 = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $security1->id]);

        $security2 = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $security2->id]);

        $locationType = MapLocationType::factory()->create();
        $location = MapLocation::factory()->create([
            'type_id' => $locationType->id,
            'is_active' => true,
        ]);

        PatrolLog::create([
            'security_user_id' => $security1->id,
            'map_location_id' => $location->id,
            'checked_in_at' => now(),
        ]);

        PatrolLog::create([
            'security_user_id' => $security2->id,
            'map_location_id' => $location->id,
            'checked_in_at' => now()->addHour(),
        ]);

        $this->assertCount(2, PatrolLog::where('map_location_id', $location->id)->get());
    }

    /** @test */
    public function patrol_log_records_check_in_timestamp(): void
    {
        $security = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $security->id]);

        $locationType = MapLocationType::factory()->create();
        $location = MapLocation::factory()->create([
            'type_id' => $locationType->id,
            'is_active' => true,
        ]);

        $checkinTime = now();

        $patrolLog = PatrolLog::create([
            'security_user_id' => $security->id,
            'map_location_id' => $location->id,
            'checked_in_at' => $checkinTime,
        ]);

        $this->assertTrue($patrolLog->checked_in_at->equalTo($checkinTime));
    }

    /** @test */
    public function non_security_users_cannot_access_patrol_scanner(): void
    {
        $student = User::factory()->create(['user_type' => UserType::Student]);

        $this->actingAs($student);

        $response = $this->get(route('security.patrol-scanner'));

        $response->assertForbidden();
    }

    /** @test */
    public function non_security_users_cannot_check_in(): void
    {
        $reporter = User::factory()->create(['user_type' => UserType::Reporter]);

        $locationType = MapLocationType::factory()->create();
        $location = MapLocation::factory()->create([
            'type_id' => $locationType->id,
            'is_active' => true,
        ]);

        $this->actingAs($reporter);

        $response = $this->post(route('security.patrol-checkin.store'), [
            'map_location_id' => $location->id,
            'notes' => 'Trying to check in',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function guest_cannot_access_patrol_features(): void
    {
        $response = $this->get(route('security.patrol-scanner'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function patrol_check_in_requires_valid_map_location(): void
    {
        $security = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $security->id]);

        $this->actingAs($security);

        $response = $this->post(route('security.patrol-checkin.store'), [
            'map_location_id' => 999999, // Non-existent location
            'notes' => 'Invalid location',
        ]);

        $response->assertSessionHasErrors('map_location_id');
    }

    /** @test */
    public function patrol_log_can_be_created_without_notes(): void
    {
        $security = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $security->id]);

        $locationType = MapLocationType::factory()->create();
        $location = MapLocation::factory()->create([
            'type_id' => $locationType->id,
            'is_active' => true,
        ]);

        $patrolLog = PatrolLog::create([
            'security_user_id' => $security->id,
            'map_location_id' => $location->id,
            'checked_in_at' => now(),
        ]);

        $this->assertNull($patrolLog->notes);
        $this->assertDatabaseHas('patrol_logs', [
            'security_user_id' => $security->id,
            'map_location_id' => $location->id,
        ]);
    }

    /** @test */
    public function patrol_log_can_be_created_without_gps_coordinates(): void
    {
        $security = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $security->id]);

        $locationType = MapLocationType::factory()->create();
        $location = MapLocation::factory()->create([
            'type_id' => $locationType->id,
            'is_active' => true,
        ]);

        $patrolLog = PatrolLog::create([
            'security_user_id' => $security->id,
            'map_location_id' => $location->id,
            'checked_in_at' => now(),
        ]);

        $this->assertNull($patrolLog->latitude);
        $this->assertNull($patrolLog->longitude);
    }

    /** @test */
    public function security_can_view_their_patrol_history(): void
    {
        $security = User::factory()->create(['user_type' => UserType::Security]);
        Security::factory()->create(['user_id' => $security->id]);

        $this->actingAs($security);

        $response = $this->get(route('security.patrol-history'));

        $response->assertOk();
        $response->assertViewIs('security.patrol-history');
    }

    /** @test */
    public function admin_can_view_all_patrol_history(): void
    {
        $admin = User::factory()->create(['user_type' => UserType::GlobalAdministrator]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.patrol-history'));

        $response->assertOk();
        $response->assertViewIs('admin.patrol-history');
    }
}
