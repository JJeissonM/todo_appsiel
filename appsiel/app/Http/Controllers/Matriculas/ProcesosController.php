<?php

namespace App\Http\Controllers\Matriculas;

use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;

use Input;
use DB;
use PDF;
use Auth;
use Storage;
use View;
use File;

use App\Http\Requests;
use App\Sistema\SecuenciaCodigo;

use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Curso;
use App\Matriculas\Matricula;

use App\Calificaciones\Periodo;
use App\Calificaciones\Calificacion;
use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\Asignatura;
use App\Calificaciones\NotaNivelacion;

use App\Calificaciones\EscalaValoracion;

class ProcesosController extends ModeloController
{
    public function promocion_academica_cargar_listado( Request $request )
    {
        $periodo_lectivo = PeriodoLectivo::find( $request->periodo_lectivo_id );

        $listado = Matricula::where([
                                        ['periodo_lectivo_id','=',$request->periodo_lectivo_id],
                                        ['curso_id','=',$request->curso_id],
                                        ['estado','=','Activo']
                                    ])
                                ->get();

        $matriculas = collect([]);
        foreach ( $listado as $matricula )
        {
            $matricula->periodo_final = $matricula->periodo_lectivo->periodo_final_del_anio_lectivo();
            $matricula->promedio_final = Calificacion::get_calificacion_promedio_estudiante_periodos( [$matricula->periodo_final->id ], $matricula->curso_id, $matricula->id_estudiante);
            $matricula->cantidad_nivelaciones = NotaNivelacion::where([
                                                        [ 'estudiante_id', '=', $matricula->id_estudiante],
                                                        [ 'periodo_id', '=', $matricula->periodo_final->id ]
                                                    ])->count();

            $matriculas->push( $matricula );
        }

        $opciones_cursos = Curso::opciones_campo_select();
        $cantidad_estudiantes = $listado->count();

        $escala_valoracion_minima = EscalaValoracion::where( 'periodo_lectivo_id', $request->periodo_lectivo_id )
                                                        ->orderBy('calificacion_minima','ASC')
                                                        ->first();
        $tope_escala_valoracion_minima = 0;
        if ( !is_null( $escala_valoracion_minima ) )
        {
            $tope_escala_valoracion_minima = $escala_valoracion_minima->calificacion_maxima;
        }

        $vista = View::make('matriculas.procesos.promocion_academica_lista_estudiantes_promover',compact( 'matriculas', 'opciones_cursos', 'cantidad_estudiantes', 'tope_escala_valoracion_minima' ) )->render();

        return $vista;
    }

    public function promocion_academica_generar( Request $request )
    {
        $lineas_estudiantes = json_decode($request->lineas_estudiantes);

        $nuevo_curso = Curso::find( (int)$request->nuevo_curso_id );
        
        $cantidad_registros = count($lineas_estudiantes);
        $nuevas_matriculas = 0;
        for ($i=0; $i < $cantidad_registros; $i++) 
        {
            if ( (int)$lineas_estudiantes[$i]->checkbox == 0 )
            {
                continue;
            }

            $requisitos = "on-on-on-on-on-on";
            $matricula = Matricula::find( (int)$lineas_estudiantes[$i]->matricula_id );

            $nuevo_codigo = SecuenciaCodigo::get_codigo('matriculas', (object)['grado_id' => $nuevo_curso->grado->id ]);

            $linea_datos = [ 'periodo_lectivo_id' => (int)$request->nuevo_periodo_lectivo_id ] +
                            [ 'id_colegio' => $matricula->id_colegio ] +
                            [ 'codigo' => $nuevo_codigo ] +
                            [ 'fecha_matricula' => $request->fecha_matricula ] +
                            [ 'id_estudiante' => $matricula->id_estudiante ] +
                            [ 'curso_id' => (int)$request->nuevo_curso_id ] +
                            [ 'requisitos' => $requisitos  ] +
                            [ 'estado' => 'Activo' ];

            Matricula::create( $linea_datos );
            SecuenciaCodigo::incrementar_consecutivo('matriculas');

            $matricula->estado = 'Inactivo';
            $matricula->save();

            $nuevas_matriculas++;

        } // Fin por cada registro

        return redirect( 'web?id=1&id_modelo=19&&search=' . date('Y-m-d') )->with( 'flash_message', 'Promoción académica exitosa. Se crearon ' . $nuevas_matriculas . ' nuevas matrículas.' );
    }


