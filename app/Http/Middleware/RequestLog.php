<?php

namespace App\Http\Middleware;

use Closure;
use Log;

class RequestLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $log = '[' . __CLASS__.':'.__FUNCTION__.':'.__LINE__. '] api:';
        $log .= $request->method() . ' ' . $request->path();
        $log .= ' ' . preg_replace(['/\s+/s','/^Array \(/','/\)[^\)]*?$/'], [' ','{','}'], print_r($request->input(), true));
        Log::channel('api')->notice($log);

        return $next($request);
    }
}
