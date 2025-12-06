<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchedulerLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'command',
        'status',
        'message',
        'started_at',
        'finished_at',
        'duration'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration' => 'integer'
    ];

    /**
     * Scope for successful executions
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed executions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'error');
    }

    /**
     * Scope for specific command
     */
    public function scopeForCommand($query, string $command)
    {
        return $query->where('command', $command);
    }
}
