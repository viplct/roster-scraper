<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class UserService
{
    public function __construct(
        private readonly WorkService $workService,
        private readonly ClientService $clientService
    ) {}

    /**
     * Get user with portfolio data by username
     *
     * @param string $username
     * @return User
     * @throws ModelNotFoundException
     */
    public function getUserDetails(string $username): User
    {
        return User::where('username', $username)
            ->with(['works', 'clients.media'])
            ->firstOrFail();
    }

    /**
     * Update user by username with provided data
     *
     * @param string $username
     * @param array $data
     * @return User
     * @throws ModelNotFoundException
     */
    public function updateUser(string $username, array $data): User
    {
        $user = User::where('username', $username)->firstOrFail();

        // Update basic user fields
        $userFields = Arr::only($data, $user->getFillable());
        if (!empty($userFields)) {
            $user->update($userFields);
        }

        // Handle works if provided
        if (isset($data['works'])) {
            $this->workService->syncUserWorks($user, $data['works']);
        }

        // Handle clients if provided
        if (isset($data['clients'])) {
            $this->clientService->syncUserClients($user, $data['clients']);
        }

        // Return updated user with relationships
        return $user->fresh(['works', 'clients.media']);
    }

    /**
     * Delete user by username
     *
     * @param string $username
     * @return bool
     * @throws ModelNotFoundException
     */
    public function deleteUser(string $username): bool
    {
        $user = User::where('username', $username)->firstOrFail();

        return $user->delete();
    }

    /**
     * Search users by query using semantic search
     *
     * @param string $query
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchUsers(string $query, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return User::search($query)->take($limit)->get();
    }
}
