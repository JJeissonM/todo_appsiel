<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

use App\Calificaciones\EscalaValoracion;
use App\Calificaciones\Periodo;
use App\Calificaciones\NotaNivelacion;

use App\Calificaciones\Asignatura;
use App\Matriculas\Curso;
use App\Matriculas\Matricula;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CalificacionDesempenio extends Model
{
    protected $table = 'sga_calificaciones_desempenio';

    protected $fillable = [ 'matricula_id', 'periodo_id', 'curso_id', 'asignatura_id', 'logro_id', 'escala_valoracion_id', 'calificacion', 'anotacion', 'creado_por', 'modificado_por'];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class,'matricula_id');
    }
    
    public function periodo()
    {
        return $this->belongsTo(Periodo::class,'periodo_id');
    }
    
    public function curso()
    {
        return $this->belongsTo(Curso::class,'curso_id');
    }
    
    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class,'asignatura_id');
    }
    
    public function logro()
    {
        return $this->belongsTo(Logro::class,'logro_id');
    }
    
    public function escala_valoracion()
    {
        return $this->belongsTo(EscalaValoracion::class,'escala_valoracion_id');
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

    public static function sqlString($colegio_id, $curso_id, $asignatura_id, $periodo_id = null, $search)
    {
        $array_wheres = [];

        if ($curso_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones_desempenio.curso_id' => $curso_id]);
        }

        if ($asignatura_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones_desempenio.asignatura_id' => $asignatura_id]);
        }

        if ($periodo_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones_desempenio.periodo_id' => $periodo_id]);
        }

        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS ESTUDIANTE';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS ESTUDIANTE';
        }

        $string = CalificacionDesempenio::where($array_wheres)
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_calificaciones_desempenio.periodo_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_calificaciones_desempenio.curso_id')
            ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_calificaciones_desempenio.estudiante_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_calificaciones_desempenio.asignatura_id')
            ->select(
                'sga_calificaciones_desempenio.anio AS AÑO',
                'sga_periodos.descripcion AS PERÍODO',
                'sga_cursos.descripcion AS CURSO',
                DB::raw($raw_nombre_completo),
                'sga_asignaturas.descripcion AS ASIGNATURA',
                'sga_calificaciones_desempenio.calificacion AS CALIFICACIÓN'
            )->orWhere("sga_calificaciones_desempenio.anio", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres)'), "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_calificaciones_desempenio.calificacion", "LIKE", "%$search%")
            ->orderBy('sga_calificaciones_desempenio.created_at', 'DESC')
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
    public static function get_calificaciones_boletines($colegio_id, $curso_id, $asignatura_id, $periodo_id)
    {
        $array_wheres = ['sga_calificaciones_desempenio.colegio_id' => $colegio_id];

        if ($curso_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones_desempenio.curso_id' => $curso_id]);
        }

        if ($asignatura_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones_desempenio.asignatura_id' => $asignatura_id]);
        }

        if ($periodo_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones_desempenio.periodo_id' => $periodo_id]);
        }

        return CalificacionDesempenio::where($array_wheres)
                        ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_calificaciones_desempenio.periodo_id')
                        ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_calificaciones_desempenio.curso_id')
                        ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_calificaciones_desempenio.estudiante_id')
                        ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_calificaciones_desempenio.asignatura_id')
                        ->select(
                            'sga_calificaciones_desempenio.anio',
                            'sga_periodos.id AS periodo_id',
                            'sga_cursos.id AS curso_id',
                            'sga_estudiantes.id AS estudiante_id',
                            'sga_asignaturas.id AS asignatura_id',
                            'sga_calificaciones_desempenio.calificacion',
                            'sga_calificaciones_desempenio.logros',
                            'sga_calificaciones_desempenio.id AS calificacion_id'
                        )
                        ->get();
    }

    public static function get_calificaciones_periodo_lectivo($colegio_id, $curso_id, $periodo_lectivo_id)
    {
        $select_raw = 'CONCAT(sga_estudiantes.apellido1," ",sga_estudiantes.apellido2," ",sga_estudiantes.nombres) AS nombre_completo_estudiante';

        $array_wheres = [['sga_calificaciones_desempenio.colegio_id','=', $colegio_id]];

        if ($curso_id != null) {
            $array_wheres = array_merge($array_wheres, [['sga_calificaciones_desempenio.curso_id', '=', $curso_id]]);
        }

        $array_wheres = array_merge($array_wheres, [ ['sga_calificaciones_desempenio.asignatura_id', '<>', config('calificaciones.asignatura_para_asistencias_id')] ]);

        $arr_periodos = Periodo::where('periodo_lectivo_id',$periodo_lectivo_id)->get()->pluck('id')->toArray();
        //dd($array_wheres);

        return CalificacionDesempenio::where($array_wheres)
                        ->whereIn('periodo_id',$arr_periodos)
                        ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_calificaciones_desempenio.periodo_id')
                        ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_calificaciones_desempenio.curso_id')
                        ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_calificaciones_desempenio.estudiante_id')
                        ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_calificaciones_desempenio.asignatura_id')
                        ->select(
                            'sga_calificaciones_desempenio.anio',
                            'sga_periodos.id AS periodo_id',
                            'sga_cursos.id AS curso_id',
                            'sga_estudiantes.id AS estudiante_id',
                            'sga_asignaturas.id AS asignatura_id',
                            'sga_calificaciones_desempenio.calificacion',
                            'sga_calificaciones_desempenio.logros',
                            'sga_calificaciones_desempenio.id AS calificacion_id'
                        )
                        ->get();
    }


    public static function get_para_boletin($periodo_id, $curso_id, $estudiante_id, $asignatura_id)
    {
        return CalificacionDesempenio::where(
                                    [
                                        'periodo_id' => $periodo_id,
                                        'curso_id' => $curso_id,
                                        'estudiante_id' => $estudiante_id,
                                        'asignatura_id' => $asignatura_id
                                    ]
                                )
                            ->get()
                            ->first();
    }


    public static function get_la_calificacion($periodo_id, $curso_id, $estudiante_id, $asignatura_id)
    {
        $periodo = Periodo::find($periodo_id);

        $calificacion = CalificacionDesempenio::where([
                                            'periodo_id' => $periodo_id,
                                            'curso_id' => $curso_id,
                                            'estudiante_id' => $estudiante_id,
                                            'asignatura_id' => $asignatura_id
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
        return CalificacionDesempenio::where([
                                    'periodo_id' => $periodo_id,
                                    'curso_id' => $curso_id,
                                    'estudiante_id' => $estudiante_id,
                                    'asignatura_id' => $asignatura_id
                                ])
                            ->get()
                            ->first();
    }


    public static function get_calificacion_promedio_asignatura_estudiante_periodos($periodos_promediar, $curso_id, $estudiante_id, $asignatura_id)
    {
        /*$array_wheres = ['sga_calificaciones_desempenio.colegio_id' => $colegio_id];

        if ($curso_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones_desempenio.curso_id' => $curso_id]);
        }*/

        $calificaciones = CalificacionDesempenio::whereIn('periodo_id', $periodos_promediar)
                            ->where([
                                'curso_id' => $curso_id,
                                'estudiante_id' => $estudiante_id,
                                'asignatura_id' => $asignatura_id
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
        return CalificacionDesempenio::whereIn('periodo_id', $periodos_promediar)
                            ->where([
                                'curso_id' => $curso_id,
                                'estudiante_id' => $estudiante_id
                            ])
                            ->avg('calificacion');
    }


    public static function get_promedio_periodos($periodos_promediar, $curso_id, $estudiante_id, $asignatura_id)
    {
        $periodo = Periodo::find($periodos_promediar[0]);

        $calificacion = number_format(CalificacionDesempenio::whereIn('periodo_id', $periodos_promediar)
            ->where([
                'curso_id' => $curso_id,
                'estudiante_id' => $estudiante_id,
                'asignatura_id' => $asignatura_id
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
        return CalificacionDesempenio::where(
            [
                'colegio_id' => $colegio_id,
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

        return CalificacionDesempenio::leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_calificaciones_desempenio.curso_id')
            ->leftJoin('sga_niveles', 'sga_niveles.id', '=', 'sga_cursos.nivel_grado')
            ->leftJoin('sga_grados', 'sga_grados.id', '=', 'sga_cursos.sga_grado_id')
            ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_calificaciones_desempenio.estudiante_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->where('sga_calificaciones_desempenio.periodo_id', $periodo_id)
            ->select(
                DB::raw('AVG(sga_calificaciones_desempenio.calificacion) AS calificacion_prom'),
                'sga_cursos.descripcion AS Curso',
                'sga_niveles.descripcion AS Nivel',
                'sga_grados.descripcion AS Grado',
                DB::raw($raw_nombre_completo),
                'sga_estudiantes.imagen',
                'sga_calificaciones_desempenio.estudiante_id'
            )
            ->groupBy('sga_calificaciones_desempenio.estudiante_id')
            ->get();
    }
}
