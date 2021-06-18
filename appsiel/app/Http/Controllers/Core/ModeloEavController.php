<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\VistaController;

use Input;
use DB;

use App\Sistema\Modelo;
use App\Core\ModeloEavValor;

use App\Salud\ExamenTieneVariables;
use App\Salud\ExamenTieneOrganos;
use App\Salud\ExamenMedico;

class ModeloEavController extends ModeloController
{

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        session( ['url_anterior' => url()->previous() ] );

        $general = new ModeloController();

        // Al nuevo registro del modelo que se está creado, se le deben enviar los campos modelo_padre_id, registro_modelo_padre_id y modelo_entidad_id via get (en la URL)
        return $general->create();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Se verifican si vienen campos con valores tipo array. Normalmente para los campos tipo chexkbox o radio
        foreach ( $request->all() as $key => $value)
        {
            if ( is_array($value) )
            {
                $request[$key] = implode( ",", $value);
            }
        }

        // Registro del Modelo Entidad en EAV
        $modelo = Modelo::find( $request->modelo_entidad_id );

        // Se va a crear un registro por cada Atributo (campo) que tenga un Valor distinto a vacío 
        foreach ( $request->all() as $key => $value) 
        {
            // Si el name del campo enviado tiene la palabra core_campo_id
            if ( strpos( $key, "core_campo_id") !== false ) 
            {
                $core_campo_id = explode("-", $key)[1]; // Atributo
                $valor = $value; // Valor

                if( $request->modelo_padre_id != null )
                {
                    $modelo_padre_id = $request->modelo_padre_id;
                }else{
                    $modelo_padre_id = 0;
                }


                if( $request->registro_modelo_padre_id != null )
                {
                    $registro_modelo_padre_id = $request->registro_modelo_padre_id;
                }else{
                    $registro_modelo_padre_id = 0;
                }


                if ( $valor != '' ) 
                {                    
                    app($modelo->name_space)->create( [ "modelo_padre_id" => $modelo_padre_id, "registro_modelo_padre_id" => $registro_modelo_padre_id, "modelo_entidad_id" => $request->modelo_entidad_id, "core_campo_id" => $core_campo_id, "valor" => $valor ] );
                }
            }
        }

        $url = $request->session()->get('url_anterior');

        return redirect( $url )->with( 'flash_message','Registro creado correctamente.' );
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
        session( ['url_anterior' => url()->previous() ] );

        $general = new ModeloController();

        $modelo = Modelo::find( $id ); // $id corresponde al modelo_entidad_id

        // Se obtienen los campos asociados a ese modelo
        $lista_campos = $general->get_campos_modelo($modelo, '', 'edit');

        // Asignar a cada $campo en la key value el valor que tienen en la tabla core_eav_valores, pues el Form::model(), no lo puede asignar automáticamente a través del name.
        $cant = count($lista_campos);
        for ($i=0; $i < $cant; $i++) 
        {
            if ( $lista_campos[$i]['tipo'] != 'bsCheckBox' )
            {
                $lista_campos[$i] = VistaController::mostrar_campo( $lista_campos[$i]['id'], 
                                                            app($modelo->name_space)->where( [ "modelo_padre_id" => Input::get('modelo_padre_id'), "registro_modelo_padre_id" => Input::get('registro_modelo_padre_id'), "modelo_entidad_id" => $id, "core_campo_id" => $lista_campos[$i]['id'] ] )->value('valor'),
                                                                'edit' );
            }
                
        }

