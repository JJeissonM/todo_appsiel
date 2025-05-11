<?php

namespace App\Http\Controllers\Ventas;

use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;

use App\Sistema\Modelo;
use App\Sistema\Campo;
use App\Core\Tercero;

use App\Ventas\Cliente;
use App\Ventas\Vendedor;
use App\Ventas\VtasMovimiento;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\ListaDctoDetalle;
use App\Ventas\Services\CustomerServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class ClienteController extends ModeloController
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $Cliente = (new CustomerServices())->store_new_customer($request->all());

        $acciones = $this->acciones_basicas_modelo( Modelo::find( 138 ), '' );
        
        $url_ver = str_replace('id_fila', $Cliente->id, $acciones->show);

        return redirect( $url_ver . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo )->with( 'flash_message', 'Registro CREADO correctamente.' );
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $modelo = Modelo::find( Input::get('id_modelo') );
        
        $registro = Cliente::find($id);
        $reg_anterior = app($modelo->name_space)->where('id', '<', $registro->id)->max('id');
        $reg_siguiente = app($modelo->name_space)->where('id', '>', $registro->id)->min('id');

        // Se obtienen los campos asociados a ese modelo
        $lista_campos = $modelo->campos()->orderBy('orden')->get()->toArray();

        // Formatear-asignar el valor correspondiente del registro del modelo
        
        // 1ro. Para los campos del modelo Cliente
        $lista_campos = Campo::asignar_valores_registro( $lista_campos, $registro );
        
        // 2do. Para los campos del modelo Tercero
        $tercero = Tercero::find($registro->core_tercero_id);
        $lista_campos = Campo::asignar_valores_registro( $lista_campos, $tercero );
        
        $lista_campos = $this->cambiar_opciones_campo_vendedor( $lista_campos );

        $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        $acciones = $this->acciones_basicas_modelo( $modelo, $variables_url );

        $url_crear = $acciones->create;
        $url_edit = $acciones->edit;

        $form_create = [
                        'url' => $acciones->store,
                        'campos' => $lista_campos
                    ];

        $miga_pan = $this->get_miga_pan($modelo, $tercero->razon_social . ' ' . $tercero->descripcion );

        $tabla = '';

        return view( 'ventas.clientes.show', compact('form_create','miga_pan','registro','url_crear','url_edit','reg_anterior','reg_siguiente','tabla') );
    }

    public function cambiar_opciones_campo_vendedor( $lista_campos )
    {
        $cantidad_campos = count($lista_campos);
        for ($i=0; $i < $cantidad_campos; $i++) 
        {
            if ( $lista_campos[$i]['name'] == 'vendedor_id' ) 
            {
                
                $raw = 'CONCAT(core_terceros.apellido1, " ",core_terceros.apellido2, " ",core_terceros.nombre1, " ",core_terceros.otros_nombres) AS descripcion';

                $opciones = Vendedor::leftJoin('core_terceros','core_terceros.id','=','vtas_vendedores.core_tercero_id')
                            ->select('vtas_vendedores.id', DB::raw($raw))
                            ->get();

                $vec = '{';
                $es_el_primero = true;
                foreach ($opciones as $opcion)
                {
                    if ( $es_el_primero ) 
                    {
                        $vec .= '"'.$opcion->id.'":"'.$opcion->descripcion.'"';
                        $es_el_primero = false;
                    }else{
                        $vec .= ',"'.$opcion->id.'":"'.$opcion->descripcion.'"';
                    }
                }

                $vec .= '}';

                $lista_campos[$i]['opciones'] = $vec;
            }
        }
        return $lista_campos;        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtiene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        $lista_campos = $this->get_campos_modelo($modelo,$registro,'edit');

        $tercero = Tercero::find($registro->core_tercero_id);
        $registro->descripcion = $tercero->descripcion;
        $registro->nombre1 = $tercero->nombre1;
        $registro->otros_nombres = $tercero->otros_nombres;
        $registro->apellido1 = $tercero->apellido1;
        $registro->apellido2 = $tercero->apellido2;
        $registro->id_tipo_documento_id = $tercero->id_tipo_documento_id;
        $registro->numero_identificacion = $tercero->numero_identificacion;
        $registro->digito_verificacion = $tercero->digito_verificacion;
        $registro->direccion1 = $tercero->direccion1;
        $registro->direccion2 = $tercero->direccion2; // Se está usando para fecha de cumpleaños
        $registro->telefono1 = $tercero->telefono1;
        $registro->codigo_ciudad = $tercero->codigo_ciudad;
        $registro->tipo = $tercero->tipo;
        $registro->razon_social = $tercero->razon_social;
        $registro->email = $tercero->email;
        
        $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        $acciones = $this->acciones_basicas_modelo( $modelo, $variables_url );
        $form_create = [
                        'url' => str_replace('id_fila', $registro->id, $acciones->update),
                        'campos' => $lista_campos
                    ];

        $url_action = str_replace('id_fila', $registro->id, $acciones->update);

        $miga_pan = $this->get_miga_pan($modelo, $tercero->razon_social . ' ' . $tercero->descripcion." > Modificar");

        $archivo_js = app($modelo->name_space)->archivo_js;

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

        $modelo = Modelo::find($request->url_id_modelo);
        // Se obtinene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        $registro->fill( $request->all() );
        $registro->save();
        
        // Modificar el datos tercero asociados
        $registro2 = Tercero::find( $registro->core_tercero_id );
        
        $tercero = Tercero::find( $registro->core_tercero_id );
        $tercero->fill( $request->all() );
        $tercero->save();

        $this->almacenar_imagenes($request, $modelo->ruta_storage_imagen, $registro2, 'edit');

        return redirect('vtas_clientes/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Registro MODIFICADO correctamente.');
    }


    
    public function eliminar_cliente($id)
    {
        // Verificar si cliente está en movimiento de ventas
        $movimiento = VtasMovimiento::where('cliente_id',$id)->get()->toArray();

        if( !empty($movimiento) )
        {
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Cliente no se puede eliminar tiene movimientos de ventas.');
        }

        // Verificar si cliente está en encabezados de ventas
        $docs = VtasDocEncabezado::where('cliente_id',$id)->get()->toArray();

        if( !empty($docs) )
        {
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Cliente no se puede eliminar tiene documentos de ventas.');
        }

        Cliente::find($id)->delete();

        return redirect('web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('flash_message','Cliente ELIMINADO correctamente.');
    }

    // Detalles de Listas de precios y descuentos
    public function get_lista_precios_cliente( $cliente_id )
    {
        $cliente = Cliente::find( $cliente_id );
        $precios = ListaPrecioDetalle::get_precios_productos_de_la_lista( $cliente->lista_precios_id );
        $descuentos = ListaDctoDetalle::get_descuentos_productos_de_la_lista( $cliente->lista_descuentos_id );

        return [ $precios, $descuentos ];
    }


    public function get_opciones_select_contactos( $cliente_id )
    {
        $cliente = Cliente::find( $cliente_id );
        $contactos = $cliente->contactos;

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($contactos as $contacto)
        {
            $opciones .= '<option value="' . $contacto->id . '">' . $contacto->tercero->descripcion . '</option>';
        }

        return $opciones;
    }

}