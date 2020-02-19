<?php

namespace App\AcademicoDocente;

use Illuminate\Database\Eloquent\Model;

use App\UserHasRole;
use DB;

class Profesor extends Model
{
    protected $table = 'users';

    protected $fillable = ['empresa_id','name', 'email', 'password'];

    public $encabezado_tabla = ['Perfil','Nombre','Email','Acción'];

    public static function consultar_registros()
    {
        $registros = UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
					            ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
					            ->where(['roles.name'=>'Profesor'])
					            ->orWhere(['roles.name'=>'Director de grupo'])
					            ->select('roles.name AS campo1','users.name AS campo2','users.email As campo3','users.id AS campo4')
					            ->get()
					            ->toArray();

	    return $registros;
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
}
