<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Models\College;
use App\Models\Security;
use App\Models\Staff;
use App\Models\Stakeholder;
use App\Models\StakeholderType;
use App\Models\StickerCounter;
use App\Models\Student;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Services\StickerGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StickerGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected StickerGenerator $stickerGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stickerGenerator = new StickerGenerator;
        Storage::fake('public');
    }

    /** @test */
    public function it_determines_maroon_color_for_security_users(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('security');

        $this->assertEquals('maroon', $color);
    }

    /** @test */
    public function it_determines_maroon_color_for_staff_users(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('staff');

        $this->assertEquals('maroon', $color);
    }

    /** @test */
    public function it_determines_blue_color_for_student_with_plate_ending_in_1(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('student', null, 'ABC-1231');

        $this->assertEquals('blue', $color);
    }

    /** @test */
    public function it_determines_blue_color_for_student_with_plate_ending_in_2(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('student', null, 'XYZ-9872');

        $this->assertEquals('blue', $color);
    }

    /** @test */
    public function it_determines_green_color_for_student_with_plate_ending_in_3(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('student', null, 'ABC-1233');

        $this->assertEquals('green', $color);
    }

    /** @test */
    public function it_determines_green_color_for_student_with_plate_ending_in_4(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('student', null, 'ABC-1234');

        $this->assertEquals('green', $color);
    }

    /** @test */
    public function it_determines_yellow_color_for_student_with_plate_ending_in_5(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('student', null, 'ABC-1235');

        $this->assertEquals('yellow', $color);
    }

    /** @test */
    public function it_determines_yellow_color_for_student_with_plate_ending_in_6(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('student', null, 'ABC-1236');

        $this->assertEquals('yellow', $color);
    }

    /** @test */
    public function it_determines_pink_color_for_student_with_plate_ending_in_7(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('student', null, 'ABC-1237');

        $this->assertEquals('pink', $color);
    }

    /** @test */
    public function it_determines_pink_color_for_student_with_plate_ending_in_8(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('student', null, 'ABC-1238');

        $this->assertEquals('pink', $color);
    }

    /** @test */
    public function it_determines_orange_color_for_student_with_plate_ending_in_9(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('student', null, 'ABC-1239');

        $this->assertEquals('orange', $color);
    }

    /** @test */
    public function it_determines_orange_color_for_student_with_plate_ending_in_0(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('student', null, 'ABC-1230');

        $this->assertEquals('orange', $color);
    }

    /** @test */
    public function it_determines_white_color_for_student_with_no_plate_number(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('student', null, null);

        $this->assertEquals('white', $color);
    }

    /** @test */
    public function it_determines_black_color_for_visitor_stakeholder(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('stakeholder', 'Visitor');

        $this->assertEquals('black', $color);
    }

    /** @test */
    public function it_determines_white_color_for_guardian_stakeholder(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('stakeholder', 'Guardian');

        $this->assertEquals('white', $color);
    }

    /** @test */
    public function it_determines_white_color_for_service_provider_stakeholder(): void
    {
        $color = $this->stickerGenerator->determineStickerColor('stakeholder', 'Service Provider');

        $this->assertEquals('white', $color);
    }

    /** @test */
    public function it_generates_sequential_sticker_numbers_per_color(): void
    {
        $number1 = $this->stickerGenerator->generateNextStickerNumber('blue');
        $number2 = $this->stickerGenerator->generateNextStickerNumber('blue');
        $number3 = $this->stickerGenerator->generateNextStickerNumber('blue');

        $this->assertEquals('0001', $number1);
        $this->assertEquals('0002', $number2);
        $this->assertEquals('0003', $number3);
    }

    /** @test */
    public function it_generates_independent_sequences_for_different_colors(): void
    {
        $blueNumber1 = $this->stickerGenerator->generateNextStickerNumber('blue');
        $greenNumber1 = $this->stickerGenerator->generateNextStickerNumber('green');
        $blueNumber2 = $this->stickerGenerator->generateNextStickerNumber('blue');

        $this->assertEquals('0001', $blueNumber1);
        $this->assertEquals('0001', $greenNumber1);
        $this->assertEquals('0002', $blueNumber2);
    }

    /** @test */
    public function it_pads_sticker_numbers_with_zeros(): void
    {
        $number = $this->stickerGenerator->generateNextStickerNumber('blue');

        $this->assertEquals(4, strlen($number));
        $this->assertStringStartsWith('0', $number);
    }

    /** @test */
    public function it_generates_sticker_svg_file(): void
    {
        $vehicleType = VehicleType::factory()->create(['name' => 'Motorcycle']);
        $user = User::factory()->create([
            'user_type' => UserType::Security,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        Security::factory()->create(['user_id' => $user->id]);

        $vehicle = Vehicle::factory()->create([
            'user_id' => $user->id,
            'type_id' => $vehicleType->id,
            'plate_no' => 'ABC-1234',
            'color' => 'maroon',
            'number' => '0001',
        ]);

        $stickerPath = $this->stickerGenerator->generateVehicleSticker(
            'maroon-0001',
            'Motorcycle',
            'ABC-1234',
            'maroon',
            $vehicle->id
        );

        // Verify sticker path is returned
        $this->assertNotNull($stickerPath);
        $this->assertStringContainsString('/storage/stickers/', $stickerPath);
        $this->assertStringEndsWith('.svg', $stickerPath);

        // Verify file exists in storage
        $fileName = basename($stickerPath);
        Storage::disk('public')->assertExists('stickers/'.$fileName);

        // Verify SVG content
        $content = Storage::disk('public')->get('stickers/'.$fileName);
        $this->assertStringContainsString('<svg', $content);
        $this->assertStringContainsString('MLUC SENTINEL', $content);
        $this->assertStringContainsString('0001', $content);
    }

    /** @test */
    public function it_creates_sticker_counter_if_not_exists(): void
    {
        $this->assertDatabaseMissing('sticker_counters', ['color' => 'blue']);

        $this->stickerGenerator->generateNextStickerNumber('blue');

        $this->assertDatabaseHas('sticker_counters', [
            'color' => 'blue',
            'count' => 1,
        ]);
    }

    /** @test */
    public function it_increments_existing_sticker_counter(): void
    {
        StickerCounter::create(['color' => 'blue', 'count' => 5]);

        $number = $this->stickerGenerator->generateNextStickerNumber('blue');

        $this->assertEquals('0006', $number);
        $this->assertDatabaseHas('sticker_counters', [
            'color' => 'blue',
            'count' => 6,
        ]);
    }

    /** @test */
    public function it_skips_numbers_already_used_by_existing_vehicles(): void
    {
        $vehicleType = VehicleType::factory()->create();
        $user = User::factory()->create([
            'user_type' => UserType::Security,
        ]);
        Security::factory()->create(['user_id' => $user->id]);

        // Create vehicles with numbers 1, 2, and 4 (skipping 3)
        Vehicle::factory()->create([
            'user_id' => $user->id,
            'type_id' => $vehicleType->id,
            'color' => 'blue',
            'number' => '1',
        ]);
        Vehicle::factory()->create([
            'user_id' => $user->id,
            'type_id' => $vehicleType->id,
            'color' => 'blue',
            'number' => '2',
        ]);
        Vehicle::factory()->create([
            'user_id' => $user->id,
            'type_id' => $vehicleType->id,
            'color' => 'blue',
            'number' => '4',
        ]);

        // Generate next number - should be 5 (not 3, which is free but lower than counter)
        $number = $this->stickerGenerator->generateNextStickerNumber('blue');

        $this->assertEquals('0005', $number);
    }

    /** @test */
    public function it_generates_correct_filename_format(): void
    {
        $vehicleType = VehicleType::factory()->create();
        $user = User::factory()->create([
            'user_type' => UserType::Staff,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);
        Staff::factory()->create(['user_id' => $user->id]);

        $vehicle = Vehicle::factory()->create([
            'user_id' => $user->id,
            'type_id' => $vehicleType->id,
            'color' => 'maroon',
            'number' => '0123',
        ]);

        $stickerPath = $this->stickerGenerator->generateVehicleSticker(
            'maroon-0123',
            'Car',
            'XYZ-7890',
            'maroon',
            $vehicle->id
        );

        $fileName = basename($stickerPath);

        // Format: color_number_firstname_lastname.svg
        $this->assertMatchesRegularExpression('/maroon_0123_[a-z]+_[a-z]+\.svg/', $fileName);
        $this->assertStringContainsString('jane', $fileName);
        $this->assertStringContainsString('smith', $fileName);
    }

    /** @test */
    public function vehicle_model_attribute_returns_correct_sticker_color_for_students(): void
    {
        $college = College::factory()->create();
        $user = User::factory()->create([
            'user_type' => UserType::Student,
        ]);
        Student::factory()->create([
            'user_id' => $user->id,
            'college_id' => $college->id,
        ]);
        $vehicleType = VehicleType::factory()->create();

        $vehicle = Vehicle::factory()->create([
            'user_id' => $user->id,
            'type_id' => $vehicleType->id,
            'plate_no' => 'ABC-1235',
        ]);

        $this->assertEquals('yellow', $vehicle->sticker_color);
    }

    /** @test */
    public function vehicle_model_attribute_returns_maroon_for_security(): void
    {
        $user = User::factory()->create([
            'user_type' => UserType::Security,
        ]);
        Security::factory()->create(['user_id' => $user->id]);
        $vehicleType = VehicleType::factory()->create();

        $vehicle = Vehicle::factory()->create([
            'user_id' => $user->id,
            'type_id' => $vehicleType->id,
        ]);

        $this->assertEquals('maroon', $vehicle->sticker_color);
    }

    /** @test */
    public function vehicle_model_attribute_returns_white_for_stakeholder(): void
    {
        $stakeholderType = StakeholderType::factory()->create(['name' => 'Guardian']);
        $user = User::factory()->create([
            'user_type' => UserType::Stakeholder,
        ]);
        Stakeholder::factory()->create([
            'user_id' => $user->id,
            'type_id' => $stakeholderType->id,
        ]);
        $vehicleType = VehicleType::factory()->create();

        $vehicle = Vehicle::factory()->create([
            'user_id' => $user->id,
            'type_id' => $vehicleType->id,
        ]);

        $this->assertEquals('white', $vehicle->sticker_color);
    }
}
