<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\ActivityLog;
use App\Models\Sprint;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskService
{
    public function createTask(Sprint $sprint, array $data): Task
    {
        $task = $sprint->tasks()->create($data);

        $this->logActivity($task, 'created', [
            'title' => $task->title,
        ]);

        return $task;
    }

    public function updateTask(Task $task, array $data): Task
    {
        $old = [
            'status'      => $task->status?->value,
            'assigned_to' => $task->assigned_to,
        ];

        $task->update($data);

        $new = [
            'status'      => $task->fresh()->status?->value,
            'assigned_to' => $task->fresh()->assigned_to,
        ];

        $this->logActivity($task, 'updated', [
            'old' => $old,
            'new' => $new,
        ]);

        return $task;
    }

    public function deleteTask(Task $task): void
    {
        $this->logActivity($task, 'deleted', [
            'title' => $task->title,
        ]);

        $task->delete();
    }

    private function logActivity(Task $task, string $action, array $metadata = []): void
    {
        ActivityLog::create([
            'task_id'  => $task->id,
            'user_id'  => Auth::id(),
            'action'   => $action,
            'metadata' => $metadata,
        ]);
    }
}
