<?php

namespace App\DTOs;

readonly class ClientData
{
    public function __construct(
        public string $name,
        public string $feedback,
        public ?string $jobTitle,
        public ?string $introduction = null,
        public ?string $imageUrl = null
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'job_title' => $this->jobTitle,
            'introduction' => $this->introduction,
            'feedback' => $this->feedback,
            'image_url' => $this->imageUrl,
        ];
    }
}
