<?php

namespace App\DTOs;

readonly class PortfolioExtractionResult
{
    /**
     * @param PortfolioOwnerData $owner
     * @param PortfolioWorkData[] $works
     * @param ClientData[] $clients
     */
    public function __construct(
        public PortfolioOwnerData $owner,
        public array $works,
        public array $clients
    ) {}

    public function toArray(): array
    {
        return [
            'owner' => $this->owner->toArray(),
            'works' => array_map(fn($work) => $work->toArray(), $this->works),
            'clients' => array_map(fn($client) => $client->toArray(), $this->clients),
        ];
    }
}
