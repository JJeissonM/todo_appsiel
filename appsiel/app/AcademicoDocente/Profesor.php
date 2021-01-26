<?php

namespace App\AcademicoDocente;

use Illuminate\Database\Eloquent\Model;

use App\UserHasRole;
use DB;

class Profesor extends Model
{
    protected $table = 'users';

    protected $fillable = ['empresa_id','name', 'email', 'password'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nombre', 'Email', 'Perfil'];

    public static function consultar_registros($nro_registros, $search)
    {
        if ( $search == '')
        {
            return UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
                            ->where(['roles.name' => 'Profesor'])
                            ->orWhere(['roles.name' => 'Director de grupo'])
                            ->select(
                                'users.name AS campo1',
                                'users.email As campo2',
                                'roles.name AS campo3',
                                'users.id AS campo4'
                            )
                            ->orderBy('users.name')
                            ->paginate($nro_registros);
        }

        return UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
                        ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
                        ->where(['roles.name' => 'Profesor'])
                        ->orWhere(['roles.name' => 'Director de grupo'])
                        ->select(
                            'users.name AS campo1',
                            'users.email As campo2',
                            'roles.name AS campo3',
                            'users.id AS campo4'
                        )
                        ->where("roles.name", "LIKE", "%$search%")
                        ->orWhere("users.name", "LIKE", "%$search%")
                        ->orWhere("users.email", "LIKE", "%$search%")
                        ->orderBy('users.created_at', 'DESC')
                        ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
            ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
            ->where(['roles.name' => 'Profesor'])
            ->orWhere(['roles.name' => 'Director de grupo'])
            ->select(
                'roles.name AS PERFIL',
                'users.name AS NOMBRE',
                'users.email As EMAIL'
            )
            ->where("roles.name", "LIKE", "%$search%")
            ->orWhere("users.name", "LIKE", "%$search%")
            ->orWhere("users.email", "LIKE", "%$search%")
            ->orderBy('users.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PROFESORES";
    }

    public static function get_array_to_select()
    {
        $opciones = UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
                                ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
                                ->where(['roles.name'=>'Profesor'])
                                ->orWhere(['roles.name'=>'Director de grupo'])
                                ->select('roles.name','users.name AS descripcion','users.id')
                                ->get();

        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id] = $opcion->descripcion;
        }
        
        return $vec;
    }

    public static function opciones_campo_select()
    {
        $opciones = UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
                                ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
                                ->where(['roles.name'=>'Profesor'])
                                ->orWhere(['roles.name'=>'Director de grupo'])
                                ->select('roles.name','users.name AS descripcion','users.id')
                                ->get();

        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id] = $opcion->descripcion;
        }
        
        return $vec;
    }
}
