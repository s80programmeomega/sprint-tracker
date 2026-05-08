<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'       => fake()->sentence(4),
            'description' => fake()->paragraph(),
            // randomElement() picks from the enum's values() array — stays in sync automatically.
            'status'      => fake()->randomElement(\App\Enums\TaskStatus::values()),
            'priority'    => fake()->randomElement(\App\Enums\TaskPriority::values()),
            'due_date'    => fake()->optional()->dateTimeBetween('now', '+1 month')?->format('Y-m-d'),
            'sprint_id'   => \App\Models\Sprint::factory(),
            'assigned_to' => null,
        ];
    }
}
