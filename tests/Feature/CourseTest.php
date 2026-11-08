<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_can_list_courses(): void
    {
        Course::create(['name' => 'Ciência da Computação', 'description' => 'Graduação']);
        Course::create(['name' => 'Sistemas de Informação', 'description' => 'Bacharelado']);

        $response = $this->getJson('/courses');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'created_at', 'updated_at']
                ]
            ]);
    }

    public function test_can_filter_courses_by_name(): void
    {
        Course::create(['name' => 'Ciência da Computação', 'description' => 'Graduação']);
        Course::create(['name' => 'Engenharia Civil', 'description' => 'Graduação']);

        $response = $this->getJson('/courses?name=Computação');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Ciência da Computação');
    }

    public function test_can_create_course(): void
    {
        $payload = [
            'name' => 'Engenharia de Software',
            'description' => 'Curso superior focado em desenvolvimento'
        ];

        $response = $this->postJson('/courses', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Engenharia de Software')
            ->assertJsonPath('data.description', 'Curso superior focado em desenvolvimento');

        $this->assertDatabaseHas('courses', [
            'name' => 'Engenharia de Software'
        ]);
    }

    public function test_create_course_validation(): void
    {
        $response = $this->postJson('/courses', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_show_course(): void
    {
        $course = Course::create([
            'name' => 'Design Digital',
            'description' => 'Design e UI/UX'
        ]);

        $response = $this->getJson("/courses/{$course->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $course->id)
            ->assertJsonPath('data.name', 'Design Digital');
    }

    public function test_can_update_course(): void
    {
        $course = Course::create([
            'name' => 'Nome Antigo',
            'description' => 'Desc Antiga'
        ]);

        $response = $this->putJson("/courses/{$course->id}", [
            'name' => 'Nome Novo',
            'description' => 'Desc Nova'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Nome Novo')
            ->assertJsonPath('data.description', 'Desc Nova');

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Nome Novo'
        ]);
    }

    public function test_can_delete_course(): void
    {
        $course = Course::create([
            'name' => 'Curso Para Deletar',
            'description' => 'Em breve removido'
        ]);

        $response = $this->deleteJson("/courses/{$course->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('courses', [
            'id' => $course->id
        ]);
    }
}
