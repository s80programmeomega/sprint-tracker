<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * TaskResource — controls the JSON shape of a Task model.
 *
 * status and priority are backed PHP 8.1 enums (TaskStatus, TaskPriority).
 * When serialized, they output their string value (e.g. 'todo', 'high').
 */
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status,       // TaskStatus enum → 'todo'|'in_progress'|'done'
            'priority'    => $this->priority,     // TaskPriority enum → 'low'|'medium'|'high'
            'due_date'    => $this->due_date,     // cast to date in Task model
            'sprint_id'   => $this->sprint_id,
            'assigned_to' => $this->assigned_to, // nullable user id
            'created_at'  => $this->created_at,
        ];
    }
}
