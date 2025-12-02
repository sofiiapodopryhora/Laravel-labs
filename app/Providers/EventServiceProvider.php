<?php

namespace App\Providers;

use App\Events\TaskCreated;
use App\Listeners\SendTaskNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TaskCreated::class => [
            SendTaskNotification::class,
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
