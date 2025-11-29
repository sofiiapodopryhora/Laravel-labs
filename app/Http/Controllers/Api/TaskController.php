<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function store(Request $request, $projectId)
    {
        $user = $request->user();
        
        // Перевіряємо доступ до проєкту
        $project = Project::where('id', $projectId)
            ->where(function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('users', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->first();

        if (!$project) {
            return response()->json([
                'message' => 'Project not found or access denied'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,done',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'project_id' => $project->id,
            'author_id' => $user->id,
            'assignee_id' => $request->assignee_id,
        ]);

        return response()->json($task->load(['author', 'assignee', 'project']), 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $task = Task::with(['author', 'assignee', 'project', 'comments'])
            ->whereHas('project', function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('users', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found or access denied'
            ], 404);
        }

        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        
        $task = Task::whereHas('project', function ($query) use ($user) {
                $query->where('owner_id', $user->id);
            })
            ->orWhere('author_id', $user->id)
            ->find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found or access denied'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:todo,in_progress,done',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $task->update($request->only(['title', 'description', 'status', 'assignee_id']));

        return response()->json($task->load(['author', 'assignee', 'project']));
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        $task = Task::whereHas('project', function ($query) use ($user) {
                $query->where('owner_id', $user->id);
            })
            ->orWhere('author_id', $user->id)
            ->find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found or access denied'
            ], 404);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }

    public function comments(Request $request, $id)
    {
        $user = $request->user();
        
        $task = Task::whereHas('project', function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('users', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found or access denied'
            ], 404);
        }

        $comments = $task->comments()->with('author')->get();

        return response()->json($comments);
    }
}