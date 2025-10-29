<?php

namespace App\Nomina;

use App\Nomina\CambioSalario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class TipoTurno extends Model
{
    protected $table = 'nom_turnos_tipos';

    /**
     * estado => { Pendiente | Liquidado}
     */
    protected $fillable = ['descripcion', 'valor', 'detalle', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Tipo Turno', 'Valor', 'Detalle', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $collection = TipoTurno::select(
                'descripcion AS campo1',
                'valor AS campo2',
                'detalle AS campo3',
                'estado AS campo4',
                'id AS campo5'
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
}
