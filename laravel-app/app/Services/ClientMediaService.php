<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientMedia;
use Illuminate\Support\Arr;

class ClientMediaService
{
    /**
     * Sync media for a client (update/insert/delete based on _delete flag)
     *
     * @param Client $client
     * @param array $mediaData
     * @return void
     */
    public function syncClientMedia(Client $client, array $mediaData): void
    {
        $existingMediaIds = array_flip($client->media()->pluck('id')->toArray());

        foreach ($mediaData as $media) {
            if (isset($media['id']) && isset($existingMediaIds[$media['id']])) {
                if (isset($media['_delete']) && $media['_delete'] === true) {
                    // Delete existing media
                    $client->media()->where('id', $media['id'])->delete();
                } else {
                    // Update existing media
                    $client->media()->where('id', $media['id'])->update($this->prepareMediaData($media));
                }
            } else {
                // Create new media (ignore _delete flag since no valid ID exists)
                $client->media()->create($this->prepareMediaData($media));
            }
        }
    }

    /**
     * Prepare media data for mass assignment
     *
     * @param array $mediaData
     * @return array
     */
    private function prepareMediaData(array $mediaData): array
    {
        return Arr::only($mediaData, app(ClientMedia::class)->getFillable());
    }
}
