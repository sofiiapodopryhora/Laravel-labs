<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Jobs\SendTaskCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTaskNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
    }

    public function handle(TaskCreated $event): void
    {
        SendTaskCreatedNotification::dispatch($event->task);
    }
}
