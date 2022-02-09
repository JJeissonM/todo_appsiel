<?php

namespace App\Ventas;

use App\Core\Foro;
use App\Core\Fororespuesta;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Cmgmyr\Messenger\Traits\Messagable;

use App\User;

use DB;
use Auth;
use Hash;

use App\Core\PasswordReset;
use App\UserHasRole;
//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class SellerUser extends User
{
    protected $table = 'users';

    public static function opciones_campo_select()
    {
        $opciones = UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
                                ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
                                ->where(['roles.name'=>'Vendedor'])
                                ->select('roles.name','users.name AS descripcion','users.id')
                                ->get();

        $vec[] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
