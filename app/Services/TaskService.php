<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TaskService
{
    /**
     * Get tasks associated with the authenticated user's projects.
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function getUserTasks(Project $project): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $tasks = Task::whereHas('project.users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->get();

        return response()->json(['tasks' => $tasks]);
    }

    /**
     * Get tasks filtered by status, priority, and role.
     *
     * @param mixed $user
     * @param string|null $status
     * @param string|null $priority
     * @param string|null $role
     * @return Collection
     */
    public function getFilteredTasks(mixed $user, string $status = null, string $priority = null, string $role = null): Collection
    {
        return Task::FilterByStatusOrPriority($user, $status, $priority)->get();
    }

    /**
     * Create a new task with authorization checks.
     *
     * @param array $data
     * @return JsonResponse
     */
    public function createTask(array $data): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($user->role === 'admin') {
            $task = Task::create($data);
            return response()->json(['task' => $task], 201);
        }

        $project = Project::findOrFail($data['project_id']);
        $userRole = $this->getUserRoleInProject($project);

        if ($userRole !== 'manager') {
            return response()->json(['error' => 'Only managers can create a new task.'], 403);
        }

        $task = Task::create($data);
        return response()->json(['task' => $task], 201);
    }

    /**
     * Update an existing task with authorization checks.
     *
     * @param Task $task
     * @param array $data
     * @return JsonResponse
     */
    public function updateTask(Task $task, array $data): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $project = $task->project;

        if ($user->role === 'admin') {
            $task->update($data);
            $this->updateProjectLastActivity($project);
            return response()->json(['task' => $task], 200);
        }

        $this->authorizeAction($project, 'update');

        $userRole = $this->getUserRoleInProject($project);

        if ($userRole === 'manager') {
            $task->update($data);
            $this->updateProjectLastActivity($project);
            return response()->json(['task' => $task], 200);
        }

        if ($userRole === 'developer') {
            if (isset($data['status']) && count($data) === 1) {
                $task->update(['status' => $data['status']]);
                $this->updateProjectLastActivity($project);
                return response()->json(['task' => $task], 200);
            }

            return response()->json(['error' => 'Developers can only update the status of tasks.'], 403);
        }

        if ($userRole === 'tester') {
            if (isset($data['notes']) && count($data) === 1) {
                $task->update(['notes' => $data['notes']]);
                $this->updateProjectLastActivity($project);
                return response()->json(['task' => $task], 200);
            }

            return response()->json(['error' => 'Testers can only add notes to tasks.'], 403);
        }

        return response()->json(['error' => 'You do not have permission to perform this action.'], 403);
    }

    /**
     * Update the last activity timestamp for a project.
     *
     * @param Project $project
     * @return void
     */
    private function updateProjectLastActivity(Project $project): void
    {
        $userIds = $project->users->pluck('id');
        $project->users()->updateExistingPivot($userIds, [
            'last_activity' => now()
        ]);
    }

    /**
     * Get the authenticated user's role in the given project.
     *
     * @param Project $project
     * @return string|null
     */
    private function getUserRoleInProject(Project $project): ?string
    {
        $user = Auth::user();

        return $project->users()
            ->where('user_id', $user->id)
            ->pluck('project_user.role')
            ->first();
    }

    /**
     * Authorize an action based on the user's role in the project.
     *
     * @param Project $project
     * @param string $action
     * @return void
     */
    public function authorizeAction(Project $project, $action): void
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return;
        }

        $userRole = $this->getUserRoleInProject($project);

        if ($userRole === null) {
            abort(403, 'You do not have permission to perform this action.');
        }

        if ($action === 'create' && $userRole !== 'manager') {
            abort(403, 'You do not have permission to perform this action.');
        }

        if ($action === 'update' && !in_array($userRole, ['manager', 'developer', 'tester'])) {
            abort(403, 'You do not have permission to perform this action.');
        }

        if (in_array($action, ['delete', 'restore']) && $userRole !== 'manager') {
            abort(403, 'You do not have permission to perform this action.');
        }
    }

    /**
     * Delete a task with authorization checks.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function deleteTask(Task $task): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $project = $task->project;

        if (!$project) {
            return response()->json(['error' => 'Associated project not found.'], 404);
        }

        if ($user->role === 'admin' || $this->getUserRoleInProject($project) === 'manager') {
            $task->delete();
            return response()->json(['message' => 'Task deleted successfully.'], 200);
        }

        return response()->json(['error' => 'You do not have permission to delete this task.'], 403);
    }

    /**
     * Restore a deleted task with authorization checks.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restoreTask($id): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $task = Task::onlyTrashed()->find($id);

        if (!$task) {
            return response()->json(['error' => 'Task not found.'], 404);
        }

        if (!$task->project) {
            return response()->json(['error' => 'Associated project not found.'], 404);
        }

        if ($user->role === 'admin' || $this->getUserRoleInProject($task->project) === 'manager') {
            $task->restore();
            return response()->json(['message' => 'Task restored successfully.'], 200);
        }

        return response()->json(['error' => 'You do not have permission to restore this task.'], 403);
    }

    /**
     * Get all deleted tasks.
     *
     * @return JsonResponse
     */
    public function showDeletedTasks(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $deletedTasks = Task::onlyTrashed()->get();

        return response()->json(['deleted_tasks' => $deletedTasks], 200);
    }

    /**
     * Permanently delete a task with authorization checks.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function forceDelete(Task $task): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($user->role === 'admin') {
            $task->forceDelete();
            return response()->json(['message' => 'Task permanently deleted.'], 200);
        }

        return response()->json(['error' => 'You do not have permission to permanently delete this task.'], 403);
    }
}
