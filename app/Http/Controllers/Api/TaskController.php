<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Http\Resources\TaskResource;
use App\Models\Sprint;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Support\Facades\Auth;

/**
 * TaskController — handles CRUD for tasks nested under a sprint.
 *
 * Route: /sprints/{sprint}/tasks/{task}
 *
 * Tasks are nested under sprints (not under projects directly),
 * because a task always belongs to a specific sprint.
 */
class TaskController extends Controller
{

    public function __construct(private TaskService $taskService)
    {}

    /**
     * GET /sprints/{sprint}/tasks
     * Returns all tasks for the given sprint.
     * Uses the Sprint→tasks() hasMany relationship.
     */
    public function index(Sprint $sprint)
    {
        return TaskResource::collection($sprint->tasks);
    }

    /**
     * POST /sprints/{sprint}/tasks
     * Creates a task scoped to the sprint — sprint_id is set automatically via the relationship.
     *
     * TaskStoreRequest checks: create-task permission + project membership (via sprint→project).
     */
    public function store(TaskStoreRequest $request, Sprint $sprint)
    {
        $task = $this->taskService->createTask($sprint, $request->validated());

        return new TaskResource($task);
    }

    /**
     * GET /sprints/{sprint}/tasks/{task}
     */
    public function show(Sprint $sprint, Task $task)
    {
        return new TaskResource($task);
    }

    /**
     * PUT/PATCH /sprints/{sprint}/tasks/{task}
     * TaskUpdateRequest checks: edit-task permission + project membership.
     * Only the fields present in the request are updated ('sometimes' rules).
     */
    public function update(TaskUpdateRequest $request, Sprint $sprint, Task $task)
    {
       $task = $this->taskService->updateTask($task, $request->validated());

        return new TaskResource($task);
    }

    /**
     * DELETE /sprints/{sprint}/tasks/{task}
     * Requires 'delete-task' permission (defined in RoleSeeder, assigned to admin role).
     */
    public function destroy(Sprint $sprint, Task $task)
    {
        if (! Auth::user()->can('delete-task')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $this->taskService->deleteTask($task);

        return response()->json(['message' => 'Task deleted']);
    }
}
