<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('role', 'contribution_hours', 'last_activity', 'deleted_at')
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function highestPriorityTask(?string $condition = null)
    {
        $query = Task::where('project_id', $this->id)
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')");

        if ($condition) {
            $query->where('title', 'LIKE', "%$condition%");
        }

        return $query->first();
    }
    public function latestTask(): HasOne
    {
        return $this->hasOne(Task::class)->latestOfMany('created_at');
    }

    public function oldestTask(): HasOne
    {
        return $this->hasOne(Task::class)->oldestOfMany('created_at');
    }


}
