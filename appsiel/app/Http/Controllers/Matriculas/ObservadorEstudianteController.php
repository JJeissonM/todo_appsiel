<?php

namespace App\Http\Controllers\Matriculas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Http\Controllers\Core\TransaccionController;

use Auth;
use DB;
use Input;
use View;

// Modelos
use App\Matriculas\CatalogoAspecto;
use App\Matriculas\TiposAspecto;
use App\Matriculas\AspectosObservador;
use App\Matriculas\NovedadesObservador;
use App\Matriculas\FodaEstudiante;
use App\Matriculas\Estudiante;
use App\Matriculas\Matricula;
use App\Matriculas\ControlDisciplinario;

use App\Calificaciones\Periodo;
use App\Core\Colegio;

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

        $view_pdf = $this->vista_preliminar($id,'show');

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, Estudiante::get_nombre_completo( $id ) );

        return view( 'matriculas.estudiantes.observador.show',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id') );
    }

    public function imprimir_observador($id_estudiante)
    {
        $view = $this->vista_preliminar($id_estudiante);
        $tam_hoja = 'Letter';
        $orientacion='portrait';

        //crear PDF
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($view)->setPaper($tam_hoja,$orientacion);
        return $pdf->stream('observador.pdf');
    }

    public static function vista_preliminar($id_estudiante, $vista = null)
    {
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()->first();

        $estudiante = Estudiante::get_datos_basicos( $id_estudiante );

        return View::make('matriculas.estudiantes.observador.vista_preliminar', compact( 'colegio', 'estudiante' ) )->render();

    }


    // FORMULARIOS PARA ACTUALIZAR  ASPECTOS
    public function valorar_aspectos($id_estudiante)
    {
        $this->set_variables_globales();

        $estudiante = Estudiante::find($id_estudiante);
        $tipos_aspectos = TiposAspecto::all();

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, 'Valoración de aspectos: '.Estudiante::get_nombre_completo( $estudiante->id ) );

        return view('matriculas.estudiantes.observador.valorar_aspectos',compact('tipos_aspectos','estudiante','miga_pan'));
    }

    // PROCEDIMIENTO ALMACENAR ASPECTOS
    public function guardar_valoracion_aspectos(Request $request)
    {
        $estudiante = Estudiante::find($request->id_estudiante);
        $tipos_aspectos = TiposAspecto::all();
        
        $aspectos = CatalogoAspecto::all();
        
        $cantidad = count( $aspectos->toArray() );
        
        for($i=0; $i < $cantidad; $i++)
        {
            $aspecto_estudiante=AspectosObservador::where('id_aspecto','=',$request->input('id_aspecto.'.$i))->where('id_estudiante','=',$request->id_estudiante)->where('fecha_valoracion','like',date('Y').'%')->count();
            
            if($aspecto_estudiante==0){
                DB::insert('insert into sga_aspectos_observador 
                        (id_estudiante,id_aspecto,fecha_valoracion,valoracion_periodo1,valoracion_periodo2,valoracion_periodo3,valoracion_periodo4) values (?,?,?,?,?,?,?)',
                        [$request->id_estudiante,$request->input('id_aspecto.'.$i),$request->fecha_valoracion,$request->input('valoracion_periodo1.'.$i),$request->input('valoracion_periodo2.'.$i),$request->input('valoracion_periodo3.'.$i),$request->input('valoracion_periodo4.'.$i)]);

            }else{
                DB::table('sga_aspectos_observador')->where(['id'=>$request->input('aspecto_estudiante_id.'.$i)])->update(['valoracion_periodo1'=>$request->input('valoracion_periodo1.'.$i),'valoracion_periodo2'=>$request->input('valoracion_periodo2.'.$i),
                                    'valoracion_periodo3'=>$request->input('valoracion_periodo3.'.$i),'valoracion_periodo4'=>$request->input('valoracion_periodo4.'.$i)]);
            }
        }

        return redirect('matriculas/estudiantes/observador/valorar_aspectos/'.$estudiante->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Registros actualizados correctamente.');
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




