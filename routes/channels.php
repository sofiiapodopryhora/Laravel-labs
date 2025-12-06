<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private channel for project updates
Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    // For testing: Allow all authenticated users
    return ['id' => $user->id, 'name' => $user->name];
    
    // Production version: Check if user has access to this project
    // return $user->projects()->where('projects.id', $projectId)->exists() ||
    //        $user->tasks()->whereHas('project', function ($query) use ($projectId) {
    //            $query->where('id', $projectId);
    //        })->exists();
});

// Public channel for project updates (for testing without authentication)
Broadcast::channel('project-public.{projectId}', function () {
    return true;
});
