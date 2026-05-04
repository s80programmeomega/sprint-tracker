<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ProjectResource — controls the JSON shape of a Project model.
 *
 * Instead of returning the raw Eloquent model (which would expose every column),
 * we explicitly list the fields we want the API consumer to receive.
 *
 * Usage:
 *   Single:     return new ProjectResource($project);
 *   Collection: return ProjectResource::collection($projects);
 */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,       // 'active' | 'archived'
            'owner_id'    => $this->owner_id,
            'created_at'  => $this->created_at,
        ];
    }
}
