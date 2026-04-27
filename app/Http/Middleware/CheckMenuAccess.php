<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, $action = 'view'): Response
    {
        $routeName = $request->route()->getName();
        
        // If route has no name or user is not logged in, we let it pass or block depending on auth middleware
        if (!$routeName || !auth()->check()) {
            return $next($request);
        }

        // We check if the user has access to this route with the specific action
        if (!auth()->user()->hasMenuAccess($routeName, $action)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'You do not have permission to perform this action.'], 403);
            }
            abort(403, 'You do not have permission to access this page or perform this action.');
        }

        return $next($request);
    }
}
