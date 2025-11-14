<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Models\College;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['user_type' => UserType::GlobalAdministrator]);
    }

    /** @test */
    public function admin_can_view_all_programs(): void
    {
        $college = College::factory()->create();
        Program::factory()->count(3)->create(['college_id' => $college->id]);

        $this->actingAs($this->admin);

        $response = $this->getJson('/api/programs');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'college_id', 'college'],
            ],
        ]);
    }

    /** @test */
    public function admin_can_create_program(): void
    {
        $college = College::factory()->create();

        $this->actingAs($this->admin);

        $response = $this->postJson('/api/programs', [
            'college_id' => $college->id,
            'name' => 'Bachelor of Science in Computer Science',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Program created successfully',
        ]);

        $this->assertDatabaseHas('programs', [
            'college_id' => $college->id,
            'name' => 'Bachelor of Science in Computer Science',
        ]);
    }

    /** @test */
    public function admin_can_update_program(): void
    {
        $college = College::factory()->create();
        $program = Program::factory()->create(['college_id' => $college->id]);

        $this->actingAs($this->admin);

        $response = $this->putJson("/api/programs/{$program->id}", [
            'college_id' => $college->id,
            'name' => 'Bachelor of Science in Information Technology',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Program updated successfully',
        ]);

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'name' => 'Bachelor of Science in Information Technology',
        ]);
    }

    /** @test */
    public function admin_can_delete_program_without_students(): void
    {
        $college = College::factory()->create();
        $program = Program::factory()->create(['college_id' => $college->id]);

        $this->actingAs($this->admin);

        $response = $this->deleteJson("/api/programs/{$program->id}");

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Program deleted successfully',
        ]);

        $this->assertDatabaseMissing('programs', ['id' => $program->id]);
    }

    /** @test */
    public function admin_cannot_delete_program_with_students(): void
    {
        $college = College::factory()->create();
        $program = Program::factory()->create(['college_id' => $college->id]);
        $student = Student::factory()->create(['program_id' => $program->id]);

        $this->actingAs($this->admin);

        $response = $this->deleteJson("/api/programs/{$program->id}");

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Cannot delete program with existing students',
        ]);

        $this->assertDatabaseHas('programs', ['id' => $program->id]);
    }

    /** @test */
    public function program_name_must_be_unique_per_college(): void
    {
        $college = College::factory()->create();
        Program::factory()->create([
            'college_id' => $college->id,
            'name' => 'Bachelor of Science in Engineering',
        ]);

        $this->actingAs($this->admin);

        $response = $this->postJson('/api/programs', [
            'college_id' => $college->id,
            'name' => 'Bachelor of Science in Engineering',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function admin_can_view_programs_by_college(): void
    {
        $college1 = College::factory()->create();
        $college2 = College::factory()->create();
        Program::factory()->count(2)->create(['college_id' => $college1->id]);
        Program::factory()->count(3)->create(['college_id' => $college2->id]);

        $this->actingAs($this->admin);

        $response = $this->getJson("/api/colleges/{$college1->id}/programs");

        $response->assertOk();
        $this->assertCount(2, $response->json());
    }
}
