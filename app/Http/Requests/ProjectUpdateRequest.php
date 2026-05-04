<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProjectUpdateRequest — validates and authorizes PUT/PATCH /projects/{project}.
 */
class ProjectUpdateRequest extends FormRequest
{
    /**
     * Two conditions must both be true:
     * 1. User has the 'edit-project' Spatie permission.
     * 2. User is actually a member of this specific project (pivot table check).
     *
     * $this->route('project') resolves the Project model from the URL via route model binding.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $this->user()->can('edit-project')
            && $project->members()
                       ->where('user_id', $this->user()->id)
                       ->exists();
    }

    /**
     * Validation rules for updating a project.
     *
     * 'sometimes' — only validate this field if it is present in the request.
     *               This allows partial updates (PATCH behaviour): you can send
     *               only the fields you want to change.
     * 'in:...'    — value must be one of the listed options.
     */
    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['sometimes', 'in:active,archived'],
        ];
    }
}
