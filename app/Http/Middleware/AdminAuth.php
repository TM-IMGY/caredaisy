<?php

namespace App\Http\Middleware;

use Closure;

class AdminAuth
{
    const ADMIN_AUTH_ID = 99;


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (auth()->user()->auth_id == self::ADMIN_AUTH_ID) {
            return $next($request);
        }

        return redirect('top');
    }
}
