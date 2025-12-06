<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;

class CheckTestTasks extends Command
{
    protected $signature = 'app:check-test-tasks';
    protected $description = 'Check the test tasks we created';

    public function handle()
    {
        $testTasks = Task::where('title', 'like', 'Old Task%')
            ->orWhere('title', 'like', 'Recent Task%')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($testTasks->isEmpty()) {
            $this->warn('No test tasks found.');
            return;
        }

        $this->info('Test Tasks Status:');

        $tableData = [];
        foreach ($testTasks as $task) {
            $tableData[] = [
                $task->id,
                $task->title,
                $task->status,
                $task->created_at->format('Y-m-d H:i:s'),
                $task->created_at->diffInDays(now()) . ' days old'
            ];
        }

        $this->table(
            ['ID', 'Title', 'Status', 'Created At', 'Age'],
            $tableData
        );
    }
}