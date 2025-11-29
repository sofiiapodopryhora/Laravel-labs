<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Отримуємо проєкти, де користувач є власником або учасником
        $projects = Project::where('owner_id', $user->id)
            ->orWhereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['owner', 'users'])
            ->get();

        return response()->json($projects);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => $request->user()->id,
        ]);

        return response()->json($project->load(['owner', 'users']), 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $project = Project::with(['owner', 'users', 'tasks'])
            ->where('id', $id)
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

        return response()->json($project);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        
        $project = Project::where('id', $id)
            ->where('owner_id', $user->id)
            ->first();

        if (!$project) {
            return response()->json([
                'message' => 'Project not found or access denied'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $project->update($request->only(['name', 'description']));

        return response()->json($project->load(['owner', 'users']));
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        $project = Project::where('id', $id)
            ->where('owner_id', $user->id)
            ->first();

        if (!$project) {
            return response()->json([
                'message' => 'Project not found or access denied'
            ], 404);
        }

        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully'
        ]);
    }

    public function tasks(Request $request, $id)
    {
        $user = $request->user();
        
        $project = Project::where('id', $id)
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

        $query = $project->tasks()->with(['author', 'assignee']);

        // Фільтрація задач
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('assignee_id')) {
            $query->where('assignee_id', $request->assignee_id);
        }

        $tasks = $query->get();

        return response()->json($tasks);
    }
}