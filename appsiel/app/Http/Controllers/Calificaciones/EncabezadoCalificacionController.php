<?php

namespace App\Http\Controllers\Calificaciones;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use Form;
use Auth;

use App\Calificaciones\EncabezadoCalificacion;

class EncabezadoCalificacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
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
        $encabezado = EncabezadoCalificacion::where( [ 'columna_calificacion' => Input::get('columna_calificacion'), 'periodo_id' => Input::get('periodo_id'), 'curso_id' => Input::get('curso_id'), 'asignatura_id' => Input::get('asignatura_id')] )->get()->first();

        if ( isset($encabezado->id) ) {
            // Para editar
            $fecha = $encabezado->fecha;
            $descripcion = $encabezado->descripcion;
            $opcion = 'edit';
            $id = $encabezado->id;
            $creado_por = $encabezado->creado_por;
            $modificado_por = Auth::user()->email;
        }else{
            // Para crear nuevo
            $fecha = date('Y-m-d');
            $descripcion = '';
            $opcion = 'create';
            $id = 0;
            $creado_por = Auth::user()->email;
            $modificado_por = '';
        }            

        $formulario = '<h4>Actividad para la calificación '.Input::get('columna_calificacion').'</h4>'.Form::open( ['url'=>url('calificaciones_encabezados?id=5'), 'id' => 'formulario_modal' ] ).'
                          <div class="form-group">
                            <label for="fecha">Fecha actividad:</label>
                            <input name="fecha" type="date" class="form-control" id="fecha" value="'.$fecha.'" required="required">
                          </div>
                          <div class="form-group">
                            <label for="descripcion">Descripción actividad:</label>
                            <textarea name="descripcion" class="form-control" id="descripcion" rows="5" required="required">'.$descripcion.'</textarea>
                          </div>
                          <input type="hidden" name="opcion" value="'.$opcion.'" id="opcion">
                          <input type="hidden" name="id" value="'.$id.'" id="id">
                          <input type="hidden" name="columna_calificacion" value="'.Input::get('columna_calificacion').'" id="columna_calificacion">
                          <input type="hidden" name="anio" value="'.Input::get('anio').'" id="anio">
                          <input type="hidden" name="periodo_id" value="'.Input::get('periodo_id').'" id="periodo_id">
                          <input type="hidden" name="curso_id" value="'.Input::get('curso_id').'" id="curso_id">
                          <input type="hidden" name="asignatura_id" value="'.Input::get('asignatura_id').'" id="asignatura_id">
                          <input type="hidden" name="creado_por" value="'.$creado_por.'" id="creado_por">
                          <input type="hidden" name="modificado_por" value="'.$modificado_por.'" id="modificado_por">
                        '.Form::close();

        return $formulario;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        dd('hi');
        $cerrar_modal = "true";

        switch ( $request->id ) {
            case '0':
                // Crear
                EncabezadoCalificacion::create( $request->all() );
                $cerrar_modal = "true";
                break;
            
            default:
                // Actualizar
                $registro = EncabezadoCalificacion::find( $request->id );
                $registro->fill( $request->all() );
                $registro->save();
                $cerrar_modal = "false";
                break;
        }

        return $cerrar_modal;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
