<?php

namespace App\DTOs;

readonly class ClientData
{
    public function __construct(
        public string $name,
        public string $feedback,
        public ?string $jobTitle,
        public ?string $introduction = null,
        public ?string $photoUrl = null
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'job_title' => $this->jobTitle,
            'introduction' => $this->introduction,
            'feedback' => $this->feedback,
            'photo_url' => $this->photoUrl,
        ];
    }
}
