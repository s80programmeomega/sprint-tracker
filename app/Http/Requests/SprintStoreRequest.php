<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * SprintStoreRequest — validates and authorizes POST /projects/{project}/sprints.
 */
class SprintStoreRequest extends FormRequest
{
    /**
     * Two conditions must both be true:
     * 1. User has the 'create-sprint' permission.
     * 2. User is a member of the parent project.
     *
     * The project comes from the nested route: /projects/{project}/sprints
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $this->user()->can('create-sprint')
            && $project->members()->where('user_id', $this->user()->id)->exists();
    }

    /**
     * 'after_or_equal:start_date' — cross-field rule: end_date must not be before start_date.
     * Laravel resolves 'start_date' from the current request automatically.
     */
    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'goal'       => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}
