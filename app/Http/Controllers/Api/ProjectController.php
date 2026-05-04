<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Support\Facades\Auth;

/**
 * ProjectController — handles CRUD for projects.
 *
 * Phase 4 rule: controllers are thin. No business logic here.
 * Each method: receive request → act on model → return resource.
 *
 * Route model binding: Laravel automatically resolves {project} in the URL
 * to a Project model instance. If the project doesn't exist, it returns 404.
 */
class ProjectController extends Controller
{

    public function __construct(private ProjectService $projectService)
    {}


    /**
     * GET /projects
     * Returns only the projects the authenticated user is a member of.
     * Uses the User→projects() belongsToMany relationship.
     */
    public function index()
    {
        $projects = Auth::user()
            ->projects()
            ->get();

        return ProjectResource::collection($projects);
    }

    /**
     * POST /projects
     * Creates a new project and automatically adds the creator as an 'admin' member
     * in the project_user pivot table.
     *
     * ProjectStoreRequest handles authorization (create-project permission) and validation.
     */
    public function store(ProjectStoreRequest $request)
    {
        $project = $this->projectService->createProject($request->validated());

        // Attach the creator to the pivot table with the 'admin' role
        $project->members()->attach(Auth::id(), ['role' => 'admin']);

        return new ProjectResource($project);
    }

    /**
     * GET /projects/{project}
     * Returns a single project. Route model binding resolves the Project automatically.
     */
    public function show(Project $project)
    {
        return new ProjectResource($project);
    }

    /**
     * PUT/PATCH /projects/{project}
     * Updates a project with only the validated fields.
     * $request->validated() returns only the fields that passed the rules() — safe to mass-assign.
     *
     * ProjectUpdateRequest handles authorization (edit-project + membership) and validation.
     */
    public function update(ProjectUpdateRequest $request, Project $project)
    {
        $project = $this->projectService->updateProject($project, $request->validated());

        return new ProjectResource($project);
    }

    /**
     * DELETE /projects/{project}
     * Only the project owner with 'delete-project' permission can delete.
     * Authorization is done inline here (not in a FormRequest) because DELETE has no body to validate.
     */
    public function destroy(Project $project)
    {
        if (!Auth::user()->can('delete-project') || $project->owner_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $this->projectService->deleteProject($project);

        return response()->json(['message' => 'Project deleted']);
    }
}
