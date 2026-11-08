<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private User $authUser;
    private Classroom $classroom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authUser = User::factory()->create();
        Sanctum::actingAs($this->authUser);

        $course = Course::create(['name' => 'Engenharia de Software']);
        $this->classroom = Classroom::create([
            'name' => 'Turma A',
            'vacancies' => 30,
            'course_id' => $course->id,
        ]);
    }

    public function test_can_enroll_user_in_classroom(): void
    {
        $student = User::factory()->create();

        $response = $this->postJson("/classrooms/{$this->classroom->id}/enroll", [
            'user_id' => $student->id,
            'action' => 'attach',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Usuário vinculado à turma com sucesso.')
            ->assertJsonPath('classroom.id', $this->classroom->id);

        $this->assertDatabaseHas('classroom_user', [
            'classroom_id' => $this->classroom->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_can_unenroll_user_from_classroom(): void
    {
        $student = User::factory()->create();
        // Matricula primeiro
        $this->classroom->users()->attach($student->id);

        $response = $this->postJson("/classrooms/{$this->classroom->id}/enroll", [
            'user_id' => $student->id,
            'action' => 'detach',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Usuário desvinculado da turma com sucesso.');

        $this->assertDatabaseMissing('classroom_user', [
            'classroom_id' => $this->classroom->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_enroll_is_idempotent(): void
    {
        $student = User::factory()->create();

        // Matricula duas vezes — não deve duplicar
        $this->postJson("/classrooms/{$this->classroom->id}/enroll", [
            'user_id' => $student->id,
            'action' => 'attach',
        ])->assertStatus(200);

        $this->postJson("/classrooms/{$this->classroom->id}/enroll", [
            'user_id' => $student->id,
            'action' => 'attach',
        ])->assertStatus(200);

        $this->assertCount(1, $this->classroom->fresh()->users);
    }

    public function test_enroll_validates_user_id(): void
    {
        $response = $this->postJson("/classrooms/{$this->classroom->id}/enroll", [
            'user_id' => 99999,
            'action' => 'attach',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    public function test_enroll_validates_action(): void
    {
        $student = User::factory()->create();

        $response = $this->postJson("/classrooms/{$this->classroom->id}/enroll", [
            'user_id' => $student->id,
            'action' => 'invalid_action',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['action']);
    }

    public function test_enroll_on_nonexistent_classroom_returns_404(): void
    {
        $student = User::factory()->create();

        $response = $this->postJson('/classrooms/99999/enroll', [
            'user_id' => $student->id,
            'action' => 'attach',
        ]);

        $response->assertStatus(404);
    }

    public function test_classroom_response_includes_enrolled_users(): void
    {
        $student = User::factory()->create();

        $response = $this->postJson("/classrooms/{$this->classroom->id}/enroll", [
            'user_id' => $student->id,
            'action' => 'attach',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'classroom' => [
                    'id', 'name', 'vacancies',
                    'users' => [['id', 'name', 'email']],
                ],
            ]);
    }
}
