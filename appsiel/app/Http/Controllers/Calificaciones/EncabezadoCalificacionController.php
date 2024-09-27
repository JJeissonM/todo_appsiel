<?php

namespace App\Http\Controllers\Calificaciones;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Calificaciones\EncabezadoCalificacion;
use Collective\Html\FormFacade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class EncabezadoCalificacionController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
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
        // Verificar si ya hay encabezado ingresado para la columna enviada
        $encabezado = EncabezadoCalificacion::where([
                                'columna_calificacion' => Input::get('columna_calificacion'),
                                'periodo_id' => Input::get('periodo_id'),
                                'curso_id' => Input::get('curso_id'),
                                'asignatura_id' => Input::get('asignatura_id')
                            ])
                            ->get()
                            ->first();
        
        // Datos Para crear nuevo
        $mensaje_descripcion = '';
        $fecha = date('Y-m-d');
        $descripcion = '';
        $opcion = 'create';
        $id_encabezado_calificacion = 0;
        $creado_por = Auth::user()->email;
        $modificado_por = '';
        $peso = 0;
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

            $mensaje_descripcion = '<span style="color:#ff4d4d; font-size: 0.9em;">Para eliminar el encabezado, deje vacía la descripción de la actividad y presione guardar.</span><br>';
        }

        return View::make('calificaciones.encabezados_estandar.formulario', compact( 'fecha', 'descripcion', 'opcion', 'id_encabezado_calificacion', 'creado_por', 'modificado_por', 'peso', 'mensaje_descripcion' ))->render();
    }

    /**
     * Store/Update/Delete
     */
    public function store(Request $request)
    {
        $cerrar_modal = "true";

        $encabezados = EncabezadoCalificacion::where([
                                                        ['curso_id', $request->curso_id],
                                                        ['asignatura_id', $request->asignatura_id],
                                                        ['periodo_id', $request->periodo_id],
                                                        ['peso', '>', 0 ]
                                                    ])
                                                ->get();

        $sumaPesos = 0;
        
        foreach ($encabezados as $e)
        {
            $sumaPesos = $sumaPesos + $e->peso;
        }

        $data =  $request->all();
        $data['descripcion'] = trim( $request->descripcion );

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
}
