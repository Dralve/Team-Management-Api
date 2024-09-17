<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    /**
     * Get all projects.
     *
     * @return Collection
     */
    public function getAllProjects(): Collection
    {
        return Project::all();
    }

    /**
     * Get a project by its model instance, including related users with pivot data.
     *
     * @param Project $project
     * @return Project
     */
    public function getProjectById(Project $project): Project
    {
        $project->load(['users' => function ($query) {
            $query->select('users.id', 'users.name')
                ->withPivot('role', 'contribution_hours', 'last_activity');
        }]);

        return $project;
    }

    /**
     * Get the highest priority task for a specific project.
     *
     * @param int $projectId
     * @param string|null $condition
     * @return mixed
     */
    public function getHighestPriorityTask(int $projectId, ?string $condition = null): mixed
    {
        $project = Project::find($projectId);

        if (!$project) {
            return null;
        }

        return $project->highestPriorityTask($condition);
    }

    /**
     * Create a new project with optional users.
     *
     * @param array $data
     * @return Project
     */
    public function createProject(array $data): Project
    {
        $project = Project::create($data);

        if (isset($data['users']) && is_array($data['users'])) {
            foreach ($data['users'] as $user) {
                if (isset($user['user_id'])) {
                    $project->users()->attach($user['user_id'], [
                        'role' => $user['role'],
                        'contribution_hours' => $user['contribution_hours'] ?? 0,
                        'last_activity' => $user['last_activity'] ?? null,
                    ]);
                }
            }
        }

        return $project;
    }

    /**
     * Update an existing project and synchronize users.
     *
     * @param Project $project
     * @param array $data
     * @return Project
     */
    public function updateProject(Project $project, array $data): Project
    {
        $project->update($data);

        if (isset($data['users']) && is_array($data['users'])) {
            $syncData = [];
            foreach ($data['users'] as $user) {
                if (isset($user['user_id'])) {
                    $ContributionHours = $project->users()
                        ->wherePivot('user_id', $user['user_id'])
                        ->pluck('project_user.contribution_hours')
                        ->first() ?? 0;

                    $syncData[$user['user_id']] = [
                        'contribution_hours' => $user['contribution_hours'] ?? $ContributionHours,
                        'role' => $user['role'] ?? $project->users()->where('users.id', $user['user_id'])->value('project_user.role'),
                    ];
                }
            }

            $project->users()->sync($syncData);
        }

        $project->load(['users' => function ($query) {
            $query->select('users.id', 'users.name', 'users.email');
        }]);

        return $project;
    }

    /**
     * delete a project and update associated users.
     *
     * @param Project $project
     * @return void
     */
    public function deleteProject(Project $project): void
    {
        $project->delete();

        $project->users()->updateExistingPivot(
            $project->users->pluck('id')->toArray(),
            ['deleted_at' => now()]
        );
    }

    /**
     * Restore a deleted project and its users.
     *
     * @param $id
     * @return Project|null
     */
    public function restoreProject($id): ?Project
    {
        $project = Project::withTrashed()->find($id);

        if ($project) {
            $project->restore();

            $project->users()->withTrashed()->updateExistingPivot(
                $project->users->pluck('id')->toArray(),
                ['deleted_at' => null]
            );

            return $project;
        }

        return null;
    }

    /**
     * Get all deleted projects.
     *
     * @return Collection
     */
    public function getDeletedProjects(): Collection
    {
        return Project::onlyTrashed()->get();
    }

    /**
     * Permanently delete a project and detach its users.
     *
     * @param $id
     * @return void
     */
    public function forceDeleteProject($id): void
    {
        $project = Project::withTrashed()->find($id);

        if ($project && $project->trashed()) {
            $project->forceDelete();
            $project->users()->detach();
        }
    }
}
