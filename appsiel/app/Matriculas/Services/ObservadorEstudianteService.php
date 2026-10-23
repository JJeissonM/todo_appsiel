<?php 

namespace App\Matriculas\Services;

use App\Calificaciones\Periodo;
use App\Core\Colegio;

use App\Matriculas\Estudiante;
use App\Matriculas\Matricula;

use App\Matriculas\TiposAspecto;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Input;

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

        $escudo_pdf = null;
        $foto_estudiante_pdf = null;
        if ($vista == 'print' && (int)config('matriculas.formato_impresion_observador') === 1) {
            $escudo_pdf = $this->imagen_local_pdf('escudos', $colegio->imagen);
            $foto_estudiante_pdf = $this->imagen_local_pdf('fotos_terceros', $estudiante->imagen);
        }
        
        return View::make( $vista_formato, compact( 'colegio', 'estudiante', 'vista','tam_hoja', 'curso_label', 'anio_lectivo_label', 'tipos_aspectos', 'anio_matricula', 'periodos', 'matricula_a_mostrar', 'escudo_pdf', 'foto_estudiante_pdf' ) )->render();
    }

    protected function imagen_local_pdf($carpeta, $nombre)
    {
        if (empty($nombre)) {
            return null;
        }
        $ruta = storage_path('app/'.$carpeta.'/'.basename($nombre));
        if (!is_file($ruta) || !is_readable($ruta)) {
            return null;
        }
        $imagen = @getimagesize($ruta);
        if (!$imagen || !in_array($imagen['mime'], ['image/png', 'image/jpeg', 'image/gif', 'image/webp'])) {
            return null;
        }
        return 'data:'.$imagen['mime'].';base64,'.base64_encode(file_get_contents($ruta));
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
