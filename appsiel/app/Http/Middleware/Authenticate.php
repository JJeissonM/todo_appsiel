<?php

namespace App\Http\Middleware;

use App\UserHasRole;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('/login');
            }
        }

        if(isset($request->id) || isset($request->id_modelo)){
            $user = Auth::user();
            $permisos = DB::table('users')
                ->join('user_has_roles','users.id','=','user_has_roles.user_id')
                ->join('roles','user_has_roles.role_id','=','roles.id')
                ->join('role_has_permissions','roles.id','=','role_has_permissions.role_id')
                ->join('permissions','permissions.id','=','role_has_permissions.permission_id')
                ->select('users.id','permissions.core_app_id','permissions.modelo_id')
                ->where('users.id','=',$user->id)
                ->get();

            $flat = false;
            foreach ($permisos as $key => $value){
               if($value->modelo_id == $request->id_modelo || $value->core_app_id == $request->id){
                   $flat = true;
                   break;
               }
            }
            if(!$flat){
                return redirect()->back();
            }
        }

        return $next($request);
    }
}
