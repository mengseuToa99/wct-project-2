<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
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
        'Reporter_Name' => $this->Reporter->username,
        'status' => $this->status,
        'category_name' => $this->category->name,
        'title' => $this->ReportDetail->title,
        'description' => $this->ReportDetail->description,
        'image' => $this->ReportDetail->image,
        'feedback' => $this->ReportDetail->feedback,
        'building' => $this->Location->building,
        'floor' => $this->Location->floor,
        'room' => $this->Location->room,
        'timeline' => $this-> created_at,
    ];
    }
}
