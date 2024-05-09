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
        $reporter = $request->user(); // Assuming your user model is Reporter

        if ($reporter && $reporter->role !== ROLE_ADMIN) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ]);
        }

        return $next($request);
    }
}
