<?php

namespace Digitalup\UpStats\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Digitalup\UpStats\UpStats;

class AssignCookie
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
        $cookie = "";
        if (!$request->cookie('upstats_user_cookie')) {
            $cookieValue = uniqid();
            // Set the cookie with a name 'user_cookie' and the generated value
            Cookie::queue('upstats_user_cookie', $cookieValue, 60); // 60 is the number of minutes the cookie will be valid

            $cookie = 'upstats_user_cookie=' . $cookieValue;
            $request->headers->set('cookie', $cookie);
        }

        // Instantiate the UpStats class
        $UpStats = new UpStats();

        // Call the store method
        $UpStats->store();

        // Continue with the request
        return $next($request);
    }
}
