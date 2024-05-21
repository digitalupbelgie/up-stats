<?php

namespace Digitalup\UpStats\Http\Middleware;

use Closure;
use Illuminate\Http\Request;



class UpstatsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/');
        }
        
        if (!$user->canAccessStats()) {
            return redirect('/');
        }

        return $next($request);
    }
}