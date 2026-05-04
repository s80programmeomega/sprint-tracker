<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * SprintResource — controls the JSON shape of a Sprint model.
 *
 * start_date and end_date are cast to Carbon dates in the model,
 * so they serialize as ISO-8601 date strings automatically.
 */
class SprintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'goal'       => $this->goal,
            'start_date' => $this->start_date,   // cast to date in Sprint model
            'end_date'   => $this->end_date,     // cast to date in Sprint model
            'status'     => $this->status,       // 'planned' | 'active' | 'completed'
            'project_id' => $this->project_id,
            'created_at' => $this->created_at,
        ];
    }
}
