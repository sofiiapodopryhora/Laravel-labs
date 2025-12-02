<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;

class CheckTasks extends Command
{
    protected $signature = 'app:check-tasks';
    protected $description = 'Check latest tasks and their creation dates';

    public function handle()
    {
        $this->info('Latest 5 tasks:');

        $tasks = Task::with(['project', 'author'])
            ->latest()
            ->limit(5)
            ->get();

        if ($tasks->isEmpty()) {
            $this->warn('No tasks found.');
            return;
        }

        $tableData = [];
        foreach ($tasks as $task) {
            $tableData[] = [
                $task->id,
                $task->title,
                $task->project->name,
                $task->author->name,
                $task->status,
                $task->created_at->format('Y-m-d H:i:s'),
            ];
        }

        $this->table(
            ['ID', 'Title', 'Project', 'Author', 'Status', 'Created At'],
            $tableData
        );

        $today = now()->format('Y-m-d');
        $todayTasks = Task::whereDate('created_at', $today)->count();
        $this->info("Tasks created today ({$today}): {$todayTasks}");
    }
}
