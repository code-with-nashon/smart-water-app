<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isUser()) {
            return $next($request);
        }
        // If not a regular user, redirect to base dashboard (which might redirect to admin dashboard if admin)
        return redirect('/dashboard')->with('error', 'You do not have user access.');
    }
}