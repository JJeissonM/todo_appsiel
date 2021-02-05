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

        $vista = View::make('matriculas.procesos.promocion_academica_lista_estudiantes_promover',compact('matriculas','opciones_cursos','cantidad_estudiantes'))->render();

        return $vista;
    }

    public function promocion_academica_generar( Request $request )
    {
        $lineas_estudiantes = json_decode($request->lineas_estudiantes);

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

            $nuevo_curso = Curso::find( (int)$request->nuevo_curso_id );
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


    /* 
        PENDIENTE POR TERMINAR
    */
    public function form_cambiar_de_curso()
    {

        $miga_pan = [
                        ['url' => $this->aplicacion->app.'?id='.Input::get('id'), 'etiqueta'=> $this->aplicacion->descripcion ],
                        ['url' => 'NO','etiqueta'=> 'Proceso: Generar promedio de notas periodo final']
                    ];

        $periodos_lectivos = PeriodoLectivo::get_array_activos();

        return view( 'calificaciones.procesos.generar_promedio_notas_periodo_final', compact( 'miga_pan', 'periodos_lectivos') );
    }


    /* 
        PENDIENTE POR TERMINAR
    */
    public function cambiar_de_curso( $estudiante_id, $curso_actual_id, $curso_futuro_id )
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
                                    "mensaje":"Tiene registros de observaciones en boletines."
                                },
                            "6":{
                                    "tabla":"sga_preinformes_academicos",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Tiene registros en preinformes académicos."   
                                }
                        }';

        $tablas = json_decode( $tablas_relacionadas );
        foreach($tablas AS $una_tabla)
        { 
            // UPDATE `sga_asistencia_clases` SET curso_id = 12 WHERE `id_estudiante` = 249 AND `curso_id` = 11
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $id )->get();

            if ( !empty($registro) )
            {
                return $una_tabla->mensaje;
            }
        }

        
    }

}
