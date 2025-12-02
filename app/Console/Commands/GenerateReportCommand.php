<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Report;
use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateReportCommand extends Command
{
    protected $signature = 'app:generate-report
                            {--start= : Start date for report (Y-m-d format)}
                            {--end= : End date for report (Y-m-d format)}
                            {--file : Generate file report in storage}';

    protected $description = 'Generate project tasks statistics report';

    public function handle()
    {
        $this->info('Starting report generation...');

        $startDate = $this->option('start') ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $this->option('end') ?? now()->endOfMonth()->format('Y-m-d');

        $this->info("Report period: {$startDate} to {$endDate}");

        $reportData = $this->generateReportData($startDate, $endDate);

        $report = $this->saveReportToDatabase($startDate, $endDate, $reportData);


        if ($this->option('file')) {
            $filePath = $this->saveReportToFile($report, $reportData);
            $this->info("Report file saved to: {$filePath}");
        }

        $this->info("Report generated successfully with ID: {$report->id}");
        $this->table(
            ['Project', 'Todo', 'In Progress', 'Done', 'Expired', 'Total'],
            $this->formatReportForTable($reportData)
        );
    }

    private function generateReportData($startDate, $endDate)
    {
        $startDateTime = \Carbon\Carbon::parse($startDate)->startOfDay();
        $endDateTime = \Carbon\Carbon::parse($endDate)->endOfDay();

        $projects = Project::with(['tasks' => function ($query) use ($startDateTime, $endDateTime) {
            $query->whereBetween('created_at', [$startDateTime, $endDateTime]);
        }])->get();

        $reportData = [];
        $totalStats = ['todo' => 0, 'in_progress' => 0, 'done' => 0, 'expired' => 0];

        foreach ($projects as $project) {
            $stats = [
                'todo' => $project->tasks->where('status', 'todo')->count(),
                'in_progress' => $project->tasks->where('status', 'in_progress')->count(),
                'done' => $project->tasks->where('status', 'done')->count(),
                'expired' => $project->tasks->where('status', 'expired')->count(),
            ];

            $stats['total'] = array_sum($stats);

            $reportData[$project->id] = [
                'project_name' => $project->name,
                'project_id' => $project->id,
                'stats' => $stats
            ];

            foreach ($totalStats as $status => $count) {
                $totalStats[$status] += $stats[$status];
            }
        }

        $totalStats['total'] = array_sum($totalStats);
        $reportData['total'] = [
            'project_name' => 'TOTAL',
            'project_id' => null,
            'stats' => $totalStats
        ];

        return $reportData;
    }

    private function saveReportToDatabase($startDate, $endDate, $reportData)
    {
        return Report::create([
            'period_start' => $startDate,
            'period_end' => $endDate,
            'payload' => json_encode($reportData),
            'path' => '',
        ]);
    }

    private function saveReportToFile($report, $reportData)
    {
        $startDate = \Carbon\Carbon::parse($report->period_start)->format('Y-m-d');
        $endDate = \Carbon\Carbon::parse($report->period_end)->format('Y-m-d');
        $fileName = "report_{$report->id}_{$startDate}_to_{$endDate}.json";
        $relativePath = "reports/{$fileName}";
        $fullPath = storage_path("app/{$relativePath}");

        File::ensureDirectoryExists(dirname($fullPath));
        
        File::put($fullPath, json_encode($reportData, JSON_PRETTY_PRINT));


        $report->update(['path' => $relativePath]);

        return $relativePath;
    }

    private function formatReportForTable($reportData)
    {
        $tableData = [];

        foreach ($reportData as $data) {
            $stats = $data['stats'];
            $tableData[] = [
                $data['project_name'],
                $stats['todo'],
                $stats['in_progress'],
                $stats['done'],
                $stats['expired'],
                $stats['total']
            ];
        }

        return $tableData;
    }
}
