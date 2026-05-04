<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SprintStoreRequest;
use App\Http\Requests\SprintUpdateRequest;
use App\Http\Resources\SprintResource;
use App\Models\Project;
use App\Models\Sprint;

/**
 * SprintController — handles CRUD for sprints nested under a project.
 *
 * Route: /projects/{project}/sprints/{sprint}
 *
 * Both $project and $sprint are resolved via route model binding.
 * Note: Laravel does NOT automatically scope $sprint to $project —
 * in a real app you'd verify $sprint->project_id === $project->id.
 * That scoping is handled in phase 5 (Service layer).
 */
class SprintController extends Controller
{
    /**
     * GET /projects/{project}/sprints
     * Returns all sprints for the given project.
     * Uses the Project→sprints() hasMany relationship.
     */
    public function index(Project $project)
    {
        return SprintResource::collection($project->sprints);
    }

    /**
     * POST /projects/{project}/sprints
     * Creates a sprint scoped to the project using the relationship,
     * which automatically sets project_id on the new sprint.
     *
     * $request->validated() returns only the fields that passed rules() — safe to mass-assign.
     */
    public function store(SprintStoreRequest $request, Project $project)
    {
        $sprint = $project->sprints()->create($request->validated());

        return new SprintResource($sprint);
    }

    /**
     * GET /projects/{project}/sprints/{sprint}
     */
    public function show(Project $project, Sprint $sprint)
    {
        return new SprintResource($sprint);
    }

    /**
     * PUT/PATCH /projects/{project}/sprints/{sprint}
     */
    public function update(SprintUpdateRequest $request, Project $project, Sprint $sprint)
    {
        $sprint->update($request->validated());

        return new SprintResource($sprint);
    }

    /**
     * DELETE /projects/{project}/sprints/{sprint}
     * Requires 'edit-sprint' permission (no dedicated delete-sprint permission in the seeder).
     */
    public function destroy(Project $project, Sprint $sprint)
    {
        if (! auth()->user()->can('edit-sprint')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $sprint->delete();

        return response()->json(['message' => 'Sprint deleted']);
    }
}
