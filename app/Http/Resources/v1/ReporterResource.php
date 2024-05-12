<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReporterResource extends JsonResource
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
            'name' => $this->username,
            'role' => $this->role,
            'email' => $this->email,
            'profile_pic' => $this->profile_pic,
            'password' => $this->password,
            'reports' => ReportResource::collection($this->reports),
        ];
    }
}
