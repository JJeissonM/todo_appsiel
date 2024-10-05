<?php

namespace App\VentasPos;

use App\User;

use App\UserHasRole;

use Illuminate\Pagination\LengthAwarePaginator;

class Cajero extends User
{
    protected $table = 'users';
    
    protected $fillable = ['empresa_id', 'name', 'email'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","store":"core/usuarios","update":"core/usuarios/id_fila"}';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nombre', 'Usuario / Email'];

    public static function consultar_registros($nro_registros, $search)
    {
        $collection = UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
                        ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
                        ->where(['roles.name' => 'Cajero PDV'])
                        ->select(
                                    'users.name AS campo1',
                                    'users.email AS campo2',
                                    'users.id AS campo3'
                                )
                        ->orderBy('users.created_at', 'DESC')
                        ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8], $search)) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        $request = request(); //obtenemos el Request para obtener la url y la query builder

        if (empty($nuevaColeccion)) {
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
        foreach ($valores_campos_seleccionados as $valor_campo) {
            $str = str_slug($valor_campo);
            $pos = strpos($str, $searchTerm);
            if ($pos !== false) {
                $encontrado = true;
            }
        }
        return $encontrado;
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
