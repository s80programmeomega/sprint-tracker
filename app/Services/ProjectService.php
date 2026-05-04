<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProjectService
{
    public function createProject(array $data): Project
    {
        $project = Project::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'owner_id'    => Auth::id(),
        ]);

        $project->members()->attach(Auth::id(), ['role' => 'admin']);

        return $project;
    }

    public function updateProject(Project $project, array $data): Project
    {
        $project->update($data);
        return $project;
    }

    public function deleteProject(Project $project): void
    {
        $project->delete();
    }

    public function addMember(Project $project, User $user, string $role = 'member'): void
    {
        $project->members()->attach($user->id, ['role' => $role]);
    }

    public function removeMember(Project $project, User $user): void
    {
        $project->members()->detach($user->id);
    }
}
