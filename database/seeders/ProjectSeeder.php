<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            ['name' => 'TaskFlow Core', 'owner_id' => 1],
            ['name' => 'Mobile App', 'owner_id' => 2],
            ['name' => 'Marketing Website', 'owner_id' => 3],
            ['name' => 'Internal Tools', 'owner_id' => 1],
        ];

        foreach ($projects as $project) {
            Project::create($project);
        }
    }
}
