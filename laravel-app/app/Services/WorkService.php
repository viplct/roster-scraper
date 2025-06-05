<?php

namespace App\Services;

use App\Models\Work;
use App\Models\User;
use Illuminate\Support\Arr;

class WorkService
{
    /**
     * Sync works for a user (update/insert/delete based on _delete flag)
     *
     * @param User $user
     * @param array $worksData
     * @return void
     */
    public function syncUserWorks(User $user, array $worksData): void
    {
        $existingWorkIds = array_flip($user->works()->pluck('id')->toArray());

        foreach ($worksData as $workData) {
            if (isset($workData['id']) && isset($existingWorkIds[$workData['id']])) {
                if (isset($workData['_delete']) && $workData['_delete'] === true) {
                    // Delete existing work in single query
                    $user->works()->where('id', $workData['id'])->delete();
                } else {
                    // Update existing work in single query
                    $user->works()->where('id', $workData['id'])->update($this->prepareWorkData($workData));
                }
            } else {
                // Create new work (ignore _delete flag since no valid ID exists)
                $user->works()->create($this->prepareWorkData($workData));
            }
        }
    }

    /**
     * Prepare work data for mass assignment
     *
     * @param array $workData
     * @return array
     */
    private function prepareWorkData(array $workData): array
    {
        return Arr::only($workData, app(Work::class)->getFillable());
    }
}
