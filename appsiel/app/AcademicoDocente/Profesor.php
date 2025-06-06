<?php

namespace App\AcademicoDocente;

use App\Core\Tercero;
use App\Sistema\Modelo;
use Illuminate\Database\Eloquent\Model;

use App\UserHasRole;

use Illuminate\Pagination\LengthAwarePaginator;

class Profesor extends Model
{
    protected $table = 'users';

    protected $fillable = ['empresa_id','name', 'email', 'password', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nombre', 'Email', 'Perfil', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $collection = UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
                            ->where(['roles.name' => 'Profesor'])
                            ->orWhere(['roles.name' => 'Director de grupo'])
                            ->select(
                                'users.name AS campo1',
                                'users.email As campo2',
                                'roles.name AS campo3',
                                'users.estado AS campo4',
                                'users.id AS campo5'
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
            ->where(['roles.name' => 'Profesor'])
            ->orWhere(['roles.name' => 'Director de grupo'])
            ->select(
                'roles.name AS PERFIL',
                'users.name AS NOMBRE',
                'users.email As EMAIL',
                'users.estado As ESTADO'
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
                                ->where([
                                    ['roles.name', '=', 'Profesor'],
                                    ['users.estado', '=', 'Activo']
                                    ])
                                ->orWhere(['roles.name'=>'Director de grupo'])
                                ->select('roles.name AS role','users.name AS descripcion','users.id')
                                ->orderBy('descripcion')
                                ->get();

        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id] = $opcion->descripcion . ' (' . $opcion->role . ')';
        }
        
        return $vec;
    }

    public function get_campos_adicionales_create( $lista_campos )
    {
        $lista_campos = $this->agregar_campos_adicionales($lista_campos, null);

        return $lista_campos;
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $tercero_del_usuario = Tercero::where('user_id', $registro->id)->get()->first();

        $lista_campos = $this->agregar_campos_adicionales($lista_campos, $tercero_del_usuario);

        return $lista_campos;
    }

    public function agregar_campos_adicionales($lista_campos, $tercero_del_usuario)
    {
        $id = null;
        $numero_identificacion = '';
        $direccion1 = '';
        $telefono1 = '';
        $imagen = '';
        if ($tercero_del_usuario != null) {
            $id = $tercero_del_usuario->id;
            $numero_identificacion = $tercero_del_usuario->numero_identificacion;
            $direccion1 = $tercero_del_usuario->direccion1;
            $telefono1 = $tercero_del_usuario->telefono1;

            $imagen = url('/') . '/appsiel//storage/app/firmas_autorizadas/' . $tercero_del_usuario->firma_autorizada->imagen;

        }

        
        $campo_core_tercero_id_esta = false;
        $cantida_campos = count($lista_campos);
        // Personalizar campos
        for ($i=0; $i <  $cantida_campos; $i++)
        {
            if ( $lista_campos[$i]['name'] == 'core_tercero_id' ) 
            {
                $lista_campos[$i]['value'] = $id;
                $lista_campos[$i]['tipo'] = 'hidden';
                $lista_campos[$i]['opciones'] = '';
                $lista_campos[$i]['atributos'] = [];
                $campo_core_tercero_id_esta = true;
            }
        }
        
        if ( !$campo_core_tercero_id_esta ) {
            array_push($lista_campos, [
                "id" => 588,
                "descripcion" => 'Tercero',
                "tipo" => "hidden",
                "name" => "core_tercero_id",
                "opciones" => "",
                "value" => $id,
                "atributos" => [],
                "definicion" => "",
                "requerido" => 0,
                "editable" => 1,
                "unico" => 0
            ]);
        }
        array_push($lista_campos, [
            "id" => 46,
            "descripcion" => 'Número identificación',
            "tipo" => "bsText",
            "name" => "numero_identificacion",
            "opciones" => "",
            "value" => $numero_identificacion,
            "atributos" => [],
            "definicion" => "",
            "requerido" => 1,
            "editable" => 1,
            "unico" => 0
        ]);
        array_push($lista_campos, [
            "id" => 50,
            "descripcion" => 'Dirección',
            "tipo" => "bsText",
            "name" => "direccion1",
            "opciones" => "",
            "value" => $direccion1,
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);
        array_push($lista_campos, [
            "id" => 55,
            "descripcion" => 'Teléfono',
            "tipo" => "bsText",
            "name" => "telefono1",
            "opciones" => "",
            "value" => $telefono1,
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);
        array_push($lista_campos, [
            "id" => 140,
            "descripcion" => 'Imágen Firma',
            "tipo" => "imagen",
            "name" => "imagen",
            "opciones" => "jpg,png,gif",
            "value" => $imagen,
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);

        return $lista_campos;
    }
}
