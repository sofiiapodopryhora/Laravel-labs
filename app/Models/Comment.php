<?php

namespace App\Models;

use App\Events\CommentCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'author_id',
        'body',
    ];

    protected $dispatchesEvents = [
        'created' => CommentCreated::class,
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
