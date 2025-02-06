<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'area' => $this->area,
            'level' => $this->level,
            'grades' => $this->whenLoaded('grades', SchoolGradeResource::collection($this->grades)),
        ];
    }
}
