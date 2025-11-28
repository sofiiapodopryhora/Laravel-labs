<?php

namespace Database\Seeders;

use App\Models\Comment;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $comments = [
            ['task_id' => 1, 'author_id' => 2, 'body' => 'I will finish DB schema today.'],
            ['task_id' => 1, 'author_id' => 1, 'body' => 'Great, don’t forget relations.'],
            ['task_id' => 2, 'author_id' => 3, 'body' => 'Auth should support Sanctum tokens.'],
            ['task_id' => 3, 'author_id' => 4, 'body' => 'Mobile UI draft is ready.'],
            ['task_id' => 4, 'author_id' => 5, 'body' => 'Added some ideas for content.'],
            ['task_id' => 5, 'author_id' => 1, 'body' => 'We can start with simple pipeline.'],
        ];

        foreach ($comments as $comment) {
            Comment::create($comment);
        }
    }
}
