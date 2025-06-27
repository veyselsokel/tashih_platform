<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For now, this middleware simply allows all requests to pass through.
        // You can add your admin specific logic here later.
        // Example: if (!auth()->check() || !auth()->user()->isAdmin()) {
        //              abort(403, 'Unauthorized action.');
        //          }
        return $next($request);
    }
}
