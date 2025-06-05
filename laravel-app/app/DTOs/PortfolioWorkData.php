<?php

namespace App\DTOs;

readonly class PortfolioWorkData
{
    public function __construct(
        public string $title,
        public string $url,
        public ?string $description = null
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'url' => $this->url,
            'description' => $this->description,
        ];
    }
}
