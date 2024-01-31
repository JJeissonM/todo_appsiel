<?php

namespace App\Http\Controllers\Matriculas;

use Illuminate\Http\Request;

use App\Http\Controllers\Core\TransaccionController;

// Modelos
use App\Matriculas\CatalogoAspecto;
use App\Matriculas\TiposAspecto;
use App\Matriculas\AspectosObservador;
use App\Matriculas\NovedadesObservador;
use App\Matriculas\Estudiante;

use App\Calificaciones\Periodo;
use App\Core\Colegio;
use App\Matriculas\Curso;
use App\Matriculas\Matricula;
use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Services\EstudiantesService;
use App\Matriculas\Services\ObservadorEstudianteService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class ObservadorEstudianteController extends TransaccionController
{

    /**
        ** Vista previa del observador del estudiante.
        ** $id = ID del estudiante
    **/
    public function show($id)
    {
        $this->set_variables_globales();

        $reg_anterior = Estudiante::where('id', '<', $id)->max('id');
        $reg_siguiente = Estudiante::where('id', '>', $id)->min('id');

        $observador_serv = new ObservadorEstudianteService();
        $view_pdf = $observador_serv->vista_preliminar($id,'show');

        $estudiante_serv = new EstudiantesService();

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, $estudiante_serv->get_nombre_completo( $id ) );

        $matriculas = $estudiante_serv->historial_matriculas($id);

        $vec_matriculas[''] = '';
        foreach ($matriculas as $matricula){
            $vec_matriculas[$matricula->id] = $matricula->curso->descripcion . ' (' . $matricula->periodo_lectivo->get_anio() . ')';
        }

        $estudiante = Estudiante::get_datos_basicos( $id );
        $matricula_a_mostrar = $observador_serv->get_matricula_a_mostrar((int)Input::get('matricula_id'), $estudiante);

        return view( 'matriculas.estudiantes.observador.show', compact( 'reg_anterior', 'reg_siguiente','miga_pan', 'view_pdf', 'id', 'vec_matriculas', 'matricula_a_mostrar') );
    }

    public function imprimir_observador($id_estudiante)
    {
        $view = (new ObservadorEstudianteService())->vista_preliminar($id_estudiante, 'print');
        $tam_hoja = 'Letter';
        $orientacion = 'portrait';

        //crear PDF
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($view)->setPaper($tam_hoja,$orientacion);
        return $pdf->stream('observador.pdf');
    }
    
    // FORMULARIOS PARA ACTUALIZAR ASPECTOS
    public function valorar_aspectos($id_estudiante)
    {
        $this->set_variables_globales();

        $estudiante = Estudiante::find($id_estudiante);
        $tipos_aspectos = TiposAspecto::all();

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, 'Valoración de aspectos: '.Estudiante::get_nombre_completo( $estudiante->id ) );
        
        $observador_serv = new ObservadorEstudianteService();
        $anio_matricula = $observador_serv->get_anio_matricula((int)Input::get('matricula_id'), $estudiante);
        
        $matricula_a_mostrar = $observador_serv->get_matricula_a_mostrar((int)Input::get('matricula_id'), $estudiante);

        $observacion_general = '';
        if ($matricula_a_mostrar != null) {
            $observacion_general = $matricula_a_mostrar->get_observacion_general();
        }        

        return view('matriculas.estudiantes.observador.valorar_aspectos',compact('tipos_aspectos','estudiante','miga_pan', 'observacion_general', 'anio_matricula', 'matricula_a_mostrar'));
    }

    // PROCEDIMIENTO ALMACENAR ASPECTOS
    public function guardar_valoracion_aspectos(Request $request)
    {
        $valoraciones_linea_aspecto = json_decode($request->valoraciones_linea_aspecto);

        foreach ($valoraciones_linea_aspecto as $key => $linea) {

            if ((int)$linea->aspecto_estudiante_id == 0) {
                $data = [
                        'id_estudiante' => $request->id_estudiante,
                        'id_aspecto' => $linea->id_aspecto,
                        'fecha_valoracion' => $request->fecha_valoracion,
                        'valoracion_periodo1' => $linea->valoracion_periodo1,
                        'valoracion_periodo2' => $linea->valoracion_periodo2,
                        'valoracion_periodo3' => $linea->valoracion_periodo3,
                        'valoracion_periodo4' => $linea->valoracion_periodo4
                    ];
                
                $aspecto = AspectosObservador::create($data);

            }else{
                $aspecto = AspectosObservador::find((int)$linea->aspecto_estudiante_id);
                $aspecto->fill([
                    'valoracion_periodo1' => $linea->valoracion_periodo1,
                    'valoracion_periodo2' => $linea->valoracion_periodo2,
                    'valoracion_periodo3' => $linea->valoracion_periodo3,
                    'valoracion_periodo4' => $linea->valoracion_periodo4
                ]);
                $aspecto->save();
            }
        }

        $matricula = Matricula::find($request->matricula_id);
        $matricula->observacion_general = $request->observacion_general;
        $matricula->save();

        return redirect('academico_docente/valorar_aspectos_observador/' . $request->id_estudiante . '?id=' . $request->url_id . '&curso_id=' . $request->curso_id . '&asignatura_id=' . $request->asignatura_id)->with('flash_message', 'Registros actualizados correctamente.');
    }

    public function eliminar_novedad($novedad_id)
    {
        $novedad =  NovedadesObservador::find($novedad_id);
        $periodo = Periodo::find($novedad->id_periodo);

        if( $periodo->cerrado == 1 )
        {
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','La novedad NO puede ser eliminada. El periodo está cerrado.');
        }

        //Borrar novedad
        $novedad->delete();

        return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('flash_message','Novedad ELIMINADA correctamente.');
    }
}




