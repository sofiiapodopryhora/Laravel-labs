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