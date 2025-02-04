<?php 

namespace App\Matriculas\Services;

use App\Calificaciones\Periodo;
use App\Core\Colegio;
use App\Core\Tercero;
use App\Matriculas\Estudiante;
use App\Matriculas\Matricula;
use App\Matriculas\Responsableestudiante;
use App\Matriculas\TiposAspecto;
use App\Ventas\Cliente;
use App\Ventas\VtasMovimiento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class ObservadorEstudianteService
{
    public function vista_preliminar($id_estudiante, $vista = null)
    {
        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()->first();

        $estudiante = Estudiante::get_datos_basicos( $id_estudiante );
        
        switch ( config('matriculas.formato_impresion_observador') ) {
            case '1':
                $vista_formato = 'matriculas.estudiantes.observador.formatos.estandar';
                break;

            case '2':
                $vista_formato = 'matriculas.estudiantes.observador.formatos.tipo_historial';
                break;

            case '3':
                $vista_formato = 'matriculas.estudiantes.observador.formatos.marca_agua';
                break;

            default:
                break;
        }

        $tam_hoja = 'letter';
        
        $curso_label = 'Sin matricula registrada';
        $anio_lectivo_label = '';

        //$matricula = Matricula::find((int)Input::get('matricula_id'));
        $matricula = $this->get_matricula_a_mostrar((int)Input::get('matricula_id'), $estudiante);
        
        if($matricula != null)
        {
            $anio_lectivo_label = $matricula->periodo_lectivo->descripcion;

            $curso_label = $matricula->curso->descripcion;

            $periodos = Periodo::where([
                                ['periodo_lectivo_id', '=', $matricula->periodo_lectivo_id]
                            ])
                            ->orderBy('periodo_lectivo_id')
                            ->orderBy('numero')
                            ->get();
        }else{
            $periodos = Periodo::get_todos_periodo_lectivo_actual();
        }
        
        $anio_matricula = $this->get_anio_matricula((int)Input::get('matricula_id'), $estudiante);   

        $tipos_aspectos = TiposAspecto::all();

        $matricula_a_mostrar = $this->get_matricula_a_mostrar((int)Input::get('matricula_id'), $estudiante);
        
        return View::make( $vista_formato, compact( 'colegio', 'estudiante', 'vista','tam_hoja', 'curso_label', 'anio_lectivo_label', 'tipos_aspectos', 'anio_matricula', 'periodos', 'matricula_a_mostrar' ) )->render();
    }
    
    public function get_anio_matricula($matricula_id, $estudiante)
    {
        $anio_matricula = date('Y');

        $matricula_a_mostrar = $this->get_matricula_a_mostrar($matricula_id, $estudiante);

        if ($matricula_a_mostrar != null) {
            $anio_matricula = $matricula_a_mostrar->periodo_lectivo->get_anio();
        } 

        return $anio_matricula;
    }
    
    public function get_matricula_a_mostrar($matricula_id, $estudiante)
    {
        $matricula = Matricula::find($matricula_id);

        if ($matricula == null) {            
            if($estudiante->matriculas->last() != null)
            {
                $matricula = $estudiante->matriculas->last();
            }
        }
        
        return $matricula;
    }
}