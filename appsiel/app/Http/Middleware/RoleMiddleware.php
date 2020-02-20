<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (Auth::guest()) {
            return redirect()->to('login');
        }
        
        if (! $request->user()->hasRole($role)) {
           abort(401);
            //echo "usuario no autorizado";
        }

       return $next($request);
    }
}
