<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Project\ProjectRequest;
use App\Http\Requests\Api\V1\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class ProjectController extends Controller
{
    protected $projectService;
    protected $taskService;

    /**
     * ProjectController constructor.
     *
     * @param ProjectService $projectService
     * @param TaskService $taskService
     */
    public function __construct(ProjectService $projectService, TaskService $taskService)
    {
        $this->projectService = $projectService;
        $this->taskService = $taskService;

        $this->middleware('auth:api');
        $this->middleware('role:admin')->only(['store', 'update', 'destroy', 'restore']);
    }

    /**
     * Display a listing of the projects.
     *
     * @return JsonResponse
     */

    public function index(): JsonResponse
    {
        $projects = $this->projectService->getAllProjects();
        return response()->json(['projects' => $projects]);
    }

    /**
     * Store a newly created project in storage.
     *
     * @param ProjectRequest $request
     * @return JsonResponse
     */
    public function store(ProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->createProject($request->validated());
        return response()->json(['message' => 'Project created successfully', 'project' => $project], 201);
    }

    /**
     * Display the specified project.
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function show(Project $project): JsonResponse
    {
        $project = $this->projectService->getProjectById($project);
        return response()->json($project);
    }

    /**
     * Get the latest task of the specified project.
     *
     * @param $projectId
     * @return JsonResponse
     */
    public function getLatestTask($projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $latestTask = $project->latestTask()->first(); // Call method and get the first result

        return response()->json(['latest_task' => $latestTask]);
    }

    /**
     * Get the oldest task of the specified project.
     *
     * @param $projectId
     * @return JsonResponse
     */
    public function getOldestTask($projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $oldestTask = $project->oldestTask()->first();

        return response()->json(['oldest_task' => $oldestTask]);
    }

    /**
     * Display the highest priority task for the specified project.
     *
     * @param Request $request
     * @param $projectId
     * @return JsonResponse
     */
    public function showHighestPriorityTask(Request $request, $projectId): JsonResponse
    {
        $condition = $request->query('title');

        $task = $this->projectService->getHighestPriorityTask($projectId, $condition);

        if ($task) {
            return response()->json($task);
        }

        return response()->json(['message' => 'No tasks found for this project.'], 404);
    }

    /**
     * Update the specified project in storage.
     *
     * @param UpdateProjectRequest $request
     * @param Project $project
     * @return JsonResponse
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $data = $request->validated();

        $updatedProject = $this->projectService->updateProject($project, $data);

        return response()->json($updatedProject, 200);
    }

    /**
     * Remove the specified project from storage.
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function destroy(Project $project): JsonResponse
    {
        $this->projectService->deleteProject($project);
        return response()->json(['message' => 'Project deleted successfully']);
    }

    /**
     * Restore the specified project from soft delete.
     *
     * @param $id
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        $project = $this->projectService->restoreProject($id);
        if ($project) {
            return response()->json(['message' => 'Project restored successfully', 'project' => $project]);
        }
        return response()->json(['message' => 'Project not found'], 404);
    }

    /**
     * Display a listing of the deleted projects.
     *
     * @return JsonResponse
     */
    public function showDeletedProjects(): JsonResponse
    {
        $deletedProjects = $this->projectService->getDeletedProjects();

        return response()->json([
            'status' => 'success',
            'deleted_projects' => $deletedProjects,
        ]);
    }

    /**
     * Permanently delete the specified project.
     *
     * @param $id
     * @return JsonResponse
     */
    public function forceDelete($id): JsonResponse
    {
        $this->projectService->forceDeleteProject($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Project permanently deleted successfully',
        ]);
    }

}
