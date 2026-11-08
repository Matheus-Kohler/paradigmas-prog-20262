<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClassroomCourseRelationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_can_create_classroom_with_course(): void
    {
        $course = Course::create([
            'name' => 'Ciência da Computação',
            'description' => 'Bacharelado'
        ]);

        $response = $this->postJson('/classrooms', [
            'name' => 'Turma CC 2026',
            'vacancies' => 40,
            'course_id' => $course->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Turma CC 2026')
            ->assertJsonPath('data.course_id', $course->id);

        $this->assertDatabaseHas('classrooms', [
            'name' => 'Turma CC 2026',
            'course_id' => $course->id,
        ]);
    }

    public function test_cannot_create_classroom_with_invalid_course(): void
    {
        $response = $this->postJson('/classrooms', [
            'name' => 'Turma Inválida',
            'vacancies' => 30,
            'course_id' => 99999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['course_id']);
    }

    public function test_list_classrooms_includes_associated_course(): void
    {
        $course = Course::create([
            'name' => 'Sistemas de Informação',
            'description' => 'Bacharelado'
        ]);

        Classroom::create([
            'name' => 'Turma SI 2026/1',
            'vacancies' => 35,
            'course_id' => $course->id,
        ]);

        $response = $this->getJson('/classrooms');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.course.id', $course->id)
            ->assertJsonPath('data.0.course.name', 'Sistemas de Informação');
    }

    public function test_show_classroom_includes_associated_course(): void
    {
        $course = Course::create([
            'name' => 'Engenharia de Software',
            'description' => 'Bacharelado'
        ]);

        $classroom = Classroom::create([
            'name' => 'Turma ES Noturno',
            'vacancies' => 50,
            'course_id' => $course->id,
        ]);

        $response = $this->getJson("/classrooms/{$classroom->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.course.id', $course->id)
            ->assertJsonPath('data.course.name', 'Engenharia de Software');
    }

    public function test_show_course_includes_associated_classrooms(): void
    {
        $course = Course::create([
            'name' => 'Análise e Desenvolvimento de Sistemas',
            'description' => 'Tecnólogo'
        ]);

        Classroom::create([
            'name' => 'Turma ADS Manhã',
            'vacancies' => 30,
            'course_id' => $course->id,
        ]);

        Classroom::create([
            'name' => 'Turma ADS Noite',
            'vacancies' => 30,
            'course_id' => $course->id,
        ]);

        $response = $this->getJson("/courses/{$course->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.classrooms')
            ->assertJsonPath('data.classrooms.0.name', 'Turma ADS Manhã');
    }

    public function test_can_filter_classrooms_by_course_id(): void
    {
        $course1 = Course::create(['name' => 'Curso 1']);
        $course2 = Course::create(['name' => 'Curso 2']);

        Classroom::create(['name' => 'Turma C1', 'vacancies' => 20, 'course_id' => $course1->id]);
        Classroom::create(['name' => 'Turma C2', 'vacancies' => 20, 'course_id' => $course2->id]);

        $response = $this->getJson("/classrooms?course_id={$course1->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Turma C1');
    }
}
