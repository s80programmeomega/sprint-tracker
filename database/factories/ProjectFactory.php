<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => fake()->words(3, true),
            'description' => fake()->sentence(),
            'status'      => fake()->randomElement(['active', 'archived']),
            // owner_id must be provided when using the factory — no default here
            // because a project without an owner would violate the FK constraint.
            // Usage: Project::factory()->create(['owner_id' => $user->id])
            'owner_id'    => \App\Models\User::factory(),
        ];
    }
}
