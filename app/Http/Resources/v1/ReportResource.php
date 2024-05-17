<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

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
            'Reporter_Name' => $this->reporter->username,
            'Reporter_Pic' => $this->reporter->profile_pic,
            'status' => $this->status,
            'category_type' => $this->typeOfCategory->type,
            'category_name' => $this->typeOfCategory->category->name,
            'title' => $this->reportDetail->title,
            'description' => $this->reportDetail->description,
            'image' => $this->reportDetail->image,
            'feedback' => $this->reportDetail->feedback,
            'building' => $this->location->building,
            'floor' => $this->location->floor,
            'room' => $this->location->room,
            'anonymous' => $this->reportDetail->anonymous,
            'timeline' => Carbon::parse($this->created_at)->format('d M Y'),
        ];
    }
}
