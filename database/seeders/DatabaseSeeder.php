<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles and permissions must be seeded first — everything else depends on them.
        $this->call(RoleSeeder::class);

        // Create a predictable admin user for local development login.
        // Using firstOrCreate so re-seeding doesn't fail on unique email constraint.
        $admin = User::factory()->create([
            'name'  => 'admin',
            'email' => 'admin@email.com',
        ]);
        $admin->assignRole('admin');

        // Create a member user to test role-based access in the UI.
        $member = User::factory()->create([
            'name'  => 'member',
            'email' => 'member@email.com',
        ]);
        $member->assignRole('member');

        // Create 2 projects owned by the admin.
        // Each project gets 2 sprints, each sprint gets 5 tasks spread across statuses.
        Project::factory(2)->create(['owner_id' => $admin->id])->each(function (Project $project) use ($admin, $member) {

            // Add both users to the project pivot table.
            $project->members()->attach($admin->id,  ['role' => 'admin']);
            $project->members()->attach($member->id, ['role' => 'member']);

            Sprint::factory(2)->create(['project_id' => $project->id])->each(function (Sprint $sprint) use ($admin) {

                // Create tasks distributed across all three statuses.
                Task::factory(2)->create(['sprint_id' => $sprint->id, 'status' => 'todo']);
                Task::factory(2)->create(['sprint_id' => $sprint->id, 'status' => 'in_progress', 'assigned_to' => $admin->id]);
                Task::factory(1)->create(['sprint_id' => $sprint->id, 'status' => 'done']);
            });
        });
    }
}
