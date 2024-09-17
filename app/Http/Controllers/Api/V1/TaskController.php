<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Task\TaskRequest;
use App\Http\Requests\Api\V1\Task\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{

    protected $taskService;

    /**
     * TaskController constructor.
     *
     * @param TaskService $taskService
     */
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of tasks for a given project.
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function index(Project $project): JsonResponse
    {
        return $this->taskService->getUserTasks($project);
    }

    /**
     * Filter tasks based on query parameters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function filterUserTasks(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $priority = $request->query('priority');
        $role = $request->query('role');

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $tasks = $this->taskService->getFilteredTasks($user, $status, $priority, $role);

        return response()->json(['tasks' => $tasks], 200);
    }

    /**
     * Display the specified task.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function show(Task $task): JsonResponse
    {
        return response()->json(['task' => $task], 200);
    }

    /**
     * Store a newly created task in storage.
     *
     * @param TaskRequest $request
     * @return JsonResponse
     */
    public function store(TaskRequest $request): JsonResponse
    {
        $response = $this->taskService->createTask($request->validated());

        if ($response->status() === 403) {
            return response()->json(['error' => 'You do not have permission to perform this action.'], 403);
        }

        return $response;
    }

    /**
     * Update the specified task in storage.
     *
     * @param UpdateTaskRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $response = $this->taskService->updateTask($task, $request->validated());

        $data = $response->getData(true);

        if (isset($data['error'])) {
            return response()->json(['error' => $data['error']], $response->getStatusCode());
        }

        return response()->json(['task' => $data['task']], $response->getStatusCode());
    }

    /**
     * Remove the specified task from storage.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function destroy(Task $task): JsonResponse
    {
        $response = $this->taskService->deleteTask($task);

        return $response;
    }

    /**
     * Restore the specified task from soft delete.
     *
     * @param $id
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        return $this->taskService->restoreTask($id);
    }

    /**
     * Display a listing of the deleted tasks.
     *
     * @return JsonResponse
     */
    public function showDeletedTasks(): JsonResponse
    {
        return $this->taskService->showDeletedTasks();
    }

    /**
     * Permanently delete the specified task.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function forceDelete(int $id): JsonResponse
    {
        $task = Task::withTrashed()->find($id);

        if (!$task) {
            return response()->json(['error' => 'Task not found.'], 404);
        }

        return $this->taskService->forceDelete($task);
    }
}
