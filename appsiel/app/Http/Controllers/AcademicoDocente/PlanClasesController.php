<?php

namespace App\Http\Controllers\AcademicoDocente;
use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;

use App\Sistema\Modelo;
use App\AcademicoDocente\PlanClaseEncabezado;
use App\AcademicoDocente\PlanClaseRegistro;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class PlanClasesController extends ModeloController
{

    /**
     * Store a newly created resource in storage.
     * // Este método es llamado desde ModeloController@store
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    	$datos = $request->all(); // Datos originales
        
    	// Crear el encabezado
    	$registro = $this->crear_nuevo_registro( $request ); // Esta línea hace que la variable $request cambie (no se porqué   ¿¿¿???)

        if (isset($datos['elemento_descripcion'])) {
            foreach ( $datos['elemento_descripcion'] as $key => $value )
            {

                PlanClaseRegistro::create( 
                                            [ 
                                                'plan_clase_encabezado_id' => $registro->id,
                                                'plan_clase_estruc_elemento_id' => $datos['elemento_id'][ $key ],
                                                'contenido' => $value,
                                                'estado' => 'Activo'
                                            ]
                                        );
            }
        }
            

        $modelo = Modelo::find( $request->url_id_modelo );
        $this->almacenar_imagenes($request, $modelo->ruta_storage_imagen, $registro);
        
        return redirect( 'sga_planes_clases/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with( 'flash_message','Registro CREADO correctamente.' );
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    	$registros_anterior_siguiente = PlanClaseEncabezado::get_registros_anterior_siguiente( $id );
    	$reg_anterior = $registros_anterior_siguiente[0];
        $reg_siguiente = $registros_anterior_siguiente[1];

        $vista = $this->vista_preliminar( $id );

        $miga_pan = $this->get_miga_pan( $this->modelo, 'Consulta' );

        return view( 'academico_docente.planes_clases.show',compact( 'reg_anterior', 'reg_siguiente', 'miga_pan', 'vista', 'id') );

    }


    public function imprimir($id)
    {
    	$view = $this->vista_preliminar( $id );
        $vista = View::make( 'layouts.pdf3', compact( 'view' ) )->render();

        $orientacion='portrait';
        $tam_hoja='Letter';

        // Crear PDF
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML( $vista )->setPaper($tam_hoja,$orientacion);

        return $pdf->stream( 'plan_de_clases.pdf' );
    }

    public function vista_preliminar( $id )
    {
    	$encabezado = PlanClaseEncabezado::get_registro_impresion( $id );

        if( $encabezado->plantilla_plan_clases_id == 99999 )
        {
            $registros = PlanClaseRegistro::get_registros_impresion_guia( $id );
        }else{
            $registros = PlanClaseRegistro::get_registros_impresion( $id );
        }

    	return View::make( 'academico_docente.planes_clases.vista_impresion', compact( 'encabezado', 'registros' ) )->render();
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

        $datos = $request->all(); // Datos originales

       // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find($request->url_id_modelo);

        // Se obtinene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        $registro2 = '';
        // Si se envían datos tipo file
        if( !empty( $request->file() ) )
        {   
            // Copia identica del registro del modelo, pues cuando se almacenan los datos cambia la instancia
            $registro2 = $registro;
        }

        if (isset($datos['elemento_descripcion'])) 
        {
           
            foreach ( $datos['elemento_descripcion'] as $key => $value )
            {

                $registro_elemento = PlanClaseRegistro::where( 'plan_clase_encabezado_id', $registro->id )
                                                        ->where( 'plan_clase_estruc_elemento_id', $datos['elemento_id'][ $key ] )
                                                        ->get()
                                                        ->first();

                if ( !is_null( $registro_elemento ) )
                {
                    $registro_elemento->update( 
                                                    [ 
                                                        'contenido' => $value
                                                    ]
                                                );
                }else{
                    PlanClaseRegistro::create( 
                                            [ 
                                                'plan_clase_encabezado_id' => $registro->id,
                                                'plan_clase_estruc_elemento_id' => $datos['elemento_id'][ $key ],
                                                'contenido' => $value,
                                                'estado' => 'Activo'
                                            ]
                                        );
                }	
            }
        }

        $registro->fill( $request->all() );

        if ($request->hasFile('archivo_adjunto')) 
        {
            $general = new ModeloController;
            $registro->archivo_adjunto = $general->almacenar_imagenes( $request, $modelo->ruta_storage_imagen, $registro2, 'edit' );
        }

        $registro->save();

        return redirect( 'sga_planes_clases/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with( 'flash_message','Registro MODIFICADO correctamente.' );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function eliminar( $encabezado_id )
    {
    	$plan_clase = PlanClaseEncabezado::find( $encabezado_id );
        
        if ($plan_clase == null) {
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with( 'mensaje_error','Registro ELIMINADO correctamente.' );
        }

        if((int)$plan_clase->periodo->cerrado)
        {
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with( 'mensaje_error','Registro No se pudo eliminar. El Periodo está cerrado.' );
        }

        if((int)$plan_clase->periodo->periodo_lectivo->cerrado)
        {
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with( 'mensaje_error','Registro No se pudo eliminar. El Año lectivo está cerrado.' );
        }

        $plan_clase->delete();

        $registros_planes_clases = PlanClaseRegistro::where( 'plan_clase_encabezado_id', $encabezado_id )->get();

        foreach ($registros_planes_clases as $registro_plan) {
            $registro_plan->delete();
        }        

    	return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with( 'flash_message','Registro ELIMINADO correctamente.' );
    }


    public function remover_archivo_adjunto( $encabezado_id )
    {
        
        $registro = PlanClaseEncabezado::find( $encabezado_id );

        // Se borra el archivo del disco
        Storage::delete( 'planes_clases/' . $registro->archivo_adjunto );

        // Actualizar registro
        $registro->update( [ 'archivo_adjunto' => '' ] );

        return redirect( 'sga_planes_clases/'.$encabezado_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion=0' )->with( 'flash_message','Archivo adjunto removido correctamente.' );
        
    }

}