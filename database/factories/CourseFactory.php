<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'code' => strtoupper($this->faker->lexify('???')) . '-' . $this->faker->numberBetween(100, 999),
            'description' => $this->faker->paragraph(),
            'credits' => $this->faker->numberBetween(1, 4),
            'department' => $this->faker->randomElement(['Computer Science', 'Mathematics', 'Physics', 'Chemistry', 'Biology']),
            'semester_type' => $this->faker->randomElement(['Spring', 'Fall', 'Summer']),
            'year' => $this->faker->numberBetween(2024, 2025),
            'max_students' => $this->faker->numberBetween(20, 50),
            'prerequisites' => $this->faker->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
