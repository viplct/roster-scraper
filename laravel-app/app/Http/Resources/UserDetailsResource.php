<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id' => $this->id,
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
                'job_title' => $this->job_title,
                'phone' => $this->phone,
                'verified_at' => $this->verified_at,
                'address' => $this->address,
                'social_urls' => $this->social_urls,
                'bio' => $this->bio,
                'expertise' => $this->expertise,
                'skills' => $this->skills,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'works' => WorkResource::collection($this->whenLoaded('works')),
            'clients' => ClientResource::collection($this->whenLoaded('clients')),
        ];
    }
} 