        $acciones = $this->acciones_basicas_modelo( $modelo, '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion') );

        $url_action = str_replace('id_fila', $id, $acciones->update);
        
        $form_create = [
                        'url' => $url_action,
                        'campos' => $lista_campos
                    ];

        $miga_pan = ModeloController::get_miga_pan($modelo,'Crear nuevo');

        // Si el modelo tiene un archivo js particular
        $archivo_js = app($modelo->name_space)->archivo_js;

        $registro = new $modelo->name_space;

        return view('layouts.edit',compact('form_create','miga_pan','registro','archivo_js','url_action'));
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
        // Registro del Modelo Entidad en EAV
        $modelo = Modelo::find($request->modelo_entidad_id);

        // Se verifican si vienen campos con valores tipo array. Normalmente para los campos tipo chexkbox.
        foreach ( $request->all() as $key => $value)
        {
            if ( is_array($value) )
            {
                $request[$key] = implode(",", $value);
            }
        }

        // Se va a crear un registro por cada Atributo (campo) que tenga un Valor distinto a vacío 
        foreach ( $request->all() as $key => $value) 
        {
            $data = [ "modelo_padre_id" => $request->modelo_padre_id, "registro_modelo_padre_id" => $request->registro_modelo_padre_id, "modelo_entidad_id" => $request->modelo_entidad_id ];

            // Si el name del campo enviado tiene la palabra core_campo_id
            if ( strpos( $key, "core_campo_id") !== false ) 
            {
                $core_campo_id = explode("-", $key)[1]; // Atributo
                $valor = $value; // Valor

                // Se obtiene el registro de cada campo
                $registro = app($modelo->name_space)->where( $data + [ "core_campo_id" => $core_campo_id ] )->first();
                
                // Se procede a hacer tres posible procesos

                // 1ro. CREAR NUEVO REGISTRO 
                // Si el registro no existe y el valor enviado NO ESTÁ VACÍO
                if ( is_null($registro) && $valor != '' ) 
                {
                    app($modelo->name_space)->create( $data + [ "core_campo_id" => $core_campo_id, "valor" => $valor ] );
                }

                // 2do. ACTUALIZAR REGISTRO EXISTENTE
                // Si el registro existe y se envía algún
                if ( !is_null($registro) && $valor != '' ) 
                {
                    $registro->fill( $data + [ "core_campo_id" => $core_campo_id, "valor" => $valor ] );
                    $registro->save();
                }


                // 3ro. ELIMINAR EL REGISTRO
                // Si el registro existe y su valor cambió a vacío
                if ( !is_null($registro) && $valor == '' )
                {
                    $registro->delete();
                }
            }
        }

        $url = $request->session()->get('url_anterior');
        $request->session()->forget('url_anterior');

        return redirect( $url )->with( 'flash_message','Registro creado correctamente.' );
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

    public static function show_datos_entidad( $modelo_padre_id, $registro_modelo_padre_id, $modelo_entidad_id )
    {

        $modelo_entidad = Modelo::find( $modelo_entidad_id );
        
        $campos = $modelo_entidad->campos()->where('name','core_campo_id-ID')->orderBy('orden')->get();
        
        $valores_entidades = app($modelo_entidad->name_space)->where([ 'modelo_padre_id' => $modelo_padre_id, 'registro_modelo_padre_id' => $registro_modelo_padre_id, 'modelo_entidad_id'=>$modelo_entidad_id])->get();
        
        
        $salida = '<table class="table table-bordered">';
        $num_cols = 2;
        $i=0;

        foreach ( $campos as $linea ) 
        {
            if ( $i % $num_cols == 0) 
            {
                $salida .= '<tr>';
            }
            
            $valor = ModeloEavController::get_valor_desde_valores_entidades( $valores_entidades, $linea->id );

            $salida .= '<td>'.VistaController::mostrar_campo( $linea->id, $valor, 'show' ).'</td>';

            $i++;

            if ( $i % $num_cols == 0) 
            {
                $salida .= '</tr>';
            }     
        }            

        $salida .= '</table> <br>';

        return $salida;
    }


    public static function get_valor_desde_valores_entidades( $valores_entidades, $core_campo_id )
    {
        $valor = '--';

        foreach ($valores_entidades as $linea )
        {
            if( $linea->core_campo_id == $core_campo_id )
            {
                $valor = $linea->valor;
            }
        }

        return $valor;
    }


    function eliminar_registros_eav( Request $request )
    {
        ModeloEavValor::where( [ 'modelo_padre_id' => $request->modelo_padre_id, 'registro_modelo_padre_id' => $request->registro_modelo_padre_id, 'modelo_entidad_id' => $request->modelo_entidad_id ] )->delete();

        return redirect( $request->ruta_redirect )->with('mensaje_error','Registros de '.$request->lbl_descripcion_modelo_entidad.' ELIMINADOS correctamente.');
    }
}