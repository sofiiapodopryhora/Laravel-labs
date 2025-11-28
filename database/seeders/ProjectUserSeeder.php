<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('project_user')->insert([
            ['project_id' => 1, 'user_id' => 1, 'role' => 'owner'],
            ['project_id' => 1, 'user_id' => 2, 'role' => 'member'],
            ['project_id' => 1, 'user_id' => 3, 'role' => 'member'],

            ['project_id' => 2, 'user_id' => 2, 'role' => 'owner'],
            ['project_id' => 2, 'user_id' => 4, 'role' => 'member'],

            ['project_id' => 3, 'user_id' => 3, 'role' => 'owner'],
            ['project_id' => 3, 'user_id' => 5, 'role' => 'member'],

            ['project_id' => 4, 'user_id' => 1, 'role' => 'owner'],
            ['project_id' => 4, 'user_id' => 5, 'role' => 'member'],
        ]);
    }
}
