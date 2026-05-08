<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sprint>
 */
class SprintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+1 month');
        $end   = fake()->dateTimeBetween($start, '+2 months');

        return [
            'name'       => 'Sprint ' . fake()->numberBetween(1, 20),
            'goal'       => fake()->sentence(),
            'start_date' => $start->format('Y-m-d'),
            'end_date'   => $end->format('Y-m-d'),
            'status'     => fake()->randomElement(['planned', 'active', 'completed']),
            // project_id must be provided or will create a new project automatically.
            'project_id' => \App\Models\Project::factory(),
        ];
    }
}
