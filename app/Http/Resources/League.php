<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class League extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            'name' => $this->name,
            'images' => $this->images,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'money' => $this->money,
            'end_date_register' => $this->end_date_register,
            'format_of_league' => $this->format_of_league,
            'number_of_athletes' => $this->number_of_athletes,
            'start_time' => $this->start_time,
            'type_of_league' => $this->type_of_league,
            'location' => $this->location,
            'slug' => $this->slug,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
