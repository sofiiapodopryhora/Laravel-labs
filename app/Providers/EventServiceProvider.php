<?php

namespace App\Providers;

use App\Events\TaskCreated;
use App\Events\CommentCreated;
use App\Listeners\SendTaskNotification;
use App\Listeners\SendTelegramTaskNotification;
use App\Listeners\SendTelegramCommentNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TaskCreated::class => [
            SendTaskNotification::class,
            SendTelegramTaskNotification::class,
        ],
        CommentCreated::class => [
            SendTelegramCommentNotification::class,
        ],
    ];

    public function register(): void
    {
        parent::register();
    }

    public function boot(): void
    {
        parent::boot();
    }
}
