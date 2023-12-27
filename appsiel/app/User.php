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

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

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

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","store":"core/usuarios","update":"core/usuarios/id_fila","change_password":"user/change_password/user_id"}';

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
        $collection = UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
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
                                ->orderBy('users.created_at', 'DESC')
                                ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if ( self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8], $search)) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        $request = request(); //obtenemos el Request para obtener la url y la query builder

        if ( empty($nuevaColeccion) )
        {
            return $array = new LengthAwarePaginator([], 1, 1, 1, [
                                                                    'path' => $request->url(),
                                                                    'query' => $request->query(),
                                                                ]);
        }
        
        //obtenemos el numero de la página actual, por defecto 1
        $page = 1;
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        }
        $total = count($nuevaColeccion); //Total para contar los registros mostrados
        $starting_point = ($page * $nro_registros) - $nro_registros; // punto de inicio para mostrar registros
        $array = $nuevaColeccion->slice($starting_point, $nro_registros); //indicamos desde donde y cuantos registros mostrar
        $array = new LengthAwarePaginator($array, $total, $nro_registros, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]); //finalmente se pagina y organiza la coleccion a devolver con todos los datos

        return $array;
    }

    /**
     * SQL Like operator in PHP.
     * Returns TRUE if match else FALSE.
     * @param array $valores_campos_seleccionados de campos donde se busca
     * @param string $searchTerm termino de busqueda
     * @return bool
     */
    public static function likePhp($valores_campos_seleccionados, $searchTerm)
    {
        $encontrado = false;
        $searchTerm = str_slug($searchTerm); // Para eliminar acentos
        foreach ($valores_campos_seleccionados as $valor_campo)
        {
            $str = str_slug($valor_campo);
            $pos = strpos($str, $searchTerm);
            if ($pos !== false)
            {
                $encontrado = true;
            }
        }
        return $encontrado;
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

    public static function crear_y_asignar_role( $name, $email, $role_id, $password )
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

	public static function opciones_campo_select()
    {
        $opciones = User::where('id', '<>', 1)
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->name;
        }

        return $vec;
    }
}
