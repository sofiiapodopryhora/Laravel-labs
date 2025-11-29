<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProjectAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $projectId = $request->route('id') ?? $request->route('project');

        if (!$projectId) {
            return response()->json(['message' => 'Project ID required'], 400);
        }

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
            ], 403);
        }

        // Додаємо проєкт до request для подальшого використання
        $request->merge(['project' => $project]);

        return $next($request);
    }
}
