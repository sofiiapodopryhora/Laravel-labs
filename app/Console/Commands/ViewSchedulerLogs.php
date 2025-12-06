<?php

namespace App\Console\Commands;

use App\Models\SchedulerLog;
use Illuminate\Console\Command;

class ViewSchedulerLogs extends Command
{
    protected $signature = 'app:view-scheduler-logs {--limit=10}';
    protected $description = 'View recent scheduler execution logs';

    public function handle()
    {
        $limit = $this->option('limit');
        
        $logs = SchedulerLog::latest('started_at')
            ->limit($limit)
            ->get();

        if ($logs->isEmpty()) {
            $this->info('No scheduler logs found.');
            return;
        }

        $this->info("Recent {$logs->count()} scheduler logs:");
        $this->line('');

        $tableData = [];
        foreach ($logs as $log) {
            $tableData[] = [
                $log->id,
                $log->command,
                $log->status,
                substr($log->message, 0, 50) . (strlen($log->message) > 50 ? '...' : ''),
                $log->duration . 's',
                $log->started_at->format('Y-m-d H:i:s'),
            ];
        }

        $this->table(
            ['ID', 'Command', 'Status', 'Message', 'Duration', 'Started At'],
            $tableData
        );

        // Show status summary
        $statusCounts = $logs->groupBy('status')->map->count();
        $this->line('');
        $this->info('Status Summary:');
        foreach ($statusCounts as $status => $count) {
            $this->line("  • {$status}: {$count}");
        }
    }
}