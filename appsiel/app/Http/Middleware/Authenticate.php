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

        if ( Auth::guard($guard)->guest() )
        {
            if ( $request->ajax() || $request->wantsJson() )
            {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('/login');
            }
        }

        $user = Auth::user();

        if( isset( $request->id ) && isset( $request->id_modelo ) )
        {
            $id_modelo = (int)$request->id_modelo;

            $array_wheres = [
                ['users.id','=',$user->id],
                ['permissions.core_app_id','=',$request->id]
            ];
            
            if ( $id_modelo != 0 )
            {
                $array_wheres = array_merge( $array_wheres, [['permissions.modelo_id','=',$id_modelo]] );
            }

            $permisos = DB::table('users')
                ->join('user_has_roles','users.id','=','user_has_roles.user_id')
                ->join('roles','user_has_roles.role_id','=','roles.id')
                ->join('role_has_permissions','roles.id','=','role_has_permissions.role_id')
                ->join('permissions','permissions.id','=','role_has_permissions.permission_id')
                ->select('users.id','permissions.core_app_id','permissions.modelo_id')
                ->where( $array_wheres )
                ->get();

            if(sizeof($permisos) == 0){
                return redirect()->back()->with('flash_message','1. Restricción App y Modelo. Necesita permiso para realizar esta acción, por favor comuníquese con el administrador para más detalles');
            }

        }else if(isset($request->id)){

            $permisos = DB::table('users')
                ->join('user_has_roles','users.id','=','user_has_roles.user_id')
                ->join('roles','user_has_roles.role_id','=','roles.id')
                ->join('role_has_permissions','roles.id','=','role_has_permissions.role_id')
                ->join('permissions','permissions.id','=','role_has_permissions.permission_id')
                ->select('users.id','permissions.core_app_id','permissions.modelo_id')
                ->where([
                    ['users.id','=',$user->id],
                    ['permissions.core_app_id','=',$request->id],
                ])
                ->get();

            if(sizeof($permisos) == 0){
                return redirect()->back()->with('flash_message','2. Restricción App. Necesita permiso para realizar esta acción, por favor comuníquese con el administrador para más detalles');
            }

        }


        // CUANDO SE ENVÍAN LOS DATOS VÍA CAMPO HIDDEN EN EL FORMULARIO POST
        if( isset( $request->url_id ) && isset( $request->url_id_modelo ) )
        {
            $permisos = DB::table('users')
                ->join('user_has_roles','users.id','=','user_has_roles.user_id')
                ->join('roles','user_has_roles.role_id','=','roles.id')
                ->join('role_has_permissions','roles.id','=','role_has_permissions.role_id')
                ->join('permissions','permissions.id','=','role_has_permissions.permission_id')
                ->select('users.id','permissions.core_app_id','permissions.modelo_id')
                ->where([
                    ['users.id','=',$user->id],
                    ['permissions.core_app_id','=',$request->url_id],
                    ['permissions.modelo_id','=',$request->url_id_modelo]
                ])
                ->get();

            if(sizeof($permisos) == 0){
                return redirect()->back()->with('flash_message','2. Restricción App y Modelo. Necesita permiso para realizar esta acción, por favor comuníquese con el administrador para más detalles');
            }

        }else if(isset($request->url_id)){

            $permisos = DB::table('users')
                ->join('user_has_roles','users.id','=','user_has_roles.user_id')
                ->join('roles','user_has_roles.role_id','=','roles.id')
                ->join('role_has_permissions','roles.id','=','role_has_permissions.role_id')
                ->join('permissions','permissions.id','=','role_has_permissions.permission_id')
                ->select('users.id','permissions.core_app_id','permissions.modelo_id')
                ->where([
                    ['users.id','=',$user->id],
                    ['permissions.core_app_id','=',$request->url_id],
                ])
                ->get();

            if(sizeof($permisos) == 0){
                return redirect()->back()->with('flash_message','2. Restricción App. Necesita permiso para realizar esta acción, por favor comuníquese con el administrador para más detalles');
            }

        }

        if( $request->method() == 'POST' )
        {
            return $next($request);
        }

        $user = Auth::user();

        if(isset($request->id) && isset($request->id_modelo))
        {
            $id_modelo = (int)$request->id_modelo;

            $array_wheres = [
                ['users.id','=',$user->id],
                ['permissions.core_app_id','=',$request->id]
            ];
            
            if ( $id_modelo != 0 )
            {
                $array_wheres = array_merge( $array_wheres, [['permissions.modelo_id','=',$id_modelo]] );
            }

            $permisos = DB::table('users')
                ->join('user_has_roles','users.id','=','user_has_roles.user_id')
                ->join('roles','user_has_roles.role_id','=','roles.id')
                ->join('role_has_permissions','roles.id','=','role_has_permissions.role_id')
                ->join('permissions','permissions.id','=','role_has_permissions.permission_id')
                ->select('users.id','permissions.core_app_id','permissions.modelo_id')
                ->where( $array_wheres )
                ->get();
            
            if(sizeof($permisos) == 0){
                return redirect()->back()->with('flash_message','3. Método GET. Restricción App y Modelo. Necesita permiso para realizar esta acción, por favor comuníquese con el administrador para más detalles');
            }

        }else if(isset($request->id)){
            $permisos = DB::table('users')
                ->join('user_has_roles','users.id','=','user_has_roles.user_id')
                ->join('roles','user_has_roles.role_id','=','roles.id')
                ->join('role_has_permissions','roles.id','=','role_has_permissions.role_id')
                ->join('permissions','permissions.id','=','role_has_permissions.permission_id')
                ->select('users.id','permissions.core_app_id','permissions.modelo_id')
                ->where([
                    ['users.id','=',$user->id],
                    ['permissions.core_app_id','=',$request->id],
                ])
                ->get();

            if(sizeof($permisos) == 0){
                return redirect()->back()->with('flash_message','3. Método GET. Restricción App. Necesita permiso para realizar esta acción, por favor comuníquese con el administrador para más detalles.');
            }

        }

        return $next($request);
    }
}
