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
use App\Inventarios\Services\InvDocumentsLinesService;
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

        if ( $doc_encabezado->condicion_pago == 'contado' ) {
            $saldo_pendiente = $doc_encabezado->valor_total + ComprasDocEncabezado::where([
                ['compras_doc_relacionado_id','=', $doc_encabezado->id]
            ])->sum('valor_total');
        }

        // Información de la Factura de compras
        $doc_registros = ComprasDocRegistro::get_registros_impresion( Input::get('factura_id') );
       
        $lista_campos = ModeloController::get_campos_modelo( $this->modelo,'','create');

        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$this->transaccion,$lista_campos,$cantidad_campos,'create',null);

        for ($i = 0; $i < $cantidad_campos; $i++) {
            if ($lista_campos[$i]['name'] == 'valor_total') {
                $lista_campos[$i]['value'] = $saldo_pendiente;
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

        if ($request->forma_pago == null) {
            $request['forma_pago'] = $factura->condicion_pago;
        }

        // Crear encabezado del documento de Compras (Nota Crédito por Valor)
        $request['compras_doc_relacionado_id'] = $factura->id; // Relacionar Nota con la Factura
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );

        $nota_credito = $encabezado_documento->crear_nuevo( $request->all() );

        // Crear líneas de registros del documento
        $this->crear_registros_nota_credito( $request, $nota_credito, $factura );

        return redirect('compras_notas_credito_valor/'.$nota_credito->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion);
    }
    
    /*
        Crea los registros, el movimiento y la contabilización de un documento. 
        Todas estas operaciones se crean juntas porque se almacenena en cada iteración de las lineas de registros
        No Devuelve nada
    */
    public function crear_registros_nota_credito( Request $request, $nota_credito, $factura )
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        $datos = $request->all();

        $this->crear_lineas_registros_compras( $datos, $nota_credito, $factura );

        return true;
    }

    // Se crean los registros con base en los registros de la devolución
    public function crear_lineas_registros_compras( $datos, $nota_credito, $factura )
    {
        $total_documento = 0;

        $registros_factura = $factura->lineas_registros;

        $inv_bodega_id = $factura->entrada_almacen->inv_bodega_id;

        foreach ($registros_factura as $linea_factura)
        {
            $porc_participacion = abs($linea_factura->precio_total) / $factura->valor_total;

            $precio_total = $nota_credito->valor_total * $porc_participacion * -1;

            $cantidad = 0;
            $total_base_impuesto = abs($precio_total);

            $tasa_impuesto = $linea_factura->tasa_impuesto;

            $precio_unitario = abs($precio_total);

            $valor_impuesto = abs($linea_factura->valor_impuesto);

            $linea_datos = [ 'inv_bodega_id' => $inv_bodega_id ] +
                            [ 'inv_motivo_id' => $linea_factura->inv_motivo_id ] +
                            [ 'inv_producto_id' => $linea_factura->inv_producto_id ] +
                            [ 'precio_unitario' => $precio_unitario ] +
                            [ 'cantidad' => $cantidad ] +
                            [ 'precio_total' => $precio_total ] +
                            [ 'base_impuesto' =>  $total_base_impuesto ] +
                            [ 'tasa_impuesto' => $tasa_impuesto ] +
                            [ 'valor_impuesto' => $valor_impuesto ] +
                            [ 'creado_por' => Auth::user()->email ] +
                            [ 'estado' => 'Activo' ];

            $datos['consecutivo'] = $nota_credito->consecutivo;
            ComprasMovimiento::create( 
                                    $datos +
                                    $linea_datos
                                );
            
            $linea_datos['cantidad'] = 1;
            ComprasDocRegistro::create( 
                                    $datos + 
                                    [ 'compras_doc_encabezado_id' => $nota_credito->id ] +
                                    $linea_datos
                                );

            // Contabilizar
            $detalle_operacion = $datos['descripcion'];

            NotaCreditoController::contabilizar_movimiento_credito( $datos + $linea_datos, $detalle_operacion );

            $total_documento += $precio_total;

        } // Fin por cada registro de la factura

        $nota_credito->valor_total = $precio_total;
        $nota_credito->save();
        
        // Un solo registro Debito
        NotaCreditoController::contabilizar_movimiento_debito( $nota_credito->forma_pago, $datos + $linea_datos, $total_documento, $detalle_operacion, $factura );

        // Actualizar registro del pago de la factura a la que afecta la nota
        if ($nota_credito->forma_pago == 'contado') {
            NotaCreditoController::actualizar_movimiento_tesoreria( $total_documento, $factura, $nota_credito, 'crear' ); 
        }else{
            NotaCreditoController::actualizar_registro_pago( $total_documento, $factura, $nota_credito, 'crear' ); 
        }

        $this->recostear_registros_entrada_almacen($factura, $total_documento, 'crear');

        return true;
    }

    public function recostear_registros_entrada_almacen($factura, $total_nota, $accion)
    {
        $porc_descuento = abs($total_nota) / $factura->valor_total;

        $entrada_almacen = InvDocEncabezado::find((int)$factura->entrada_almacen_id);

        $lineas_registros_entrada_almacen = $entrada_almacen->lineas_registros;

        $doc_line_service = new InvDocumentsLinesService();

        foreach ($lineas_registros_entrada_almacen as $linea_entrada_almacen) {

            if ($accion == 'crear') {
                $costo_unitario = $linea_entrada_almacen->costo_unitario * ( 1 - $porc_descuento);
            }else{
                $costo_unitario = $linea_entrada_almacen->costo_unitario * ( 1 + $porc_descuento);
            }            

            $doc_line_service->update_document_line($linea_entrada_almacen, $costo_unitario, $linea_entrada_almacen->cantidad);
        }
    }

    public function show($id)
    {
        $this->set_variables_globales();

        return redirect( 'compras/'.$id.'?id='.$this->app->id.'&id_modelo='.$this->modelo->id.'&id_transaccion='.$this->transaccion->id.'&vista=compras.notas_credito.por_valor.show');
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

        // 3ro. Borrar registros contables del documento
        ContabMovimiento::where($array_wheres)->delete();

        // 4to. Se actualiza el registro de la factura a la que afecto la nota en el movimimeto de cuentas por pagar o Tesoreria
        if ( $nota->forma_pago == 'contado') {
            NotaCreditoController::actualizar_movimiento_tesoreria( $nota->valor_total, $factura, $nota, 'anular' ); 
        }else{
            // Se envía el valor en positivo para que sume al saldo pendiente y reste al valor abonado
            NotaCreditoController::actualizar_registro_pago( $nota->valor_total * -1, $factura, $nota, 'anular' );
        }

        // 5to. Se elimina el movimiento de compras
        ComprasMovimiento::where($array_wheres)->delete();

        $modificado_por = Auth::user()->email;

        // 6to. Se marcan como anulados los registros del documento
        ComprasDocRegistro::where( 'compras_doc_encabezado_id', $nota->id )->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por] );

        // 6to. Se marca como anulado el documento
        $nota->update( [ 'estado' => 'Anulado', 'compras_doc_relacionado_id' => '0', 'entrada_almacen_id' => '0', 'modificado_por' => $modificado_por] );

        $this->recostear_registros_entrada_almacen($factura, $nota->valor_total, 'anular');

        return redirect( 'compras/'.$id.'?id='.$this->app->id.'&id_modelo='.$this->modelo->id.'&id_transaccion='.$this->transaccion->id.'&vista=compras.notas_credito.por_valor.show')->with('flash_message','Nota anulada correctamente.');
    }

}