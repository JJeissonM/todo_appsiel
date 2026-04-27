<?php

namespace App\Http\Controllers\Calificaciones;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Calificaciones\EncabezadoCalificacion;
use App\Calificaciones\Services\EncabezadosCalificacionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class EncabezadoCalificacionController extends Controller
{
    protected $encabezadosCalificacionService;

    public function __construct()
    {
        //$this->middleware('auth');
        $this->encabezadosCalificacionService = app(EncabezadosCalificacionService::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Este método es llamado desde una petición AJAX para crear o actualizar un encabezado.
     * Retorna un formulario como respuesta
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $anio = (int)Input::get('anio');
        $periodoId = (int)Input::get('periodo_id');
        $cursoId = (int)Input::get('curso_id');
        $asignaturaId = (int)Input::get('asignatura_id');

        $encabezado = $this->encabezadosCalificacionService->getEncabezado(
            $anio,
            $periodoId,
            $cursoId,
            $asignaturaId,
            Input::get('columna_calificacion')
        );
        
        // Datos Para crear nuevo
        $mensaje_descripcion = '';
        $fecha = date('Y-m-d');
        $descripcion = '';
        $opcion = 'create';
        $id_encabezado_calificacion = 0;
        $creado_por = Auth::user()->email;
        $modificado_por = '';
        $peso = 0;
        $label = '';
        $titulo = '';
        if ( $encabezado != null )
        {
            // Datos Para editar
            $fecha = $encabezado->fecha;
            $descripcion = $encabezado->descripcion;
            $opcion = 'edit';
            $id_encabezado_calificacion = $encabezado->id;
            $creado_por = $encabezado->creado_por;
            $modificado_por = Auth::user()->email;
            $peso = $encabezado->peso;
            $label = $encabezado->label;
            $titulo = $encabezado->titulo;

            $mensaje_descripcion = '<span style="color:#ff4d4d; font-size: 0.9em;">Para eliminar el encabezado, deje vacía la descripción de la actividad y presione guardar.</span><br>';
        }

        $usar_encabezados_por_anio = $this->encabezadosCalificacionService->usarEncabezadosPorAnio();

        return View::make('calificaciones.encabezados_estandar.formulario', compact( 'fecha', 'descripcion', 'opcion', 'id_encabezado_calificacion', 'creado_por', 'modificado_por', 'peso', 'label', 'titulo', 'mensaje_descripcion', 'usar_encabezados_por_anio' ))->render();
    }

    /**
     * Store/Update/Delete
     */
    public function store(Request $request)
    {
        $cerrar_modal = "true";
        $scope = $this->encabezadosCalificacionService->getAtributosDePersistencia(
            (int)$request->anio,
            (int)$request->periodo_id,
            (int)$request->curso_id,
            (int)$request->asignatura_id
        );

        $encabezados = $this->encabezadosCalificacionService->getQuery(
            (int)$scope['anio'],
            (int)$request->periodo_id,
            (int)$request->curso_id,
            (int)$request->asignatura_id
        )->where('peso', '>', 0)->get();

        $sumaPesos = 0;
        
        foreach ($encabezados as $e)
        {
            $sumaPesos = $sumaPesos + $e->peso;
        }

        $data =  $request->all();
        $data = array_merge($data, $scope);
        $data['descripcion'] = trim( $request->descripcion );

        if ($this->encabezadosCalificacionService->soportaLabelYTitulo()) {
            $data['label'] = trim((string)$request->label) === '' ? null : trim((string)$request->label);
            $data['titulo'] = trim((string)$request->titulo) === '' ? null : trim((string)$request->titulo);
        } else {
            unset($data['label']);
            unset($data['titulo']);
        }

        $duplicateQuery = $this->encabezadosCalificacionService->getQuery(
            (int)$scope['anio'],
            (int)$request->periodo_id,
            (int)$request->curso_id,
            (int)$request->asignatura_id
        )
            ->where('columna_calificacion', $request->columna_calificacion);

        if ((int)$request->id_encabezado_calificacion > 0) {
            $duplicateQuery->where('id', '<>', (int)$request->id_encabezado_calificacion);
        }

        $duplicate = $duplicateQuery->exists();

        if ($duplicate) {
            return "duplicado";
        }

        switch ( $request->id_encabezado_calificacion )
        {
            case '0':

                // Se valida la sumatoria de todos los pesos
                if (($sumaPesos + (float)$request->peso) > 100)
                {
                    return "pesos";
                }

                // Crear
                EncabezadoCalificacion::create( $data );
                return "CREADO";
                    
                break;

            default:
                // Actualizar
                $registro = EncabezadoCalificacion::find( (int)$request->id_encabezado_calificacion );

                if ($registro == null ) {
                    return "pesos";
                }

                // Se valida la sumatoria de todos los pesos
                if ( ( ($sumaPesos - $registro->peso) + (float)$request->peso ) > 100 )
                {
                    return "pesos";
                }

                if ( $request->descripcion == '' )
                {
                    $registro->delete();
                    $cerrar_modal = "ELIMINADO";
                }else{
                    $registro->fill( $data );
                    $registro->save();
                    $cerrar_modal = "MODIFICADO";
                }
                    
                break;
        }

        return $cerrar_modal;
    }

    public function verificarUnicidad(Request $request)
    {
        $idExcluido = (int) $request->input('id_encabezado_calificacion', 0);
        $scope = $this->encabezadosCalificacionService->getAtributosDePersistencia(
            (int)$request->input('anio'),
            (int)$request->input('periodo_id'),
            (int)$request->input('curso_id'),
            (int)$request->input('asignatura_id')
        );

        $query = $this->encabezadosCalificacionService->getQuery(
            (int)$scope['anio'],
            (int)$request->input('periodo_id'),
            (int)$request->input('curso_id'),
            (int)$request->input('asignatura_id')
        )->where('columna_calificacion', $request->input('columna_calificacion'));

        if ($idExcluido > 0) {
            $query->where('id', '<>', $idExcluido);
        }

        $duplicate = $query->exists();

        return response()->json(['duplicate' => $duplicate]);
    }
}
