<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user is authenticated, active, and has employee role
        if (!$user || !$user->isActive() || !$user->isEmployee()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Employee access required.',
            ], 403);
        }

        // Check if user has employee record
        if (!$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found.',
            ], 404);
        }

        return $next($request);
    }
}
