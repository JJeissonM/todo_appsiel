<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TipoTurno extends Model
{
    protected $table = 'nom_turnos_tipos';

    /**
     * estado => { Activo | Inactivo}
     */
    protected $fillable = ['descripcion', 'checkin_time_1', 'checkout_time_1', 'checkin_time_2', 'checkout_time_2', 'valor', 'detalle', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Tipo Turno', 'Hora entrada 1', 'Hora salida 1', 'Hora entrada 2', 'Hora salida 2', 'Valor', 'Detalle', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $collection = TipoTurno::select(
                'descripcion AS campo1',
                'checkin_time_1 AS campo2',
                'checkout_time_1 AS campo3',
                'checkin_time_2 AS campo4',
                'checkout_time_2 AS campo5',
                'valor AS campo6',
                'detalle AS campo7',
                'estado AS campo8',
                'id AS campo9'
            )
            ->orderBy('descripcion')
            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5], $search)) {
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
        $string = TipoTurno::select(
                'descripcion AS TIPO_TURNO',
                'checkin_time_1 AS HORA_ENTRADA_1',
                'checkout_time_1 AS HORA_SALIDA_1',
                'checkin_time_2 AS HORA_ENTRADA_2',
                'checkout_time_2 AS HORA_SALIDA_2',
                'valor AS VALOR',
                'detalle AS DETALLE',
                'estado AS ESTADO',
                'id AS ID'
            )
            ->orWhere("descripcion", "LIKE", "%$search%")
            ->orWhere("valor", "LIKE", "%$search%")
            ->orWhere("detalle", "LIKE", "%$search%")
            ->orWhere("estado", "LIKE", "%$search%")
            ->orderBy('descripcion')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE TIPOS DE TURNOS";
    }

    /**
     * 
     */
    public static function opciones_campo_select()
    {
        $opciones = TipoTurno::where('estado', 'Activo')->orderBy('descripcion')->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion . ' ($' . number_format($opcion->valor,0,',','.') . ')';
        }

        return $vec;
    }

    public function store_adicional($datos, $registro)
    {
        if($registro->checkin_time_1 == "00:00:00" || $registro->checkin_time_1 == '')
        {
            $registro->checkin_time_1 = null;
        }
        if($registro->checkout_time_1 == "00:00:00" || $registro->checkout_time_1 == '')
        {
            $registro->checkout_time_1 = null;
        }
        if($registro->checkin_time_2 == "00:00:00" || $registro->checkin_time_2 == '')
        {
            $registro->checkin_time_2 = null;
        }
        if($registro->checkout_time_2 == "00:00:00" || $registro->checkout_time_2 == '')
        {
            $registro->checkout_time_2 = null;
        }

        $registro->save();

    }

    public function update_adicional($datos, $id )
    {
        $registro = TipoTurno::find($id);

        if($registro->checkin_time_1 == "00:00:00" || $registro->checkin_time_1 == '')
        {
            $registro->checkin_time_1 = null;
        }
        if($registro->checkout_time_1 == "00:00:00" || $registro->checkout_time_1 == '')
        {
            $registro->checkout_time_1 = null;
        }
        if($registro->checkin_time_2 == "00:00:00" || $registro->checkin_time_2 == '')
        {
            $registro->checkin_time_2 = null;
        }
        if($registro->checkout_time_2 == "00:00:00" || $registro->checkout_time_2 == '')
        {
            $registro->checkout_time_2 = null;
        }

        $registro->save();
    }

    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"nom_turnos_registros",
                                    "llave_foranea":"tipo_turno_id",
                                    "mensaje":"Está asignado en registros de turnos."
                                }
                        }';

        $tablas = json_decode( $tablas_relacionadas );
        //$cantidad = count($tablas);
        foreach($tablas AS $una_tabla)
        { 
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $id )->get();

            if ( !empty($registro) )
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    } 
}
