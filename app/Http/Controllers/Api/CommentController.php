<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function store(Request $request, $taskId)
    {
        $user = $request->user();
        
        // Перевіряємо доступ до задачі
        $task = Task::whereHas('project', function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('users', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->find($taskId);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found or access denied'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $comment = Comment::create([
            'body' => $request->content,
            'task_id' => $task->id,
            'author_id' => $user->id,
        ]);

        return response()->json($comment->load('author'), 201);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        $comment = Comment::where('author_id', $user->id)->find($id);

        if (!$comment) {
            return response()->json([
                'message' => 'Comment not found or access denied'
            ], 404);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully'
        ]);
    }
}