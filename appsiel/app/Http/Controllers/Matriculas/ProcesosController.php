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

use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Curso;
use App\Matriculas\Matricula;

use App\Calificaciones\Periodo;
use App\Calificaciones\Calificacion;
use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\Asignatura;

class ProcesosController extends ModeloController
{
    public function crear_matriculas_masivas( Request $request )
    {
        $periodo_lectivo = PeriodoLectivo::find( $request->periodo_lectivo_id );

        $matriculas = Matricula::where([
                                        ['periodo_lectivo_id','=',$request->periodo_lectivo_id],
                                        ['curso_id','=',$request->curso_id],
                                        ['estado','=','Activo']
                                    ])
                                ->get();

        $vista = View::make('matriculas.procesos.crear_matriculas_masivas_lista_estudiantes_promover',compact('matriculas'))->render();

        return $vista;
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
