<?php

namespace Database\Seeders;

use App\Models\Report;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        Report::create([
            'period_start' => now()->subWeek()->startOfWeek(),
            'period_end' => now()->subWeek()->endOfWeek(),
            'payload' => json_encode(['tasks_completed' => 5, 'new_tasks' => 10]),
            'path' => 'reports/weekly-1.json',
        ]);

        Report::create([
            'period_start' => now()->startOfWeek(),
            'period_end' => now()->endOfWeek(),
            'payload' => json_encode(['tasks_completed' => 2, 'new_tasks' => 7]),
            'path' => 'reports/weekly-2.json',
        ]);
    }
}
