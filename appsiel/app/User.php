<?php

namespace App;

use App\Core\Foro;
use App\Core\Fororespuesta;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Cmgmyr\Messenger\Traits\Messagable;

use DB;
use Auth;
use Hash;

use App\Core\PasswordReset;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class User extends Authenticatable
{
    use HasRoles;
    use Messagable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'empresa_id', 'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /*public function roles(){
        return $this->hasMany(UserHasRole::class,'user_id','id');
    }*/

    public function empresa()
    {
        return $this->belongsTo('App\Core\Empresa');
    }

    public function foros()
    {
        return $this->hasMany(Foro::class);
    }

    public function fororespuestas()
    {
        return $this->hasMany(Fororespuesta::class);
    }

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Empresa', 'Nombre', 'Email', 'Fecha creación', 'Perfil'];

    public static function consultar_registros($nro_registros, $search)
    {
        return UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
            ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
            ->leftJoin('core_empresas', 'core_empresas.id', '=', 'users.empresa_id')
            ->where('users.id', '<>', 1)
            ->select(
                'core_empresas.descripcion AS campo1',
                'users.name AS campo2',
                'users.email As campo3',
                'users.created_at AS campo4',
                'roles.name AS campo5',
                'users.id AS campo6'
            )
            ->orWhere("core_empresas.descripcion", "LIKE", "%$search%")
            ->orWhere("users.name", "LIKE", "%$search%")
            ->orWhere("users.email", "LIKE", "%$search%")
            ->orWhere("users.created_at", "LIKE", "%$search%")
            ->orWhere("roles.name", "LIKE", "%$search%")
            ->orderBy('users.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
            ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
            ->leftJoin('core_empresas', 'core_empresas.id', '=', 'users.empresa_id')
            ->where('users.id', '<>', 1)
            ->select(
                'core_empresas.descripcion AS EMPRESA',
                'users.name AS NOMBRE',
                'users.email As EMAIL',
                'users.created_at AS FECHA_CREACIÓN',
                'roles.name AS PERFIL'
            )
            ->orWhere("core_empresas.descripcion", "LIKE", "%$search%")
            ->orWhere("users.name", "LIKE", "%$search%")
            ->orWhere("users.email", "LIKE", "%$search%")
            ->orWhere("users.created_at", "LIKE", "%$search%")
            ->orWhere("roles.name", "LIKE", "%$search%")
            ->orderBy('users.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE USUARIOS";
    }

    public static function crear_y_asignar_role($name, $email, $role_id, $password)
    {
        if ( !is_null( User::where('email',$email)->get()->first() ) )
        {
            return null;
        }
        
        $user = User::create([
                                'empresa_id' => Auth::user()->empresa_id,
                                'name' => $name,
                                'email' => $email,
                                'password' => Hash::make($password)
                            ]);

        $role_r = Role::where('id', '=', $role_id)->firstOrFail();
        $user->assignRole($role_r); //Assigning role to user

        // Se almacena la contraseña temporalmente; cuando el usuario la cambie, se eliminará
        PasswordReset::insert([
                                'email' => $email,
                                'token' => $password,
                                'created_at' => date('Y-m-d H:i:s') ]);

        return $user;
    }
}
