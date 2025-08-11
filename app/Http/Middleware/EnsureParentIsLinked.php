<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureParentIsLinked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user->role->value === 'parent' && $user->children()->doesntExist()) {
            // They are a parent but not linked to any student yet.
            // We can redirect them to a waiting page or show an error.
            // For now, we'll abort with a helpful message.
            abort(403, 'Your parent account is not yet linked to a student. Please contact the school administration.');
        }

        return $next($request);
    }
}
