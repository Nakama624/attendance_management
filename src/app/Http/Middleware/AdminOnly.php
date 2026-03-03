<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || (int)$user->manager_flg !== 1) {
            abort(403);
        }
        return $next($request);
    }
}

