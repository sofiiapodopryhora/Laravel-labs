<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\TelegramController;
use App\Http\Middleware\CheckProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });

    Route::apiResource('projects', ProjectController::class);
    
    Route::get('/projects/{project}/tasks', [ProjectController::class, 'tasks']);
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
    
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::patch('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

    Route::get('/tasks/{task}/comments', [TaskController::class, 'comments']);
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store']);
    
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    
    Route::prefix('telegram')->group(function () {
        Route::get('/test', [TelegramController::class, 'testConnection']);
        Route::post('/send', [TelegramController::class, 'sendMessage']);
        Route::post('/send-queued', [TelegramController::class, 'sendMessageQueued']);
    });
});

// Testing routes for real-time functionality (without authentication)
Route::post('/test-task-update', function () {
    $task = \App\Models\Task::where('status', '!=', 'done')->first();
    if ($task) {
        $task->status = 'done';
        $task->save();
        return response()->json(['message' => 'Task updated successfully', 'task' => $task]);
    }
    return response()->json(['message' => 'No available task to update'], 404);
});

Route::post('/test-comment-create', function () {
    $task = \App\Models\Task::first();
    $user = \App\Models\User::first();
    
    if ($task && $user) {
        $comment = \App\Models\Comment::create([
            'task_id' => $task->id,
            'author_id' => $user->id,
            'body' => 'Test comment created at ' . now()->toTimeString()
        ]);
        return response()->json(['message' => 'Comment created successfully', 'comment' => $comment]);
    }
    return response()->json(['message' => 'Unable to create comment'], 404);
});