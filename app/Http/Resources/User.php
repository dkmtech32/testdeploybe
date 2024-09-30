<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return 
        [
            "name"=> $this->name,
            "email"=> $this->email,
            "avatar"=> $this->profile_photo_path,
            "role"=> $this->role,
            "title"=> $this->title,
            "phone"=> $this->phone,
            "address"=> $this->address,
            "sex"=> $this->sex,
        ];
    }
}
