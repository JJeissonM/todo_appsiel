<?php

namespace App\VentasPos;

use Illuminate\Database\Eloquent\Model;

use App\UserHasRole;
use DB;

class Cajero extends Model
{
    protected $table = 'users';
    protected $fillable = ['empresa_id', 'name', 'email'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","store":"core/usuarios","update":"core/usuarios/id_fila"}';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nombre', 'Usuario / Email'];

    public static function consultar_registros($nro_registros, $search)
    {
        return UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
            ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
            ->where(['roles.name' => 'Cajero PDV'])
            ->select(
                'users.name AS campo1',
                'users.email AS campo2',
                'users.id AS campo3'
            )

            ->orWhere("users.name", "LIKE", "%$search%")
            ->orWhere("users.email", "LIKE", "%$search%")
            ->orderBy('users.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
            ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
            ->where(['roles.name' => 'Cajero PDV'])
            ->select(
                'users.name AS Nombre',
                'users.email AS ´Usuario / Email´'
            )

            ->orWhere("users.name", "LIKE", "%$search%")
            ->orWhere("users.email", "LIKE", "%$search%")
            ->orderBy('users.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ";
    }


    public static function opciones_campo_select()
    {
        $opciones = UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
            ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
            ->where(['roles.name' => 'Cajero PDV'])
            ->select('users.id', 'users.name')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->name;
        }

        return $vec;
    }
}
