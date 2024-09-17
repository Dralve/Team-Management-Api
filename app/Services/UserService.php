<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Create a new user with hashed password.
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    /**
     * Update a user's profile or role based on authorization.
     *
     * @param User $user
     * @param array $data
     * @return array
     */
    public function updateUser(User $user, array $data): array
    {
        $authUser = Auth::user();

        if ($authUser->role === 'admin') {
            if (isset($data['role'])) {
                // Update the user role
                $user->role = $data['role'];
                $user->save();

                return [
                    'status' => 'success',
                    'message' => 'User role updated successfully',
                    'user' => $user,
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Admins cannot update user profile information',
            ];
        }

        if ($authUser->id === $user->id) {
            if (isset($data['role'])) {
                return [
                    'status' => 'error',
                    'message' => 'Unauthorized to update role',
                ];
            }

            $user->update($data);

            return [
                'status' => 'success',
                'message' => 'User profile updated successfully',
                'user' => $user,
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Unauthorized to update this user',
        ];
    }

    /**
     * Get all users.
     *
     * @return Collection
     */
    public function getAllUsers(): Collection
    {
        return User::all();
    }

    /**
     * Get a user by their ID, including trashed users.
     *
     * @param $id
     * @return Builder|array|Model
     */
    public function getUserById($id): Builder|array|Model
    {
        return User::withTrashed()->findOrFail($id);
    }

    /**
     * delete a user.
     *
     * @param User $user
     * @return array
     */
    public function deleteUser(User $user): array
    {
        $authUser = Auth::user();

        if ($authUser->role === 'admin') {
            $user->delete();

            return [
                'status' => 'success',
                'message' => 'User deleted successfully',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Unauthorized to delete this user',
        ];
    }

    /**
     * Restore a deleted user.
     *
     * @param $id
     * @return User|null
     */
    public function restoreUser($id): array
    {
        $authUser = Auth::user();

        if ($authUser->role === 'admin') {
            $user = User::withTrashed()->find($id);
            if ($user) {
                $user->restore();
                return [
                    'status' => 'success',
                    'message' => 'User restored successfully',
                    'user' => $user,
                ];
            }

            return [
                'status' => 'error',
                'message' => 'User not found',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Unauthorized to restore this user',
        ];
    }

    /**
     * Get all deleted users.
     *
     * @return Collection
     */
    public function getDeletedUsers(): Collection
    {
        $authUser = Auth::user();

        if ($authUser->role === 'admin') {
            return User::onlyTrashed()->get();
        }

        return collect();
    }


    /**
     * Permanently delete a user.
     *
     * @param User $user
     * @return void
     */
    public function forceDeleteUser(User $user): void
    {
        if ($user->trashed()) {
            $user->forceDelete();
        }
    }
}
