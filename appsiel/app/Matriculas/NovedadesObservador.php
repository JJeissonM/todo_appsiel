<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class NovedadesObservador extends Model
{
    protected $table = 'sga_novedades_observador';

    protected $fillable = [ 'id_estudiante', 'id_periodo', 'fecha_novedad', 'tipo_novedad', 'descripcion', 'creado_por', 'modificado_por' ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Estudiante', 'Periodo', 'Fecha novedad', 'Tipo novedad', 'Descripción', 'Creado por'];

    public static function consultar_registros($nro_registros, $search)
    {        
        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';
        }

        $collection = NovedadesObservador::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_novedades_observador.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_novedades_observador.id_periodo')
            ->leftJoin('users', 'users.email', '=', 'sga_novedades_observador.creado_por')
            ->leftJoin('sga_tipos_novedades', 'sga_tipos_novedades.id', '=', 'sga_novedades_observador.tipo_novedad')
            ->select(
                DB::raw($raw_nombre_completo),
                'sga_periodos.descripcion AS campo2',
                'sga_novedades_observador.fecha_novedad AS campo3',
                'sga_tipos_novedades.descripcion AS campo4',
                'sga_novedades_observador.descripcion AS campo5',
                'users.name AS campo6',
                'sga_novedades_observador.id AS campo7'
            )->orderBy('sga_novedades_observador.created_at', 'DESC')
            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7], $search)) {
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
        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS ESTUDIANTE';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS ESTUDIANTE';
        }

        $string = NovedadesObservador::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_novedades_observador.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_novedades_observador.id_periodo')
            ->leftJoin('users', 'users.email', '=', 'sga_novedades_observador.creado_por')
            ->leftJoin('sga_tipos_novedades', 'sga_tipos_novedades.id', '=', 'sga_novedades_observador.tipo_novedad')
            ->select(
                DB::raw($raw_nombre_completo),
                'sga_periodos.descripcion AS PERIODO',
                'sga_novedades_observador.fecha_novedad AS FECHA',
                'sga_tipos_novedades.descripcion AS TIPO_DE_NOVEDAD',
                'sga_novedades_observador.descripcion AS DESCRIPCIÓN',
                'users.name AS CREADO_POR'
            )->where("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_novedades_observador.fecha_novedad", "LIKE", "%$search%")
            ->orWhere("sga_tipos_novedades.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2,' ',core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
            ->orWhere("sga_novedades_observador.descripcion", "LIKE", "%$search%")
            ->orWhere("users.name", "LIKE", "%$search%")
            ->orderBy('sga_novedades_observador.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO NOVEDADES OBSERVADOR DE ESTUDIANTES";
    }

    public static function get_novedades_un_estudiante( $estudiante_id, $nro_registros, $search )
    {
        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';
        }

        $collection = NovedadesObservador::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_novedades_observador.id_estudiante')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                    ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_novedades_observador.id_periodo')
                    ->leftJoin('sga_tipos_novedades', 'sga_tipos_novedades.id', '=', 'sga_novedades_observador.tipo_novedad')
                    ->leftJoin('users', 'users.email', '=', 'sga_novedades_observador.creado_por')
                    ->where('sga_novedades_observador.id_estudiante',$estudiante_id)
                    ->where('sga_novedades_observador.creado_por',Auth::user()->email)
                    ->select( DB::raw($raw_nombre_completo),
                            'sga_periodos.descripcion AS campo2',
                            'sga_novedades_observador.fecha_novedad AS campo3',
                            'sga_tipos_novedades.descripcion AS campo4',
                            'sga_novedades_observador.descripcion AS campo5',
                            'users.name AS campo6',
                            'sga_novedades_observador.id AS campo7')
                    ->get();
                    
        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7], $search)) {
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
}
