<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;

/**
 * TaskUpdateRequest — validates and authorizes PUT/PATCH /sprints/{sprint}/tasks/{task}.
 */
class TaskUpdateRequest extends FormRequest
{
    /**
     * User must have 'edit-task' permission AND be a member of the sprint's project.
     */
    public function authorize(): bool
    {
        $sprint = $this->route('sprint');

        return $this->user()->can('edit-task')
            && $sprint->project->members()->where('user_id', $this->user()->id)->exists();
    }

    /**
     * 'status' uses TaskStatus::values() — keeps validation in sync with the enum.
     * 'priority' uses TaskPriority::values() — same pattern.
     *
     * Both enums expose a static values() method that returns ['todo','in_progress','done']
     * and ['low','medium','high'] respectively.
     */
    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['sometimes', 'in:' . implode(',', TaskStatus::values())],
            'priority'    => ['sometimes', 'in:' . implode(',', TaskPriority::values())],
            'due_date'    => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ];
    }
}
