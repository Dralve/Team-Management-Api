<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\UpdateUserRequest;
use App\Http\Requests\Api\V1\User\UserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected $userService;

    /**
     * UserController constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;

        $this->middleware('auth:api');
    }

    /**
     * Display a listing of all users.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = $this->userService->getAllUsers();
        return response()->json(['users' => $users]);
    }

    /**
     * Store a newly created user in storage.
     *
     * Only available for admin users.
     *
     * @param UserRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(UserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Display the specified user.
     *
     * Admins can view any user, while users can view their own profile.
     *
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        return response()->json($user);
    }

    /**
     * Update the specified user.
     *
     * Users can update their own profiles, and admins can update user roles.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $result = $this->userService->updateUser($user, $request->validated());

        if ($result['status'] === 'success') {
            return response()->json([
                'message' => $result['message'],
                'user' => $result['user']
            ], 200);
        }

        return response()->json([
            'message' => $result['message']
        ], 403);
    }

    /**
     * Remove the specified user from storage.
     *
     * Only available for admins.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        $result = $this->userService->deleteUser($user);

        return response()->json($result, $result['status'] === 'success' ? 200 : 403);
    }

    /**
     * Restore a soft-deleted user.
     *
     * Only available for admins.
     *
     * @param $id
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        $result = $this->userService->restoreUser($id);

        return response()->json(
            $result,
            $result['status'] === 'success' ? 200 : 404
        );
    }

    /**
     * Permanently delete a user.
     *
     * Includes soft-deleted users in the query.
     *
     * @param $id
     * @return JsonResponse
     */
    public function forceDelete($id): JsonResponse
    {
        $user = User::withTrashed()->find($id);

        if ($user && $user->trashed()) {
            $this->userService->forceDeleteUser($user);

            return response()->json([
                'status' => 'success',
                'message' => 'User permanently deleted successfully',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'User not found or is not soft-deleted',
        ], 400);
    }

    /**
     * Display a listing of deleted users.
     *
     * Only available for admins.
     *
     * @return JsonResponse
     */
    public function showDeletedUsers(): JsonResponse
    {
        $deletedUsers = $this->userService->getDeletedUsers();

        return response()->json([
            'status' => 'success',
            'deleted_users' => $deletedUsers
        ], 200);
    }
}
