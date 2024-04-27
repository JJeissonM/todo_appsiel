<?php

namespace App\Http\Controllers\Compras;

use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Inventarios\InventarioController;
use App\Http\Controllers\Core\TransaccionController;

use App\Http\Controllers\Contabilidad\ContabilidadController;

use App\Core\EncabezadoDocumentoTransaccion;

// Modelos

use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvProducto;
use App\Inventarios\InvMotivo;

use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasDocRegistro;
use App\Compras\ComprasMovimiento;
use App\Compras\Proveedor;

use App\Contabilidad\ContabMovimiento;

use App\CxP\CxpMovimiento;
use App\CxP\CxpAbono;

use App\Contabilidad\Impuesto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class NotaCreditoValorController extends TransaccionController
{
    /**
     * Show the form for creating a new resource.
     * Este método create() es llamado desde un botón-select en el index de compras
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        if ( is_null( Input::get('factura_id') ) )
        {
            return redirect('web?id=9&id_modelo=166')->with('mensaje_error','No puede hacer notas crédito desde esta opción. Debe ir al Botón Crear Nota crédito directa');
        }
            
        $this->set_variables_globales();

        $id_transaccion = $this->transaccion->id;

        $doc_encabezado = ComprasDocEncabezado::get_registro_impresion( Input::get('factura_id') );

        $movimiento_cxc = CxpMovimiento::where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
                            ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
                            ->where('consecutivo', $doc_encabezado->consecutivo)
                            ->get()
                            ->first();
        
        $saldo_pendiente = $doc_encabezado->valor_total;

        if ( $movimiento_cxc != null )
        {
            $saldo_pendiente = $movimiento_cxc->saldo_pendiente;
            if ( $movimiento_cxc->saldo_pendiente == 0 )
            {
                return redirect('compras/'.$doc_encabezado->id.'?id=9&id_modelo=159&id_transaccion=25')->with('mensaje_error','Factura Crédito. No tiene SALDO PENDIENTE por cobrar.');
            }
        }

        // Información de la Factura de compras
        $doc_registros = ComprasDocRegistro::get_registros_impresion( Input::get('factura_id') );
       
        $lista_campos = ModeloController::get_campos_modelo( $this->modelo,'','create');

        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$this->transaccion,$lista_campos,$cantidad_campos,'create',null);

        for ($i = 0; $i < $cantidad_campos; $i++) {
            if ($lista_campos[$i]['name'] == 'valor_total') {
                $lista_campos[$i]['value'] = $doc_encabezado->valor_total;
            }
        }

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
                                                                <br/>
                                                                <b>Fecha:</b> '.$doc_encabezado->fecha.'
                                                            </b>
                                                            <br/>
                                                            <b>Cliente:</b> '.$doc_encabezado->tercero_nombre_completo.'
                                                            <br/>
                                                            <b>'.config("configuracion.tipo_identificador").' &nbsp;&nbsp;</b> ' . $eid. '
                                                            <br/>
                                                            <b>Valor Factura &nbsp;&nbsp;</b> $' . number_format($doc_encabezado->valor_total, 0, ',','.'). '
                                                            <br/>
                                                            <input type="hidden" name="valor_saldo_pendiente" id="valor_saldo_pendiente" value="' . $saldo_pendiente . '">
                                                            <b>Valor Pendiente &nbsp;&nbsp;</b> $' . number_format($saldo_pendiente, 0, ',','.'). '
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

        return view('compras.notas_credito.por_valor.create', compact('form_create', 'id_transaccion', 'miga_pan', 'doc_encabezado'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $factura = ComprasDocEncabezado::get_registro_impresion( $request->compras_doc_relacionado_id ); 
        
        $request['creado_por'] = Auth::user()->email;

        // Crear encabezado del documento de Compras (Nota Crédito por Valor)
        $request['compras_doc_relacionado_id'] = $factura->id; // Relacionar Nota con la Factura
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );

        $nota_credito = $encabezado_documento->crear_nuevo( $request->all() );

        // Crear líneas de registros del documento
        $this->crear_registros_nota_credito( $request->all(), $nota_credito, $factura );

        return redirect('compras_notas_credito_directa/'.$nota_credito->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion);
    }
    
    /*
        Crea los registros, el movimiento y la contabilización de un documento. 
        Todas estas operaciones se crean juntas porque se almacenena en cada iteración de las lineas de registros
        No Devuelve nada
    */
    // Se crean los registros con base en los registros de la devolución
    public function crear_registros_nota_credito( $datos, $nota_credito, $factura )
    {
        $registros_contab_factura = ContabMovimiento::where([
                                    'core_empresa_id' => $factura->core_empresa_id, 
                                    'core_tipo_transaccion_id'  =>  $factura->core_tipo_transaccion_id,
                                    'core_tipo_doc_app_id'  =>  $factura->core_tipo_doc_app_id,
                                    'consecutivo'  =>  $factura->consecutivo
                                ])->get();

        foreach ($registros_contab_factura as $contab_movim) {
            $contab_movim->fecha = $nota_credito->fecha;
            $contab_movim->core_tipo_transaccion_id = $nota_credito->core_tipo_transaccion_id;
            $contab_movim->core_tipo_doc_app_id = $nota_credito->core_tipo_doc_app_id;
            $contab_movim->consecutivo = $nota_credito->consecutivo;

            if ( $contab_movim->valor_debito == 0 ) {
                $porc_participacion = abs($contab_movim->valor_credito) / $factura->valor_total;
                $contab_movim->valor_credito = $nota_credito->valor_total * $porc_participacion * -1;
                $contab_movim->valor_saldo = $nota_credito->valor_total * $porc_participacion * -1;
            }else{
                $contab_movim->valor_debito = $nota_credito->valor_total;
                $contab_movim->valor_saldo = $nota_credito->valor_total;
            }

            $contab_movim->tipo_transaccion = 'nota_credito_valor';
            $contab_movim->creado_por = $nota_credito->creado_por;
            
            $nuevo_movimiento = $contab_movim->toArray();

            ContabMovimiento::create( $nuevo_movimiento );
        }

        $registros_entrada_almacen = InvDocRegistro::where( 'inv_doc_encabezado_id', $factura->entrada_almacen_id )->get();

        foreach ($registros_entrada_almacen as $un_registro)
        {
            
        }

        if ( $factura->forma_pago == 'credito' ) {
            // Actualizar registro del pago de la factura a la que afecta la nota
            $this->actualizar_registro_pago( $nota_credito->valor_total, $factura, $nota_credito, 'crear' );
        }         

        return true;
    }

    public function contabilizar_movimiento_credito( $datos, $detalle_operacion )
    {
        // IVA descontable (CR)
        // Si se ha liquidado impuestos en la transacción
        if ( isset( $datos['tasa_impuesto'] ) && $datos['tasa_impuesto'] > 0 )
        {
            $cta_impuesto_compras_id = InvProducto::get_cuenta_impuesto_devolucion_compras( $datos['inv_producto_id'] );
            ContabilidadController::contabilizar_registro2( $datos, $cta_impuesto_compras_id, $detalle_operacion, 0, abs( $datos['valor_impuesto'] ));
        }

        // Reversar cuenta por legalizar (completar CR), en la transacción inventarios, se liquidó el costo_total
        $cta_contrapartida_id = InvMotivo::find( $datos['inv_motivo_id'] )->cta_contrapartida_id;
        ContabilidadController::contabilizar_registro2( $datos, $cta_contrapartida_id, $detalle_operacion, 0, abs( $datos['base_impuesto'] ));
    }

    public function contabilizar_movimiento_debito( $forma_pago, $datos, $total_documento, $detalle_operacion, $factura = null )
    {
        /*
            Se crea un SOLO registro contable de la cuenta por pagar (Crédito) o la tesorería (Contado)
        */

        
        // Contabilizar Cta. Por Pagar (DB)

        // Se resetean estos campos del registro
        $datos['inv_producto_id'] = 0;
        $datos['cantidad '] = 0;
        $datos['tasa_impuesto'] = 0;
        $datos['base_impuesto'] = 0;
        $datos['valor_impuesto'] = 0;
        $datos['inv_bodega_id'] = 0;

        if ( is_null($factura) )
        {
            $cxp_id = Proveedor::get_cuenta_por_pagar( $datos['proveedor_id'] );
        }else{
            $cxp_id = Proveedor::get_cuenta_por_pagar( $factura->proveedor_id );
        }
        
        ContabilidadController::contabilizar_registro2( $datos, $cxp_id, $detalle_operacion, abs($total_documento), 0 );
    }

    public function actualizar_registro_pago( $total_nota, $factura, $nota, $accion )
    {
        /*
            Al crear la nota: Se disminuye el saldo pendiente y se aumenta el valor pagado. También se crea un registro de abono de cxp
            A anular la nota: Se aumenta el saldo pendiente y se disminuye el valor pagado
        */

        // total_nota es negativo cuando se hace la nota y positivo cuando se anula


        $movimiento_cxp = CxpMovimiento::where('core_tipo_transaccion_id', $factura->core_tipo_transaccion_id)
                                ->where('core_tipo_doc_app_id', $factura->core_tipo_doc_app_id)
                                ->where('consecutivo', $factura->consecutivo)
                                ->get()
                                ->first();

        $nuevo_total_pendiente = $movimiento_cxp->saldo_pendiente + $total_nota; 
        $nuevo_total_pagado = $movimiento_cxp->valor_pagado - $total_nota;

        $estado = 'Pendiente';
        if ( $nuevo_total_pendiente == 0)
        {
            $estado = 'Pagado';
        }

        $movimiento_cxp->update( [ 
                                    'valor_pagado' => $nuevo_total_pagado,
                                    'saldo_pendiente' => $nuevo_total_pendiente,
                                    'estado' => $estado
                                ] );

        $datos = ['core_tipo_transaccion_id' => $nota->core_tipo_transaccion_id]+
                  ['core_tipo_doc_app_id' => $nota->core_tipo_doc_app_id]+
                  ['consecutivo' => $nota->consecutivo]+
                  ['fecha' => $nota->fecha]+
                  ['core_empresa_id' => $nota->core_empresa_id]+
                  ['core_tercero_id' => $nota->core_tercero_id]+
                  ['modelo_referencia_tercero_index' => 'App\Compras\Proveedor']+
                  ['referencia_tercero_id' => $factura->proveedor_id]+
                  ['doc_cxp_transacc_id' => $factura->core_tipo_transaccion_id]+
                  ['doc_cxp_tipo_doc_id' => $factura->core_tipo_doc_app_id]+
                  ['doc_cxp_consecutivo' => $factura->consecutivo]+
                  ['doc_cruce_transacc_id' => 0]+
                  ['doc_cruce_tipo_doc_id' => 0]+
                  ['doc_cruce_consecutivo' => 0]+
                  ['abono' => abs($total_nota)]+
                  ['creado_por' => $nota->creado_por];

        if ( $accion == 'crear')
        {
            // Almacenar registro de abono
            CxpAbono::create( $datos );
        }else{
            // Eliminar registro de abono
            CxpAbono::where( $datos )->delete();
        }
    }

    public function show($id)
    {
        $this->set_variables_globales();

        return redirect( 'compras/'.$id.'?id='.$this->app->id.'&id_modelo='.$this->modelo->id.'&id_transaccion='.$this->transaccion->id.'&vista=compras.notas_credito.show');
    }

    // ANULAR
    public function anular( $id )
    {
        $this->set_variables_globales();

        $nota = ComprasDocEncabezado::find( $id );

        $array_wheres = ['core_empresa_id'=>$nota->core_empresa_id, 
            'core_tipo_transaccion_id' => $nota->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $nota->core_tipo_doc_app_id,
            'consecutivo' => $nota->consecutivo];

        // 1ro. Actualizar campo de cantidad_devuelta en cada línea de registro de la factura de compras
        $factura = ComprasDocEncabezado::find( $nota->compras_doc_relacionado_id );
        $registros_factura = ComprasDocRegistro::where('compras_doc_encabezado_id', $factura->id)->get();
        foreach ($registros_factura as $linea)
        {
            $cantidad_nota = ComprasDocRegistro::where('compras_doc_encabezado_id',$nota->id)
                                                ->where('inv_producto_id',$linea->inv_producto_id)
                                                ->value('cantidad');

            $nueva_cantidad_devuelta = $linea->cantidad_devuelta + $cantidad_nota;
            $linea->update( [ 'cantidad_devuelta' => $nueva_cantidad_devuelta ] );
        }
                            

        // 2do. Anular documento asociado de inventarios
        InventarioController::anular_documento_inventarios( $nota->entrada_almacen_id );

        // 3ro. Borrar registros contables del documento
        ContabMovimiento::where($array_wheres)->delete();

        // 4to. Se actualiza el registro de la factura a la que afecto la nota en el movimimeto de cuentas por pagar
        // Se envía el valor en positivo para que sume al saldo pendiente y reste al valor abonado
        $this->actualizar_registro_pago( $nota->valor_total * -1, $factura, $nota, 'anular' );

        // 5to. Se elimina el movimiento de compras
        ComprasMovimiento::where($array_wheres)->delete();

        $modificado_por = Auth::user()->email;
        // 6to. Se marcan como anulados los registros del documento
        ComprasDocRegistro::where( 'compras_doc_encabezado_id', $nota->id )->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por] );

        // 6to. Se marca como anulado el documento
        $nota->update( [ 'estado' => 'Anulado', 'compras_doc_relacionado_id' => '0', 'entrada_almacen_id' => '0', 'modificado_por' => $modificado_por] );

        return redirect( 'compras/'.$id.'?id='.$this->app->id.'&id_modelo='.$this->modelo->id.'&id_transaccion='.$this->transaccion->id.'&vista=compras.notas_credito.show')->with('flash_message','Nota anulada correctamente.');
    }

}