<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Maria Silva'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@email.com'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'classrooms' => ClassroomResource::collection($this->whenLoaded('classrooms')),
            'created_at' => $this->created_at
        ];
    }
}