    public function cambio_de_curso_cargar_listado( Request $request )
    {
        $periodo_lectivo = PeriodoLectivo::find( $request->periodo_lectivo_id );

        $matriculas = Matricula::where([
                                        ['periodo_lectivo_id','=',$request->periodo_lectivo_id],
                                        ['curso_id','=',$request->curso_id],
                                        ['estado','=','Activo']
                                    ])
                                ->get();
        
        $curso_actual = Curso::find( $request->curso_id );

        $opciones_cursos = Curso::select_curso_del_grado( $curso_actual->sga_grado_id, $request->curso_id );

        $cantidad_estudiantes = $matriculas->count();

        $vista = View::make('matriculas.procesos.cambio_de_curso_lista_estudiantes_cambiar',compact( 'matriculas', 'opciones_cursos', 'cantidad_estudiantes' ) )->render();

        return $vista;
    }

    public function cambio_de_curso_generar( Request $request )
    {
        $lineas_estudiantes = json_decode($request->lineas_estudiantes);

        $nuevo_curso = Curso::find( (int)$request->nuevo_curso_id );

        $cantidad_registros = count($lineas_estudiantes);
        $cantidad_estudiantes_trasladados = 0;
        for ($i=0; $i < $cantidad_registros; $i++) 
        {
            if ( (int)$lineas_estudiantes[$i]->checkbox == 0 )
            {
                continue;
            }

            $matricula = Matricula::find( (int)$lineas_estudiantes[$i]->matricula_id );

            $this->cambiar_de_curso( $matricula->id_estudiante, $matricula->curso_id, (int)$request->nuevo_curso_id );

            $cantidad_estudiantes_trasladados++;

        } // Fin por cada registro

        return redirect( 'web?id=1&id_modelo=19&&search=' . $nuevo_curso->descripcion )->with( 'flash_message', 'Cambio de curso exitoso. Se trasladaron ' . $cantidad_estudiantes_trasladados . ' estudiantes.' );
    }


    /* 
        PENDIENTE POR TERMINAR
    */
    public function cambiar_de_curso( $estudiante_id, $curso_actual_id, $curso_nuevo_id )
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"sga_control_disciplinario",
                                    "llave_foranea":"estudiante_id",
                                    "mensaje":"Tiene registros de control disciplinario."
                                },
                            "1":{
                                    "tabla":"sga_asistencia_clases",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Tiene registros de asistencia a clases."
                                },
                            "2":{
                                    "tabla":"sga_calificaciones",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Ya Tiene calificaciones."  
                                },
                            "3":{
                                    "tabla":"sga_calificaciones_auxiliares",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Ya Tiene calificaciones."
                                },
                            "4":{
                                    "tabla":"sga_matriculas",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Tiene matrículas asociadas."
                                },
                            "5":{
                                    "tabla":"sga_observaciones_boletines",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Tiene registros de observaciones en informes."
                                },
                            "6":{
                                    "tabla":"sga_preinformes_academicos",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Tiene registros en preinformes académicos."   
                                },
                            "7":{
                                    "tabla":"sga_estudiante_reconocimientos",
                                    "llave_foranea":"estudiante_id",
                                    "mensaje":"Tiene registros en preinformes académicos."   
                                },
                            "8":{
                                    "tabla":"sga_notas_nivelaciones",
                                    "llave_foranea":"estudiante_id",
                                    "mensaje":"Tiene registros en preinformes académicos."   
                                }
                        }';

        $tablas = json_decode( $tablas_relacionadas );
        foreach( $tablas AS $una_tabla )
        { 
            // UPDATE `sga_asistencia_clases` SET curso_id = 12 WHERE `id_estudiante` = 249 AND `curso_id` = 11
            $registros = DB::table( $una_tabla->tabla )->where( [
                                                                [ $una_tabla->llave_foranea, '=', $estudiante_id],
                                                                [ 'curso_id', '=', $curso_actual_id]
                                                            ] )
                                                    ->update(['curso_id' => $curso_nuevo_id]);
        }

        
    }

}
