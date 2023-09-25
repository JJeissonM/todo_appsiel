<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ObservacionesBoletin extends Model
{
    protected $table = 'sga_observaciones_boletines';

    protected $fillable = ['codigo_matricula','id_colegio','id_periodo','curso_id','id_estudiante','observacion','puesto'];

    public static function consultar_registros($nro_registros, $search)
    {
        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo4';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo4';
        }

        return ObservacionesBoletin::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_observaciones_boletines.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_observaciones_boletines.curso_id')
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_observaciones_boletines.id_periodo')
            ->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
            ->select(
                'sga_periodos_lectivos.descripcion AS campo1',
                'sga_periodos.descripcion AS campo2',
                'sga_cursos.descripcion AS campo3',
                DB::raw($raw_nombre_completo),
                'sga_observaciones_boletines.puesto AS campo5',
                'sga_observaciones_boletines.observacion AS campo6',
                'sga_observaciones_boletines.id AS campo7'
            )->orWhere("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres)'), "LIKE", "%$search%")
            ->orWhere("sga_observaciones_boletines.puesto", "LIKE", "%$search%")
            ->orWhere("sga_observaciones_boletines.observacion", "LIKE", "%$search%")
            ->orderBy('sga_observaciones_boletines.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString_consultar_registros($search)
    {
        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS ESTUDIANTE';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS ESTUDIANTE';
        }

        $string = ObservacionesBoletin::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_observaciones_boletines.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_observaciones_boletines.curso_id')
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_observaciones_boletines.id_periodo')
            ->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
            ->select(
                'sga_periodos_lectivos.descripcion AS AÑO',
                'sga_periodos.descripcion AS PERÍODO',
                'sga_cursos.descripcion AS CURSO',
                DB::raw($raw_nombre_completo),
                'sga_observaciones_boletines.puesto AS PUESTO',
                'sga_observaciones_boletines.observacion AS OBSERVACIÓN'
            )->orWhere("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres)'), "LIKE", "%$search%")
            ->orWhere("sga_observaciones_boletines.puesto", "LIKE", "%$search%")
            ->orWhere("sga_observaciones_boletines.observacion", "LIKE", "%$search%")
            ->orderBy('sga_observaciones_boletines.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    public static function consultar_registros_director_grupo($nro_registros, $search)
    {
        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo4';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo4';
        }

        $collection = ObservacionesBoletin::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_observaciones_boletines.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_observaciones_boletines.curso_id')
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_observaciones_boletines.id_periodo')
            ->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
            ->leftJoin('sga_curso_tiene_director_grupo', 'sga_curso_tiene_director_grupo.curso_id', '=', 'sga_cursos.id')
            ->where('sga_curso_tiene_director_grupo.user_id', Auth::user()->id)
            ->select(
                'sga_periodos_lectivos.descripcion AS campo1',
                'sga_periodos.descripcion AS campo2',
                'sga_cursos.descripcion AS campo3',
                DB::raw($raw_nombre_completo),
                'sga_observaciones_boletines.puesto AS campo5',
                'sga_observaciones_boletines.observacion AS campo6',
                'sga_observaciones_boletines.id AS campo7'
            )
            ->orderBy('sga_observaciones_boletines.created_at', 'DESC')
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

    public static function sqlString_registros_director_grupo($search)
    {
        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS ESTUDIANTE';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS ESTUDIANTE';
        }

        $string = ObservacionesBoletin::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_observaciones_boletines.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_observaciones_boletines.curso_id')
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_observaciones_boletines.id_periodo')
            ->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
            ->leftJoin('sga_curso_tiene_director_grupo', 'sga_curso_tiene_director_grupo.curso_id', '=', 'sga_cursos.id')
            ->where('sga_curso_tiene_director_grupo.user_id', Auth::user()->id)
            ->select(
                'sga_periodos_lectivos.descripcion AS AÑO',
                'sga_periodos.descripcion AS PERÍODO',
                'sga_cursos.descripcion AS CURSO',
                DB::raw($raw_nombre_completo),
                'sga_observaciones_boletines.puesto AS PUESTO',
                'sga_observaciones_boletines.observacion AS OBSERVACIÓN'
            )->orWhere("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres)'), "LIKE", "%$search%")
            ->orWhere("sga_observaciones_boletines.puesto", "LIKE", "%$search%")
            ->orWhere("sga_observaciones_boletines.observacion", "LIKE", "%$search%")
            ->orderBy('sga_observaciones_boletines.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    public static function get_cantidad_x_matricula( $colegio_id, $codigo_matricula)
    {
        return ObservacionesBoletin::where(
                                    [ 
                                        'id_colegio' => $colegio_id,
                                        'codigo_matricula' => $codigo_matricula
                                    ]
                                )
                            ->count();
    }

    public static function get_observaciones_boletines( $colegio_id, $periodo_id, $curso_id)
    {
        return ObservacionesBoletin::where(
                                            [
                                                'id_colegio' => $colegio_id,
                                                'id_periodo' => $periodo_id,
                                                'curso_id' => $curso_id
                                            ]
                                        )
                                    ->get();
    }

    public static function get_x_estudiante( $periodo_id, $curso_id, $estudiante_id)
    {
        return ObservacionesBoletin::where(
                                            [
                                                'id_periodo' => $periodo_id,
                                                'curso_id' => $curso_id,
                                                'id_estudiante' => $estudiante_id
                                            ]
                                        )
                                    ->get()
                                    ->first();
    }
}
