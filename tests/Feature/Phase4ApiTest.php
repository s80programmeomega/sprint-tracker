<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 4 Feature Tests — covers all API endpoints for projects, sprints, and tasks.
 *
 * RefreshDatabase wraps each test in a transaction and rolls it back,
 * so tests are isolated and don't affect each other.
 */
class Phase4ApiTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ────────────────────────────────────────────────────────────────

    /** Create a user with the 'admin' role (has all permissions). */
    private function adminUser(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');
        return $user;
    }

    /** Create a user with the 'member' role (create-task, edit-task, assign-task). */
    private function memberUser(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('member');
        return $user;
    }

    /** Create a project owned by $user and attach them to the pivot as 'admin'. */
    private function projectFor(User $user): Project
    {
        $project = Project::create([
            'name'     => 'Test Project',
            'owner_id' => $user->id,
        ]);
        $project->members()->attach($user->id, ['role' => 'admin']);
        return $project;
    }

    /** Create a sprint for a project. */
    private function sprintFor(Project $project): Sprint
    {
        return $project->sprints()->create([
            'name'       => 'Sprint 1',
            'start_date' => '2026-05-01',
            'end_date'   => '2026-05-14',
        ]);
    }

    // ─── Auth guard ─────────────────────────────────────────────────────────────

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/projects')->assertUnauthorized();
    }

    // ─── Projects ───────────────────────────────────────────────────────────────

    public function test_admin_can_create_project(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->postJson('/api/projects', [
            'name'        => 'My Project',
            'description' => 'A test project',
        ]);

        $response->assertCreated()  // 201
                 ->assertJsonFragment(['name' => 'My Project']);

        $this->assertDatabaseHas('projects', ['name' => 'My Project', 'owner_id' => $user->id]);
        // Creator should be in the pivot table as admin
        $this->assertDatabaseHas('project_user', ['user_id' => $user->id, 'role' => 'admin']);
    }

    public function test_member_cannot_create_project(): void
    {
        $user = $this->memberUser();

        $this->actingAs($user)->postJson('/api/projects', ['name' => 'X'])
             ->assertForbidden();
    }

    public function test_project_store_validates_required_name(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)->postJson('/api/projects', [])
             ->assertUnprocessable()  // 422
             ->assertJsonValidationErrors(['name']);
    }

    public function test_user_can_list_own_projects(): void
    {
        $user = $this->adminUser();
        $project = $this->projectFor($user);

        $response = $this->actingAs($user)->getJson('/api/projects');

        $response->assertOk()
                 ->assertJsonFragment(['id' => $project->id]);
    }

    public function test_user_cannot_see_projects_they_are_not_member_of(): void
    {
        $owner = $this->adminUser();
        $other = User::factory()->create();
        $project = $this->projectFor($owner);

        $response = $this->actingAs($other)->getJson('/api/projects');

        $response->assertOk()
                 ->assertJsonMissing(['id' => $project->id]);
    }

    public function test_admin_can_update_own_project(): void
    {
        $user = $this->adminUser();
        $project = $this->projectFor($user);

        $this->actingAs($user)->putJson("/api/projects/{$project->id}", ['name' => 'Updated'])
             ->assertOk()
             ->assertJsonFragment(['name' => 'Updated']);
    }

    public function test_admin_can_delete_own_project(): void
    {
        $user = $this->adminUser();
        $project = $this->projectFor($user);

        $this->actingAs($user)->deleteJson("/api/projects/{$project->id}")
             ->assertOk()
             ->assertJsonFragment(['message' => 'Project deleted']);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    // ─── Sprints ────────────────────────────────────────────────────────────────

    public function test_admin_can_create_sprint(): void
    {
        $user = $this->adminUser();
        $project = $this->projectFor($user);

        $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/sprints", [
            'name'       => 'Sprint 1',
            'start_date' => '2026-05-01',
            'end_date'   => '2026-05-14',
        ]);

        $response->assertCreated()
                 ->assertJsonFragment(['name' => 'Sprint 1', 'project_id' => $project->id]);
    }

    public function test_sprint_end_date_must_be_after_start_date(): void
    {
        $user = $this->adminUser();
        $project = $this->projectFor($user);

        $this->actingAs($user)->postJson("/api/projects/{$project->id}/sprints", [
            'name'       => 'Bad Sprint',
            'start_date' => '2026-05-14',
            'end_date'   => '2026-05-01',  // before start_date
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['end_date']);
    }

    public function test_admin_can_list_sprints(): void
    {
        $user = $this->adminUser();
        $project = $this->projectFor($user);
        $sprint = $this->sprintFor($project);

        $this->actingAs($user)->getJson("/api/projects/{$project->id}/sprints")
             ->assertOk()
             ->assertJsonFragment(['id' => $sprint->id]);
    }

    public function test_admin_can_update_sprint(): void
    {
        $user = $this->adminUser();
        $project = $this->projectFor($user);
        $sprint = $this->sprintFor($project);

        $this->actingAs($user)->putJson("/api/projects/{$project->id}/sprints/{$sprint->id}", [
            'status' => 'active',
        ])->assertOk()
          ->assertJsonFragment(['status' => 'active']);
    }

    public function test_sprint_status_must_be_valid(): void
    {
        $user = $this->adminUser();
        $project = $this->projectFor($user);
        $sprint = $this->sprintFor($project);

        $this->actingAs($user)->putJson("/api/projects/{$project->id}/sprints/{$sprint->id}", [
            'status' => 'invalid-status',
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['status']);
    }

    public function test_admin_can_delete_sprint(): void
    {
        $user = $this->adminUser();
        $project = $this->projectFor($user);
        $sprint = $this->sprintFor($project);

        $this->actingAs($user)->deleteJson("/api/projects/{$project->id}/sprints/{$sprint->id}")
             ->assertOk()
             ->assertJsonFragment(['message' => 'Sprint deleted']);

        $this->assertDatabaseMissing('sprints', ['id' => $sprint->id]);
    }

    // ─── Tasks ──────────────────────────────────────────────────────────────────

    public function test_member_can_create_task(): void
    {
        $this->seed(RoleSeeder::class);

        $owner = User::factory()->create();
        $owner->assignRole('admin');

        $member = User::factory()->create();
        $member->assignRole('member');

        $project = $this->projectFor($owner);
        // Add member to the project pivot
        $project->members()->attach($member->id, ['role' => 'member']);
        $sprint = $this->sprintFor($project);

        $response = $this->actingAs($member)->postJson("/api/sprints/{$sprint->id}/tasks", [
            'title' => 'Fix bug #42',
        ]);

        $response->assertCreated()
                 ->assertJsonFragment(['title' => 'Fix bug #42', 'sprint_id' => $sprint->id]);
    }

    public function test_task_assigned_to_must_exist(): void
    {
        $user = $this->adminUser();
        $project = $this->projectFor($user);
        $sprint = $this->sprintFor($project);

        $this->actingAs($user)->postJson("/api/sprints/{$sprint->id}/tasks", [
            'title'       => 'Task',
            'assigned_to' => 99999,  // non-existent user
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['assigned_to']);
    }

    public function test_task_status_must_be_valid_enum_value(): void
    {
        $user = $this->adminUser();
        $project = $this->projectFor($user);
        $sprint = $this->sprintFor($project);
        $task = $sprint->tasks()->create(['title' => 'Task']);

        $this->actingAs($user)->putJson("/api/sprints/{$sprint->id}/tasks/{$task->id}", [
            'status' => 'not-a-real-status',
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['status']);
    }

    // ─── Auth ───────────────────────────────────────────────────────────────────

    public function test_me_returns_authenticated_user(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)->getJson('/api/me')
             ->assertOk()
             ->assertJsonFragment(['id' => $user->id]);
    }

    public function test_logout_deletes_token(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)->postJson('/api/logout')
             ->assertOk()
             ->assertJsonFragment(['message' => 'Logged out successfully']);
    }

    // ─── Projects (missing cases) ────────────────────────────────────────────────

    public function test_admin_can_show_single_project(): void
    {
        $user = $this->adminUser();
        $project = $this->projectFor($user);

        $this->actingAs($user)->getJson("/api/projects/{$project->id}")
             ->assertOk()
             ->assertJsonFragment(['id' => $project->id]);
    }

    public function test_non_member_cannot_update_project(): void
    {
        $owner = $this->adminUser();
        $other = User::factory()->create();
        $other->assignRole('admin');
        $project = $this->projectFor($owner);

        $this->actingAs($other)->putJson("/api/projects/{$project->id}", ['name' => 'Hacked'])
             ->assertForbidden();
    }

    public function test_non_owner_cannot_delete_project(): void
    {
        $owner = $this->adminUser();
        $other = User::factory()->create();
        $other->assignRole('admin');
        $project = $this->projectFor($owner);

        $this->actingAs($other)->deleteJson("/api/projects/{$project->id}")
             ->assertForbidden();
    }

    // ─── Sprints (missing cases) ─────────────────────────────────────────────────

    public function test_admin_can_show_single_sprint(): void
    {
        $user = $this->adminUser();
        $project = $this->projectFor($user);
        $sprint = $this->sprintFor($project);

        $this->actingAs($user)->getJson("/api/projects/{$project->id}/sprints/{$sprint->id}")
             ->assertOk()
             ->assertJsonFragment(['id' => $sprint->id]);
    }

    public function test_non_member_cannot_create_sprint(): void
    {
        $owner = $this->adminUser();
        $other = User::factory()->create();
        $other->assignRole('admin');
        $project = $this->projectFor($owner);

        $this->actingAs($other)->postJson("/api/projects/{$project->id}/sprints", [
            'name'       => 'Sprint X',
            'start_date' => '2026-05-01',
            'end_date'   => '2026-05-14',
        ])->assertForbidden();
    }

    // ─── Tasks (missing cases) ───────────────────────────────────────────────────

    public function test_member_can_list_tasks(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('member');
        $project = $this->projectFor($user);
        $sprint = $this->sprintFor($project);
        $task = $sprint->tasks()->create(['title' => 'Listed Task']);

        $this->actingAs($user)->getJson("/api/sprints/{$sprint->id}/tasks")
             ->assertOk()
             ->assertJsonFragment(['id' => $task->id]);
    }

    public function test_member_can_show_single_task(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('member');
        $project = $this->projectFor($user);
        $sprint = $this->sprintFor($project);
        $task = $sprint->tasks()->create(['title' => 'Single Task']);

        $this->actingAs($user)->getJson("/api/sprints/{$sprint->id}/tasks/{$task->id}")
             ->assertOk()
             ->assertJsonFragment(['id' => $task->id]);
    }

    public function test_member_can_update_task(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('member');
        $project = $this->projectFor($user);
        $sprint = $this->sprintFor($project);
        $task = $sprint->tasks()->create(['title' => 'Old Title']);

        $this->actingAs($user)->putJson("/api/sprints/{$sprint->id}/tasks/{$task->id}", [
            'title'  => 'New Title',
            'status' => 'in_progress',
        ])->assertOk()
          ->assertJsonFragment(['title' => 'New Title', 'status' => 'in_progress']);
    }

    public function test_task_priority_must_be_valid(): void
    {
        $user = $this->adminUser();
        $project = $this->projectFor($user);
        $sprint = $this->sprintFor($project);
        $task = $sprint->tasks()->create(['title' => 'Task']);

        $this->actingAs($user)->putJson("/api/sprints/{$sprint->id}/tasks/{$task->id}", [
            'priority' => 'urgent',  // not a valid value
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['priority']);
    }

    public function test_non_member_cannot_create_task(): void
    {
        $owner = $this->adminUser();
        $other = User::factory()->create();
        $other->assignRole('member');
        $project = $this->projectFor($owner);
        $sprint = $this->sprintFor($project);

        $this->actingAs($other)->postJson("/api/sprints/{$sprint->id}/tasks", [
            'title' => 'Sneaky Task',
        ])->assertForbidden();
    }

    public function test_admin_can_delete_task(): void
    {
        $user = $this->adminUser();
        $project = $this->projectFor($user);
        $sprint = $this->sprintFor($project);
        $task = $sprint->tasks()->create(['title' => 'Task to delete']);

        $this->actingAs($user)->deleteJson("/api/sprints/{$sprint->id}/tasks/{$task->id}")
             ->assertOk()
             ->assertJsonFragment(['message' => 'Task deleted']);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
