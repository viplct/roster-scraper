<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserDetailsResource;
use App\Services\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userPortfolioService
    ) {}

    /**
     * Get user portfolio data by username
     *
     * @param string $username
     * @return JsonResponse
     */
    public function show(string $username): JsonResponse
    {
        try {
            $user = $this->userPortfolioService->getUserDetails($username);

            return response()->json([
                'success' => true,
                'response' => new UserDetailsResource($user)
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'error' => "User with username '{$username}' does not exist"
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user portfolio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user data by username
     *
     * @param UpdateUserRequest $request
     * @param string $username
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, string $username): JsonResponse
    {
        try {
            $user = $this->userPortfolioService->updateUser($username, $request->validated());

            return response()->json([
                'success' => true,
                'response' => new UserDetailsResource($user)
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'error' => "User with username '{$username}' does not exist"
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user by username
     *
     * @param string $username
     * @return JsonResponse
     */
    public function destroy(string $username): JsonResponse
    {
        try {
            $this->userPortfolioService->deleteUser($username);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'error' => "User with username '{$username}' does not exist"
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
