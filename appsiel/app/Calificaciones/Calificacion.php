<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

use App\Calificaciones\EscalaValoracion;
use App\Calificaciones\Periodo;
use App\Calificaciones\NotaNivelacion;

use App\Calificaciones\Asignatura;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class Calificacion extends Model
{
    protected $table = 'sga_calificaciones';

    // logros es un string donde se almacenan códigos de logros separados por coma (usado para logros adicionales)
    protected $fillable = [ 'codigo_matricula', 'id_colegio', 'anio', 'id_periodo', 'curso_id', 'id_estudiante', 'id_asignatura', 'calificacion', 'logros', 'creado_por', 'modificado_por'];

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class,'id_asignatura');
    }

    public function nota_nivelacion()
    {
        return NotaNivelacion::where('periodo_id', $this->id_periodo)
                            ->where('curso_id', $this->curso_id)
                            ->where('asignatura_id', $this->id_asignatura)
                            ->where('estudiante_id', $this->id_estudiante)
                            ->get()
                            ->first();
    }

    /**
     * Obtener todas las calificaciones con sus datos relacionados
     */
    public static function get_calificaciones($id_colegio, $curso_id, $asignatura_id, $periodo_id = null, $nro_registros, $search)
    {
        $array_wheres = ['sga_calificaciones.id_colegio' => $id_colegio];

        if ($curso_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones.curso_id' => $curso_id]);
        }

        if ($asignatura_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones.id_asignatura' => $asignatura_id]);
        }

        if ($periodo_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones.id_periodo' => $periodo_id]);
        }

        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo4';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo4';
        }

        $collection = Calificacion::where($array_wheres)
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_calificaciones.id_periodo')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_calificaciones.curso_id')
            ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_calificaciones.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_calificaciones.id_asignatura')
            ->select(
                'sga_calificaciones.anio AS campo1',
                'sga_periodos.descripcion AS campo2',
                'sga_cursos.descripcion AS campo3',
                DB::raw($raw_nombre_completo),
                'sga_asignaturas.descripcion AS campo5',
                'sga_calificaciones.calificacion AS campo6',
                'sga_calificaciones.id AS campo7')
            ->orderBy('sga_calificaciones.created_at','DESC')
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

    public static function sqlString($id_colegio, $curso_id, $asignatura_id, $periodo_id = null, $search)
    {
        $array_wheres = ['sga_calificaciones.id_colegio' => $id_colegio];

        if ($curso_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones.curso_id' => $curso_id]);
        }

        if ($asignatura_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones.id_asignatura' => $asignatura_id]);
        }

        if ($periodo_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones.id_periodo' => $periodo_id]);
        }

        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS ESTUDIANTE';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS ESTUDIANTE';
        }

        $string = Calificacion::where($array_wheres)
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_calificaciones.id_periodo')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_calificaciones.curso_id')
            ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_calificaciones.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_calificaciones.id_asignatura')
            ->select(
                'sga_calificaciones.anio AS AÑO',
                'sga_periodos.descripcion AS PERÍODO',
                'sga_cursos.descripcion AS CURSO',
                DB::raw($raw_nombre_completo),
                'sga_asignaturas.descripcion AS ASIGNATURA',
                'sga_calificaciones.calificacion AS CALIFICACIÓN'
            )->orWhere("sga_calificaciones.anio", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres)'), "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_calificaciones.calificacion", "LIKE", "%$search%")
            ->orderBy('sga_calificaciones.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CALIFICACIONES";
    }

    /**
     * Obtener las calificaciones con sus datos relacionados
     */
    public static function get_calificaciones_boletines($id_colegio, $curso_id, $asignatura_id, $periodo_id)
    {
        $array_wheres = ['sga_calificaciones.id_colegio' => $id_colegio];

        if ($curso_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones.curso_id' => $curso_id]);
        }

        if ($asignatura_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones.id_asignatura' => $asignatura_id]);
        }

        if ($periodo_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones.id_periodo' => $periodo_id]);
        }

        return Calificacion::where($array_wheres)
                        ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_calificaciones.id_periodo')
                        ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_calificaciones.curso_id')
                        ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_calificaciones.id_estudiante')
                        ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_calificaciones.id_asignatura')
                        ->select(
                            'sga_calificaciones.anio',
                            'sga_periodos.id AS id_periodo',
                            'sga_cursos.id AS curso_id',
                            'sga_estudiantes.id AS id_estudiante',
                            'sga_asignaturas.id AS id_asignatura',
                            'sga_calificaciones.calificacion',
                            'sga_calificaciones.logros',
                            'sga_calificaciones.id AS calificacion_id'
                        )
                        ->get();
    }

    public static function get_calificaciones_periodo_lectivo($id_colegio, $curso_id, $periodo_lectivo_id)
    {
        $select_raw = 'CONCAT(sga_estudiantes.apellido1," ",sga_estudiantes.apellido2," ",sga_estudiantes.nombres) AS nombre_completo_estudiante';

        $array_wheres = [['sga_calificaciones.id_colegio','=', $id_colegio]];

        if ($curso_id != null) {
            $array_wheres = array_merge($array_wheres, [['sga_calificaciones.curso_id', '=', $curso_id]]);
        }

        $array_wheres = array_merge($array_wheres, [ ['sga_calificaciones.id_asignatura', '<>', config('calificaciones.asignatura_id_para_asistencias')] ]);

        $arr_periodos = Periodo::where('periodo_lectivo_id',$periodo_lectivo_id)->get()->pluck('id')->toArray();
        //dd($array_wheres);

        return Calificacion::where($array_wheres)
                        ->whereIn('id_periodo',$arr_periodos)
                        ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_calificaciones.id_periodo')
                        ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_calificaciones.curso_id')
                        ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_calificaciones.id_estudiante')
                        ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_calificaciones.id_asignatura')
                        ->select(
                            'sga_calificaciones.anio',
                            'sga_periodos.id AS id_periodo',
                            'sga_cursos.id AS curso_id',
                            'sga_estudiantes.id AS id_estudiante',
                            'sga_asignaturas.id AS id_asignatura',
                            'sga_calificaciones.calificacion',
                            'sga_calificaciones.logros',
                            'sga_calificaciones.id AS calificacion_id'
                        )
                        ->get();
    }


    public static function get_para_boletin($periodo_id, $curso_id, $estudiante_id, $asignatura_id)
    {
        return Calificacion::where(
                                    [
                                        'id_periodo' => $periodo_id,
                                        'curso_id' => $curso_id,
                                        'id_estudiante' => $estudiante_id,
                                        'id_asignatura' => $asignatura_id
                                    ]
                                )
                            ->get()
                            ->first();
    }


    public static function get_la_calificacion($periodo_id, $curso_id, $estudiante_id, $asignatura_id)
    {
        $periodo = Periodo::find($periodo_id);

        $calificacion = Calificacion::where([
                                            'id_periodo' => $periodo_id,
                                            'curso_id' => $curso_id,
                                            'id_estudiante' => $estudiante_id,
                                            'id_asignatura' => $asignatura_id
                                        ])
                                    ->get()
                                    ->first();

        $la_calificacion = (object)[
                                        'valor' => 0,
                                        'escala_id' => 0,
                                        'escala_descripcion' => '-',
                                        'escala_abreviatura' => '-',
                                        'escala_nacional' => '-',
                                        'logros' => ''
                                    ];

        if (!is_null($calificacion)) {
            $escala = EscalaValoracion::get_escala_segun_calificacion($calificacion->calificacion, $periodo->periodo_lectivo_id);

            if (!is_null($escala)) {
                $la_calificacion = (object)[
                                            'valor' => $calificacion->calificacion,
                                            'escala_id' => $escala->id,
                                            'escala_descripcion' => $escala->nombre_escala,
                                            'escala_abreviatura' => $escala->sigla,
                                            'escala_nacional' => $escala->nombre_escala,
                                            'logros' => $calificacion->logros
                                        ];
            } else {
                $la_calificacion = (object)[
                                            'valor' => $calificacion->calificacion,
                                            'escala_id' => 0,
                                            'escala_descripcion' => '-',
                                            'escala_abreviatura' => '-',
                                            'escala_nacional' => '-',
                                            'logros' => ''
                                        ];
            }
        }

        return $la_calificacion;
    }
    
    public static function get_la_calificacion2($periodo_id, $curso_id, $estudiante_id, $asignatura_id)
    {
        return Calificacion::where([
                                    'id_periodo' => $periodo_id,
                                    'curso_id' => $curso_id,
                                    'id_estudiante' => $estudiante_id,
                                    'id_asignatura' => $asignatura_id
                                ])
                            ->get()
                            ->first();
    }


    public static function get_calificacion_promedio_asignatura_estudiante_periodos($periodos_promediar, $curso_id, $estudiante_id, $asignatura_id)
    {
        /*$array_wheres = ['sga_calificaciones.id_colegio' => $id_colegio];

        if ($curso_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones.curso_id' => $curso_id]);
        }*/

        $calificaciones = Calificacion::whereIn('id_periodo', $periodos_promediar)
                            ->where([
                                'curso_id' => $curso_id,
                                'id_estudiante' => $estudiante_id,
                                'id_asignatura' => $asignatura_id
                            ])
                            ->get();      

        $sumatoria_calificaciones = 0;
        $n = 0;
        foreach( $calificaciones AS $calificacion )
        {
            $n++;
            if ( $calificacion->nota_nivelacion() == null )
            {
                $sumatoria_calificaciones += $calificacion->calificacion;
            }else{
                $sumatoria_calificaciones += $calificacion->nota_nivelacion()->calificacion;
            }
        }

        if ( $n != 0 )
        {
            return $sumatoria_calificaciones / $n;
        }

        return 0;
    }

    public static function get_calificacion_promedio_estudiante_periodos($periodos_promediar, $curso_id, $estudiante_id)
    {
        return Calificacion::whereIn('id_periodo', $periodos_promediar)
                            ->where([
                                'curso_id' => $curso_id,
                                'id_estudiante' => $estudiante_id
                            ])
                            ->avg('calificacion');
    }


    public static function get_promedio_periodos($periodos_promediar, $curso_id, $estudiante_id, $asignatura_id)
    {
        $periodo = Periodo::find($periodos_promediar[0]);

        $calificacion = number_format(Calificacion::whereIn('id_periodo', $periodos_promediar)
            ->where([
                'curso_id' => $curso_id,
                'id_estudiante' => $estudiante_id,
                'id_asignatura' => $asignatura_id
            ])->avg('calificacion'), 2, '.', ',');

        if (!is_null($calificacion)) {
            $escala = EscalaValoracion::get_escala_segun_calificacion($calificacion, $periodo->periodo_lectivo_id);

            if (!is_null($escala) && !empty($escala)) {
                $la_calificacion = (object)[
                    'valor' => $calificacion,
                    'escala_id' => $escala->id,
                    'escala_descripcion' => $escala->nombre_escala,
                    'escala_abreviatura' => $escala->sigla,
                    'escala_nacional' => $escala->escala_nacional
                ];
            } else {
                $la_calificacion = (object)['valor' => $calificacion, 'escala_id' => 0, 'escala_descripcion' => '
            -', 'escala_abreviatura' => '
            -', 'escala_nacional' => '
            -'];
            }
        } else {
            $la_calificacion = (object)['valor' => '-', 'escala_id' => 0, 'escala_descripcion' => '
            -', 'escala_abreviatura' => '
            -', 'escala_nacional' => '
            -'];
        }

        return $la_calificacion;
    }

    public static function get_cantidad_x_matricula($colegio_id, $codigo_matricula)
    {
        return Calificacion::where(
            [
                'id_colegio' => $colegio_id,
                'codigo_matricula' => $codigo_matricula
            ]
        )
            ->count();
    }


    public static function calificaciones_promedio_por_estudiante($periodo_id)
    {

        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS nombre_completo';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS nombre_completo';
        }

        return Calificacion::leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_calificaciones.curso_id')
            ->leftJoin('sga_niveles', 'sga_niveles.id', '=', 'sga_cursos.nivel_grado')
            ->leftJoin('sga_grados', 'sga_grados.id', '=', 'sga_cursos.sga_grado_id')
            ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_calificaciones.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->where('sga_calificaciones.id_periodo', $periodo_id)
            ->select(
                DB::raw('AVG(sga_calificaciones.calificacion) AS calificacion_prom'),
                'sga_cursos.descripcion AS Curso',
                'sga_niveles.descripcion AS Nivel',
                'sga_grados.descripcion AS Grado',
                DB::raw($raw_nombre_completo),
                'sga_estudiantes.imagen',
                'sga_calificaciones.id_estudiante'
            )
            ->groupBy('sga_calificaciones.id_estudiante')
            ->get();
    }
}
