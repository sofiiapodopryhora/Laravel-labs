<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\Project;
use App\Models\Comment;
use App\Models\Report;
use App\Models\SchedulerLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class GenerateWeeklyReport extends Command
{
    protected $signature = 'app:generate-report';
    protected $description = 'Generate weekly report with statistics';

    public function handle()
    {
        $startTime = now();
        $this->info('Starting weekly report generation...');

        try {
            $weekStart = Carbon::now()->startOfWeek();
            $weekEnd = Carbon::now()->endOfWeek();

            // Collect statistics
            $stats = $this->collectWeeklyStatistics($weekStart, $weekEnd);
            $stats['period_start'] = $weekStart->format('Y-m-d');
            $stats['period_end'] = $weekEnd->format('Y-m-d');
            
            // Create report record
            $report = $this->createReportRecord($stats, $weekStart, $weekEnd);
            
            $this->displayReport($stats, $weekStart, $weekEnd);
            
            $message = "Weekly report generated successfully with ID: {$report->id}";
            $this->info($message);
            
            $this->logSchedulerExecution('generate-weekly-report', 'success', $message, $startTime);

        } catch (\Exception $e) {
            $errorMessage = "Error generating weekly report: " . $e->getMessage();
            $this->error($errorMessage);
            $this->logSchedulerExecution('generate-weekly-report', 'error', $errorMessage, $startTime);
            
            return 1;
        }

        return 0;
    }

    private function collectWeeklyStatistics(Carbon $weekStart, Carbon $weekEnd): array
    {
        return [
            'tasks_created' => Task::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
            'tasks_completed' => Task::where('status', 'completed')
                ->whereBetween('updated_at', [$weekStart, $weekEnd])->count(),
            'tasks_in_progress' => Task::where('status', 'in_progress')->count(),
            'tasks_expired' => Task::where('status', 'expired')
                ->whereBetween('updated_at', [$weekStart, $weekEnd])->count(),
            'comments_created' => Comment::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
            'active_projects' => Project::whereHas('tasks', function ($query) use ($weekStart, $weekEnd) {
                $query->whereBetween('created_at', [$weekStart, $weekEnd]);
            })->count(),
            'total_projects' => Project::count(),
            'tasks_by_priority' => [
                'high' => Task::where('priority', 'high')
                    ->whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                'medium' => Task::where('priority', 'medium')
                    ->whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                'low' => Task::where('priority', 'low')
                    ->whereBetween('created_at', [$weekStart, $weekEnd])->count(),
            ],
            'tasks_by_status' => [
                'pending' => Task::where('status', 'pending')->count(),
                'in_progress' => Task::where('status', 'in_progress')->count(),
                'completed' => Task::where('status', 'completed')->count(),
                'expired' => Task::where('status', 'expired')->count(),
            ]
        ];
    }

    private function createReportRecord(array $stats, Carbon $weekStart, Carbon $weekEnd): Report
    {
        $fileName = 'weekly-report-' . $weekStart->format('Y-m-d') . '-to-' . $weekEnd->format('Y-m-d') . '.json';
        $path = 'reports/' . $fileName;
        
        // Save the report to storage
        $this->saveReportToStorage($stats, $path);
        
        $reportData = [
            'period_start' => $weekStart->format('Y-m-d'),
            'period_end' => $weekEnd->format('Y-m-d'),
            'payload' => $stats,
            'path' => $path
        ];

        return Report::create($reportData);
    }
    
    private function saveReportToStorage(array $stats, string $path): void
    {
        $reportContent = [
            'generated_at' => now()->toISOString(),
            'period_start' => $stats['period_start'] ?? null,
            'period_end' => $stats['period_end'] ?? null,
            'statistics' => $stats
        ];
        
        Storage::disk('local')->put($path, json_encode($reportContent, JSON_PRETTY_PRINT));
    }

    private function displayReport(array $stats, Carbon $weekStart, Carbon $weekEnd)
    {
        $this->line('');
        $this->info('=== WEEKLY REPORT ===');
        $this->info("Period: {$weekStart->format('Y-m-d')} to {$weekEnd->format('Y-m-d')}");
        $this->line('');
        
        $this->info('📊 Task Statistics:');
        $this->line("  • Tasks created this week: {$stats['tasks_created']}");
        $this->line("  • Tasks completed this week: {$stats['tasks_completed']}");
        $this->line("  • Tasks expired this week: {$stats['tasks_expired']}");
        $this->line("  • Tasks currently in progress: {$stats['tasks_in_progress']}");
        $this->line('');
        
        $this->info('📈 Priority Breakdown (new tasks):');
        $this->line("  • High priority: {$stats['tasks_by_priority']['high']}");
        $this->line("  • Medium priority: {$stats['tasks_by_priority']['medium']}");
        $this->line("  • Low priority: {$stats['tasks_by_priority']['low']}");
        $this->line('');
        
        $this->info('🗂️ Current Status Overview:');
        $this->line("  • Pending: {$stats['tasks_by_status']['pending']}");
        $this->line("  • In Progress: {$stats['tasks_by_status']['in_progress']}");
        $this->line("  • Completed: {$stats['tasks_by_status']['completed']}");
        $this->line("  • Expired: {$stats['tasks_by_status']['expired']}");
        $this->line('');
        
        $this->info('💬 Activity:');
        $this->line("  • Comments created: {$stats['comments_created']}");
        $this->line("  • Active projects: {$stats['active_projects']}");
        $this->line("  • Total projects: {$stats['total_projects']}");
        $this->line('');
    }

    private function logSchedulerExecution(string $command, string $status, string $message, Carbon $startTime)
    {
        try {
            $duration = max(0, now()->diffInSeconds($startTime));
            
            SchedulerLog::create([
                'command' => $command,
                'status' => $status,
                'message' => $message,
                'started_at' => $startTime,
                'finished_at' => now(),
                'duration' => $duration
            ]);
        } catch (\Exception $e) {
            $this->error("Failed to log scheduler execution: " . $e->getMessage());
        }
    }
}