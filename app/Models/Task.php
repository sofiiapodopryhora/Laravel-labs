<?php

namespace App\Models;

use App\Events\TaskUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'author_id',
        'assignee_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
    ];

    protected static function booted()
    {
        static::updated(function ($task) {
            // Dispatch TaskUpdated event when task is updated
            if ($task->wasChanged(['status', 'priority', 'assignee_id', 'title', 'description'])) {
                TaskUpdated::dispatch($task->load(['project', 'assignee']));
            }
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
