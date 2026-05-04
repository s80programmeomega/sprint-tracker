<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;

/**
 * TaskStoreRequest — validates and authorizes POST /sprints/{sprint}/tasks.
 */
class TaskStoreRequest extends FormRequest
{
    /**
     * User must have 'create-task' permission AND be a member of the sprint's project.
     *
     * Tasks are nested under sprints, but membership is on the project level.
     * We traverse: sprint → project → members pivot.
     */
    public function authorize(): bool
    {
        $sprint = $this->route('sprint');

        return $this->user()->can('create-task')
            && $sprint->project->members()->where('user_id', $this->user()->id)->exists();
    }

    /**
     * 'in:...' built from TaskPriority::values() — uses the enum's own values() helper
     * so the validation rule stays in sync with the enum automatically.
     *
     * 'exists:users,id' — database-level check: the assigned_to user must exist in the users table.
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority'    => ['sometimes', 'in:' . implode(',', TaskPriority::values())],
            'due_date'    => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ];
    }
}
