<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_can_be_created_listed_shown_updated_and_deleted(): void
    {
        $studentData = [
            'student_number' => '2026-0001',
            'name' => 'Juan Dela Cruz',
            'course' => 'BS Information Technology',
            'age' => 20,
            'email' => 'juan@example.com',
        ];

        $this->postJson('/api/students', $studentData)
            ->assertCreated()
            ->assertJsonPath('message', 'Student created successfully.')
            ->assertJsonPath('data.student_number', $studentData['student_number']);

        $student = Student::firstOrFail();

        $this->getJson('/api/students')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $student->id);

        $this->getJson("/api/students/{$student->id}")
            ->assertOk()
            ->assertJsonPath('email', $studentData['email']);

        $updatedData = [
            ...$studentData,
            'name' => 'Juan Miguel Dela Cruz',
            'course' => 'BS Computer Science',
            'age' => 21,
            'email' => 'juanmiguel@example.com',
        ];

        $this->putJson("/api/students/{$student->id}", $updatedData)
            ->assertOk()
            ->assertJsonPath('message', 'Student updated successfully.')
            ->assertJsonPath('data.name', $updatedData['name']);

        $this->deleteJson("/api/students/{$student->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Student deleted successfully.');

        $this->getJson("/api/students/{$student->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Student not found.');
    }

    public function test_student_creation_validates_required_and_unique_fields(): void
    {
        $this->postJson('/api/students', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'student_number',
                'name',
                'course',
                'age',
                'email',
            ]);

        $studentData = [
            'student_number' => '2026-0001',
            'name' => 'Juan Dela Cruz',
            'course' => 'BS Information Technology',
            'age' => 20,
            'email' => 'juan@example.com',
        ];
        Student::create($studentData);

        $this->postJson('/api/students', $studentData)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['student_number', 'email']);
    }
}
