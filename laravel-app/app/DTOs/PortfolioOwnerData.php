<?php

namespace App\DTOs;

readonly class PortfolioOwnerData
{
    public function __construct(
        public ?string $name = null,
        public ?string $jobTitle = null,
        public ?string $introduction = null,
        public array $expertise = [],
        public array $skills = [],
        public array $socialUrls = []
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'job_title' => $this->jobTitle,
            'introduction' => $this->introduction,
            'expertise' => $this->expertise,
            'skills' => $this->skills,
            'social_urls' => $this->socialUrls,
        ];
    }
}
