<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Arr;

class ClientService
{
    public function __construct(
        private readonly ClientMediaService $clientMediaService
    ) {}

    /**
     * Sync clients for a user (update/insert/delete based on _delete flag)
     *
     * @param User $user
     * @param array $clientsData
     * @return void
     */
    public function syncUserClients(User $user, array $clientsData): void
    {
        $existingClientIds = array_flip($user->clients()->pluck('id')->toArray());

        foreach ($clientsData as $clientData) {
            if (isset($clientData['id']) && isset($existingClientIds[$clientData['id']])) {
                if (isset($clientData['_delete']) && $clientData['_delete'] === true) {
                    // Delete existing client (this will cascade delete media if configured)
                    $user->clients()->where('id', $clientData['id'])->delete();
                } else {
                    // Update existing client
                    $user->clients()->where('id', $clientData['id'])->update($this->prepareClientData($clientData));

                    // Handle client media if provided (need to get client instance for media operations)
                    if (isset($clientData['media'])) {
                        /** @var Client $client */
                        $client = $user->clients()->find($clientData['id']);
                        $this->clientMediaService->syncClientMedia($client, $clientData['media']);
                    }
                }
            } else {
                // Create new client (ignore _delete flag since no valid ID exists)
                $newClient = $user->clients()->create($this->prepareClientData($clientData));

                // Handle client media if provided
                if (isset($clientData['media'])) {
                    $this->clientMediaService->syncClientMedia($newClient, $clientData['media']);
                }
            }
        }
    }

    /**
     * Prepare client data for mass assignment
     *
     * @param array $clientData
     * @return array
     */
    private function prepareClientData(array $clientData): array
    {
        return Arr::only($clientData, app(Client::class)->getFillable());
    }
}
