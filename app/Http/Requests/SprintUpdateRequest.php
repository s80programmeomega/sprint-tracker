<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * SprintUpdateRequest — validates and authorizes PUT/PATCH /projects/{project}/sprints/{sprint}.
 */
class SprintUpdateRequest extends FormRequest
{
    /**
     * User must have 'edit-sprint' permission AND be a member of the parent project.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $this->user()->can('edit-sprint')
            && $project->members()->where('user_id', $this->user()->id)->exists();
    }

    /**
     * All fields use 'sometimes' — allows partial updates without sending every field.
     * 'in:planned,active,completed' — mirrors the status values defined in the sprints migration.
     */
    public function rules(): array
    {
        return [
            'name'       => ['sometimes', 'string', 'max:255'],
            'goal'       => ['nullable', 'string'],
            'start_date' => ['sometimes', 'date'],
            'end_date'   => ['sometimes', 'date', 'after_or_equal:start_date'],
            'status'     => ['sometimes', 'in:planned,active,completed'],
        ];
    }
}
