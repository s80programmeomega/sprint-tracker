<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProjectStoreRequest — validates and authorizes POST /projects.
 *
 * FormRequest combines authorization + validation in one class,
 * keeping the controller free of both concerns.
 */
class ProjectStoreRequest extends FormRequest
{
    /**
     * Only users with the 'create-project' Spatie permission can create projects.
     * Returning false automatically sends a 403 response — no need to handle it in the controller.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-project');
    }

    /**
     * Validation rules for creating a project.
     *
     * 'required' — field must be present and non-empty.
     * 'nullable' — field is optional; if absent or null, it passes.
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
