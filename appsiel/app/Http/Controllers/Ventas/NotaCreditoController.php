<?php

namespace App\Http\Controllers\ventas;

use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Inventarios\InventarioController;
use App\Http\Controllers\Core\TransaccionController;

use App\Http\Controllers\Contabilidad\ContabilidadController;

use App\Core\EncabezadoDocumentoTransaccion;

use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\DevolucionVentas;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;

use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\Ventas\VtasMovimiento;
use App\Ventas\Cliente;

use App\Contabilidad\ContabMovimiento;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;
use App\Ventas\Services\NotaCreditoServices;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class NotaCreditoController extends TransaccionController
{
    public $movimiento_cxc;

    /**
     * Show the form for creating a new resource.
     * Este método create() es llamado desde un botón-select en el index de ventas
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->set_variables_globales();

        $id_transaccion = $this->transaccion->id;

        $saldo_pendiente = 0;
        $vec_saldos = [0,0,0];

        if ( is_null( Input::get('factura_id') ) )
        {
            return redirect('web?id=13&id_modelo=167')->with('mensaje_error','No puede hacer notas crédito desde esta opción. Debe ir al Botón Crear Nota crédito directa o hacer la Nota desde una Factura.');
        }

        $factura = VtasDocEncabezado::get_registro_impresion( Input::get('factura_id') );

        $this->movimiento_cxc = CxcMovimiento::where('core_tipo_transaccion_id', $factura->core_tipo_transaccion_id)
                            ->where('core_tipo_doc_app_id', $factura->core_tipo_doc_app_id)
                            ->where('consecutivo', $factura->consecutivo)
                            ->get()
                            ->first();

        if ( is_null( $this->movimiento_cxc ) )
        {
            return redirect('ventas/'.$factura->id.'?id=13&id_modelo=139&id_transaccion=23')->with('mensaje_error','La factura no tiene registros de cuentas por cobrar');
        }

        if ( $this->movimiento_cxc->saldo_pendiente == 0 )
        {
            return redirect('ventas/'.$factura->id.'?id=13&id_modelo=139&id_transaccion=23')->with('mensaje_error','La factura no tiene SALDO PENDIENTE por cobrar');
        }

        $vec_saldos = [$this->movimiento_cxc->valor_documento, $this->movimiento_cxc->valor_pagado, $this->movimiento_cxc->saldo_pendiente];

        // Información de la Factura de ventas
        $doc_encabezado = VtasDocEncabezado::get_registro_impresion( Input::get('factura_id') );
        $doc_registros = VtasDocRegistro::get_registros_impresion( Input::get('factura_id') );

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['15-entrada'=>'Devolución por ventas'];

        $tabla = View::make('ventas.notas_credito.tabla_registros_create', compact( 'doc_registros', 'motivos', 'vec_saldos' ) )->render();
        
        $lista_campos = ModeloController::get_campos_modelo( $this->modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$this->transaccion,$lista_campos,$cantidad_campos,'create',null);

        $eid = '';

			if( config("configuracion.tipo_identificador") == 'NIT') { 
                $eid = number_format( $doc_encabezado->numero_identificacion, 0, ',', '.');
            }	else { 
                $eid = $doc_encabezado->numero_identificacion;
            }

        // Agregar al comienzo del documento
        array_unshift($lista_campos, [
                                            "id" => 201,
                                            "descripcion" => "Empresa",
                                            "tipo" => "personalizado",
                                            "name" => "encabezado",
                                            "opciones" => "",
                                            "value" => '<div style="border: solid 1px #ddd; padding-top: -20px;">
                                                            <b style="font-size: 1.6em; text-align: center; display: block;">
                                                                '.$doc_encabezado->documento_transaccion_descripcion.'
                                                                <br/>
                                                                <b>No.</b> '.$doc_encabezado->documento_transaccion_prefijo_consecutivo.'
                                                            </b>
                                                            <br/>
                                                            <b>Cliente:</b> '.$doc_encabezado->tercero_nombre_completo.'
                                                            <br/>
                                                            <b>'.config("configuracion.tipo_identificador").' &nbsp;&nbsp;</b> ' . $eid. '
                                                        </div>',
                                            "atributos" => [],
                                            "definicion" => "",
                                            "requerido" => 0,
                                            "editable" => 1,
                                            "unico" => 0
                                        ] );

        $form_create = [
                        'url' => $this->modelo->url_form_create,
                        'campos' => $lista_campos
                    ];
        
        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, 'Crear: '.$this->transaccion->descripcion );

        return view('ventas.notas_credito.create', compact('form_create','id_transaccion','miga_pan','tabla','doc_encabezado'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $factura = VtasDocEncabezado::get_registro_impresion( $request->ventas_doc_relacionado_id ); // WARNING: si la factura tiene varias entradas, no se puede hacer la nota

        $request['creado_por'] = Auth::user()->email;
        $request['vendedor_id'] = $factura->vendedor_id;

        // 1ro. Crear documento de Entrada de inventarios (Devolución) con base en la remisión y las cantidades a devolver
        $devolucion = new DevolucionVentas;
        $documento_devolucion = $devolucion->crear_nueva( $request->all(), $factura->remision_doc_encabezado_id );

        // 2do. Crear encabezado del documento de ventas (Nota Crédito)
        $request['remision_doc_encabezado_id'] = $documento_devolucion->id;
        $request['ventas_doc_relacionado_id'] = $factura->id; // Relacionar Nota con la Factura
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );
        $nota_credito = $encabezado_documento->crear_nuevo( $request->all() );

        // 3ro. Crear líneas de registros del documento
        NotaCreditoController::crear_registros_nota_credito( $request, $nota_credito, $factura );

        return redirect('ventas_notas_credito_directa/'.$nota_credito->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion);
    }

    /*
        Crea los registros, el movimiento y la contabilización de un documento. 
        Todas estas operaciones se crean juntas porque se almacenena en cada iteración de las lineas de registros
        No Devuelve nada
    */
    public static function crear_registros_nota_credito( Request $request, $nota_credito, $factura )
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        $datos = $request->all();

        // Se crean los registro con base en el documento de inventario ya creado
        // lineas_registros solo tiene el ID del documentos de inventario
        // remision_doc_encabezado_id es el ID de una devolución en ventas
        $lineas_registros = [(object)[ 'id_doc' => $nota_credito->remision_doc_encabezado_id ]];

        NotaCreditoController::crear_lineas_registros_ventas( $datos, $nota_credito, $lineas_registros, $factura );

        return true;
    }

    // Se crean los registros con base en los registros de la devolución
    public static function crear_lineas_registros_ventas( $datos, $nota_credito, $lineas_registros, $factura )
    {
        $nota_credito_service = new NotaCreditoServices();

        $total_documento = 0;
        // Por cada remisión pendiente
        $cantidad_registros = count( $lineas_registros );
        $remision_doc_encabezado_id = '';
        $primera = true;
        for ($i=0; $i < $cantidad_registros ; $i++)
        {
            $doc_devolucion_id = (int)$lineas_registros[$i]->id_doc;

            $registros_devolucion = InvDocRegistro::where( 'inv_doc_encabezado_id', $doc_devolucion_id )->get();

            foreach ($registros_devolucion as $un_registro)
            {
                // Nota: $un_registro contiene datos de inventarios 
                $cantidad = $un_registro->cantidad * -1; // Fue una entrada de inventarios, se vuelve la cantidad negativa, porque es una diminución de las ventas

                // Los precios se deben traer de la linea de la factura
                $linea_factura = VtasDocRegistro::where( 'vtas_doc_encabezado_id', $factura->id)
                                                ->where( 'inv_producto_id', $un_registro->inv_producto_id )
                                                ->get()
                                                ->first();

                $precio_unitario = $linea_factura->precio_unitario;

                $precio_unitario_con_descuento = $linea_factura->precio_unitario * ( 1 - $linea_factura->tasa_descuento / 100 );

                $base_impuesto = $linea_factura->base_impuesto;

                $precio_total_con_descuento = $precio_unitario_con_descuento * $cantidad;

                $valor_total_descuento = ( $precio_unitario - $precio_unitario_con_descuento ) * $un_registro->cantidad;

                $valor_impuesto = ( $precio_unitario_con_descuento - $base_impuesto ) * $cantidad;

                $linea_datos = [ 'inv_bodega_id' => $un_registro->inv_bodega_id ] +
                                [ 'inv_motivo_id' => $un_registro->inv_motivo_id ] +
                                [ 'inv_producto_id' => $un_registro->inv_producto_id ] +
                                [ 'precio_unitario' => $precio_unitario ] +
                                [ 'cantidad' => $cantidad ] +
                                [ 'precio_total' => $precio_total_con_descuento ] +
                                [ 'base_impuesto' =>  $base_impuesto ] +
                                [ 'tasa_impuesto' => $linea_factura->tasa_impuesto ] +
                                [ 'valor_impuesto' =>  $valor_impuesto ] +
                                [ 'base_impuesto_total' => ( $base_impuesto * $un_registro->cantidad ) ] +
                                [ 'tasa_descuento' => $linea_factura->tasa_descuento ] +
                                [ 'valor_total_descuento' => $valor_total_descuento ] +
                                [ 'creado_por' => Auth::user()->email ] +
                                [ 'estado' => 'Activo' ];

                VtasDocRegistro::create( 
                                        $datos + 
                                        [ 'vtas_doc_encabezado_id' => $nota_credito->id ] +
                                        $linea_datos
                                    );

                $datos['consecutivo'] = $nota_credito->consecutivo;
                VtasMovimiento::create( 
                                        $datos +
                                        $linea_datos
                                    );

                // Contabilizar
                $detalle_operacion = $datos['descripcion'];

                // Reversar ingresos e impuestos
                NotaCreditoController::contabilizar_movimiento_debito( $datos + $linea_datos, $detalle_operacion );

                $total_documento += $precio_total_con_descuento;

                // Actualizar campo de cantidad_devuelta en cada línea de registro de la factura de ventas
                $nueva_cantidad_devuelta = $linea_factura->cantidad_devuelta + abs($un_registro->cantidad);
                $linea_factura->cantidad_devuelta = $nueva_cantidad_devuelta;
                $linea_factura->save();

            } // Fin por cada registro de la entrada

            // Marcar la entrada como facturada
            InvDocEncabezado::find( $doc_devolucion_id )->update( [ 'estado' => 'Facturada' ] );

            // Se va creando un listado de entradas separadas por coma 
            if ($primera)
            {
                $remision_doc_encabezado_id = $doc_devolucion_id;
                $primera = false;
            }else{
                $remision_doc_encabezado_id .= ','.$doc_devolucion_id;
            }

        }

        $nota_credito->valor_total = $total_documento;
        $nota_credito->remision_doc_encabezado_id = $remision_doc_encabezado_id;
        $nota_credito->save();
        
        // Un solo registro para reversar la cuenta por cobrar (CR)
        NotaCreditoController::contabilizar_movimiento_credito( $datos, $total_documento, $datos['descripcion'], $factura );

        // Actualizar registro del pago de la factura a la que afecta la nota
        if ($factura->forma_pago == 'credito') {
            $nota_credito_service->actualizar_registro_pago( $total_documento, $factura, $nota_credito, 'crear' );
        }else {
            $nota_credito_service->actualizar_movimiento_tesoreria( $total_documento, $factura, $nota_credito, 'crear' );
        }

        return true;
    }

    public static function contabilizar_movimiento_debito( $datos, $detalle_operacion )
    {
        // IVA descontable (DB)
        // Si se ha liquidado impuestos en la transacción
        if ( isset( $datos['tasa_impuesto'] ) && $datos['tasa_impuesto'] > 0 )
        {
            $cta_impuesto_ventas_id = InvProducto::get_cuenta_impuesto_devolucion_ventas( $datos['inv_producto_id'] );
            ContabilidadController::contabilizar_registro2( $datos, $cta_impuesto_ventas_id, $detalle_operacion, abs( $datos['valor_impuesto'] ), 0);
        }

        // La cuenta de ingresos se toma del grupo de inventarios
        $cta_ingresos_id = InvProducto::get_cuenta_ingresos( $datos['inv_producto_id'] );
        ContabilidadController::contabilizar_registro2( $datos, $cta_ingresos_id, $detalle_operacion, $datos['base_impuesto_total'], 0);
    }

    public static function contabilizar_movimiento_credito( $datos, $total_documento, $detalle_operacion, $factura = null )
    {
        /*
            Se crea un SOLO registro contable de la cuenta por cobrar (Crédito)
        */
            
        // Se resetean estos campos del registro
        $datos['inv_producto_id'] = 0;
        $datos['cantidad '] = 0;
        $datos['tasa_impuesto'] = 0;
        $datos['base_impuesto'] = 0;
        $datos['valor_impuesto'] = 0;
        $datos['inv_bodega_id'] = 0;

        if ( is_null($factura) )
        {
            $cxc_id = Cliente::get_cuenta_cartera( $datos['cliente_id'] );
        }else{
            $cxc_id = Cliente::get_cuenta_cartera( $factura->cliente_id );
        }
        
        ContabilidadController::contabilizar_registro2( $datos, $cxc_id, $detalle_operacion, 0, abs($total_documento) );
    }

    public function show($id)
    {
        $this->set_variables_globales();

        return redirect('ventas/'.$id.'?id='.$this->app->id.'&id_modelo='.$this->modelo->id.'&id_transaccion='.$this->transaccion->id.'&vista=ventas.notas_credito.show');
    }

    // ANULAR
    // La nota crédito realiza una salida del inventario
    public function anular( $id )
    {
        $nota_credito_service = new NotaCreditoServices();

        $this->set_variables_globales();

        $nota = VtasDocEncabezado::find( $id );
        $devolucion = InvDocEncabezado::find( $nota->remision_doc_encabezado_id );

        // Validar saldos negativos en movimientos de inventarios
        $linea_saldo_negativo = InvMovimiento::validar_saldo_movimientos_posteriores_todas_lineas( $devolucion, 'no_fecha', 'anular', 'salida' ); // al anular la devolución en ventas se hace una de inventarios
        
        if( $linea_saldo_negativo != '0')
        {
            return redirect( 'ventas/'.$id.'?id='.$this->app->id.'&id_modelo='.$this->modelo->id.'&id_transaccion='.$this->transaccion->id.'&vista=ventas.notas_credito.show')->with('mensaje_error',$linea_saldo_negativo);
        }

        $array_wheres = ['core_empresa_id'=>$nota->core_empresa_id, 
            'core_tipo_transaccion_id' => $nota->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $nota->core_tipo_doc_app_id,
            'consecutivo' => $nota->consecutivo];

        // 1ro. Actualizar campo de cantidad_devuelta en cada línea de registro de la factura de ventas
        $factura = VtasDocEncabezado::find( $nota->ventas_doc_relacionado_id );
        $registros_factura = VtasDocRegistro::where('vtas_doc_encabezado_id', $factura->id)->get();
        foreach ($registros_factura as $linea)
        {
            $cantidad_nota = VtasDocRegistro::where('vtas_doc_encabezado_id',$nota->id)
                                                ->where('inv_producto_id',$linea->inv_producto_id)
                                                ->value('cantidad');

            // La cantidad de la nota es negativa
            $nueva_cantidad_devuelta = $linea->cantidad_devuelta + $cantidad_nota;
            $linea->update( [ 'cantidad_devuelta' => $nueva_cantidad_devuelta ] );
        }

        // 2do. Anular documento asociado de inventarios
        InventarioController::anular_documento_inventarios( $nota->remision_doc_encabezado_id );

        // 3ro. Borrar registros contables del documento
        ContabMovimiento::where($array_wheres)->delete();

        // 4to. Se actualiza el registro de la factura a la que afecto la nota en el movimimeto de cuentas por cobrar
        // Se envía el valor en positivo para que sume al saldo pendiente y reste al valor abonado
        if ($factura->forma_pago == 'credito') {
            $nota_credito_service->actualizar_registro_pago( $nota->valor_total * -1, $factura, $nota, 'anular' );
        }else {
            $nota_credito_service->actualizar_movimiento_tesoreria( $nota->valor_total * -1, $factura, $nota, 'anular' );
        }

        // 5to. Se elimina el movimiento de ventas
        VtasMovimiento::where($array_wheres)->delete();

        $modificado_por = Auth::user()->email;
        // 6to. Se marcan como anulados los registros del documento
        VtasDocRegistro::where( 'vtas_doc_encabezado_id', $nota->id )->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por] );

        // 6to. Se marca como anulado el documento
        $nota->update( [ 'estado' => 'Anulado', 'ventas_doc_relacionado_id' => '0', 'remision_doc_encabezado_id' => '0', 'modificado_por' => $modificado_por] );

        return redirect( 'ventas/'.$id.'?id='.$this->app->id.'&id_modelo='.$this->modelo->id.'&id_transaccion='.$this->transaccion->id.'&vista=ventas.notas_credito.show')->with('flash_message','Nota anulada correctamente.');
    }

}