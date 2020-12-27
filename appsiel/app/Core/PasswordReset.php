<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use App\UserHasRole;

class PasswordReset extends Model
{
    protected $table = 'password_resets';

    protected $fillable = ['email', 'token', 'created_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Perfil', 'Nombre', 'Usuario/Email', 'Contraseña actual', 'Fecha actualización'];

    public $urls_acciones = '{"show":"no"}';

    public static function consultar_registros($nor_registros)
    {
        return PasswordReset::leftJoin('users', 'users.email', '=', 'password_resets.email')
            ->leftJoin('user_has_roles', 'user_has_roles.user_id', '=', 'users.id')
            ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
            ->select(
                'roles.name AS campo1',
                'users.name AS campo2',
                'users.email As campo3',
                'password_resets.token AS campo4',
                'password_resets.created_at AS campo5',
                'users.id AS campo6'
            )->orderBy('users.created_at', 'DESC')
            ->paginate($nor_registros);
    }
}
