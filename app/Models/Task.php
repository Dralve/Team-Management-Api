<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'project_id',
        'notes',
    ];

    Protected $casts = [
        'due_date',
        'deleted_at'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function users(): BelongsToMany
    {
        return $this->project->users();
    }

    public function scopeFilterByStatusOrPriority(Builder $query, $user, $status = null, $priority = null)
    {
        // Ensure tasks belong to projects where the user is associated
        return $query->whereRelation('project.users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($priority, function ($q) use ($priority) {
                $q->where('priority', $priority);
            });
    }


    public function getDueDateAttribute($value): string
    {
        return Carbon::parse($value)->format('Y-m-d H:i');
    }

    public function setDueDateAttribute($value): void
    {
        $this->attributes['due_date'] = Carbon::parse($value);
    }



}
