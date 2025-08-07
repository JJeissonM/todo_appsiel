<?php 

namespace App\Inventarios\Services;

use App\Compras\ComprasDocEncabezado;
use Illuminate\Http\Request;

use App\Core\Transactions\Services\DocumentsService AS TransactionDocumentsService;

use App\Inventarios\Services\AccountingServices;

use App\Contabilidad\ContabMovimiento;
use App\Inventarios\InvProducto;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMotivo;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvCostoPromProducto;
use App\Inventarios\InvDocEncabezado;
use App\Nomina\OrdenDeTrabajo;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class InvDocumentsService
{
    public function store_document(Request $request, array $lineas_registros, $modelo_id)
    {
        $request['creado_por'] = Auth::user()->email;
        $obj_tran_docu_serv = new TransactionDocumentsService( $modelo_id );
        
        $doc_encabezado = $obj_tran_docu_serv->store_document_header( $request->all() );
        
        $this->store_document_lines( $request->all(), $doc_encabezado, $lineas_registros);

        $this->contabilizar( $doc_encabezado );
        
        return $doc_encabezado->id;
    }

    public function store_document_lines( $datos, $doc_encabezado, array $lineas_registros)
    {
        $cantidad_registros = count($lineas_registros);
        
        $average_cost_serv = new AverageCost();
        for ($i = 0; $i < $cantidad_registros; $i++)
        {
            $cantidad = (float)$lineas_registros[$i]->cantidad;
            $costo_total = (float)$lineas_registros[$i]->costo_total;

            $motivo = InvMotivo::find( (int)$lineas_registros[$i]->inv_motivo_id );

            // Cuando el motivo de la transacción es de salida, 
            // las cantidades y costos totales restan del movimiento ( negativo )
            if ( $motivo->movimiento == 'salida' )
            {
                $cantidad = (float)$lineas_registros[$i]->cantidad * -1;
                $costo_total = (float)$lineas_registros[$i]->costo_total * -1;
            }

            $linea_datos = 
                            ['inv_bodega_id' => (int)$lineas_registros[$i]->inv_bodega_id] +
                            ['inv_motivo_id' => (int)$lineas_registros[$i]->inv_motivo_id] +
                            ['inv_producto_id' => (int)$lineas_registros[$i]->inv_producto_id] +
                            ['costo_unitario' => (float)$lineas_registros[$i]->costo_unitario] +
                            ['cantidad' => $cantidad] +
                            ['costo_total' => $costo_total];

            InvDocRegistro::create(
                                    $datos +
                                    $linea_datos +
                                    ['inv_doc_encabezado_id' => $doc_encabezado->id]
                                );

            // Solo se almacena el movimiento para productos almacenables
            $tipo_producto = InvProducto::find($lineas_registros[$i]->inv_producto_id)->tipo;
            if ( $tipo_producto == 'producto' )
            {
                $datos['consecutivo'] = $doc_encabezado->consecutivo;
                InvMovimiento::create(
                                        $datos +
                                        $linea_datos +
                                        ['inv_doc_encabezado_id' => $doc_encabezado->id]
                                    );
                                    
                if ($motivo->movimiento == 'entrada')
                {
                    $costo_prom = $average_cost_serv->calculate_average_cost( (int)$lineas_registros[$i]->inv_bodega_id, (int)$lineas_registros[$i]->inv_producto_id, (float)$lineas_registros[$i]->costo_unitario, $datos['fecha'], $cantidad );
                    
                    // Actualizo/Almaceno el costo promedio
                    $average_cost_serv->set_costo_promedio( (int)$lineas_registros[$i]->inv_bodega_id, (int)$lineas_registros[$i]->inv_producto_id, $costo_prom);
                }
            }
        }
    }

    /*
        Cuentas de Inventarios vs Costo de ventas
        Aplica a productos almacenables
    */
    public function contabilizar( $encabezado_documento )
    {
        $lineas_registros = $encabezado_documento->lineas_registros;
        
        if( is_null($lineas_registros) )
        {
            return 0;
        }

        foreach ($lineas_registros as $linea)
        {
            if ( $linea->item->tipo != 'producto')
            {
                continue; // Si no es un producto, saltar la contabilización de abajo.
            }

            $datos = $encabezado_documento->toArray() + $linea->toArray();

            // Si el movimiento es de ENTRADA de inventarios, se DEBITA la cta. de inventarios vs la cta. contrapartida
            $valor_debito = abs( $linea->costo_total );
            $valor_credito = 0;

            // Si el movimiento es de SALIDA de inventarios, se ACREDITA la cta. de inventarios vs la cta. contrapartida
            if ( $linea->motivo->movimiento == 'salida') {
                $valor_debito = 0;
                $valor_credito = abs( $linea->costo_total );
            }        
            
            $cta_inventarios_id = $linea->item->grupo_inventario->cta_inventarios_id; // Dada por el Grupo de Inventarios
            $cta_contrapartida_id = $linea->motivo->cta_contrapartida_id; // Dada por el Motivo de Inventarios

            $this->contabilizar_registro( $datos, $cta_inventarios_id, $valor_debito, $valor_credito);
            // Se invierten los valores Débito y Crédito
            $this->contabilizar_registro( $datos, $cta_contrapartida_id, $valor_credito, $valor_debito);
        }
    }

    public function contabilizar_registro( $datos, $contab_cuenta_id, $valor_debito, $valor_credito )
    {
        ContabMovimiento::create(   
                                    $datos + 
                                    [ 'contab_cuenta_id' => $contab_cuenta_id ] +
                                    [ 'valor_debito' => $valor_debito] + 
                                    [ 'valor_credito' => ($valor_credito * -1) ] + 
                                    [ 'valor_saldo' => ( $valor_debito - $valor_credito ) ]
                                );
    }
    
    // Remision de ventas
    public function create_doc_delivery_note( $model_name, $datos, $inv_bodega_id, $estado )
    {
        // Llamar a los parámetros del archivo de configuración
        $parametros = config('ventas');
        $datos['core_tipo_transaccion_id'] = $parametros['rm_tipo_transaccion_id'];
        $datos['core_tipo_doc_app_id'] = $parametros['rm_tipo_doc_app_id'];
        
        $datos['inv_bodega_id'] = $inv_bodega_id;
        $datos['estado'] = $estado;
        $datos['creado_por'] = Auth::user()->email;
        
        $obj_tran_docu_serv = new TransactionDocumentsService( $model_name );        
        $delivery_note_header = $obj_tran_docu_serv->store_document_header( $datos );
        
        $this->store_delivery_note_lines( $datos, $delivery_note_header );

        return $delivery_note_header;
    }

    /*
        Nota los costos son llamados del costo promedio
    */
    public function store_delivery_note_lines( $data, $delivery_note_header )
    {
        $invoice_doc_lines = $data['invoice_doc_lines'];
        $inv_bodega_id = $data['inv_bodega_id'];

        foreach( $invoice_doc_lines AS $invoice_line )
        {
            if ( $invoice_line->cantidad == 0 )
            {
                continue;
            }
            
            if ( is_null( $invoice_line->item ) )
            {
                continue;
            }

            $costo_unitario = InvCostoPromProducto::get_costo_promedio(   $delivery_note_header->inv_bodega_id, $invoice_line->inv_producto_id );
            $cantidad = $invoice_line->cantidad * -1; // Salida de inventarios
            $costo_total = $cantidad * $costo_unitario;

            $delivery_note_line_data = $delivery_note_header->toArray();
            $delivery_note_line_data['inv_doc_encabezado_id'] = $delivery_note_header->id;
            $delivery_note_line_data['core_empresa_id'] = $delivery_note_header->core_empresa_id;
            $delivery_note_line_data['inv_bodega_id'] = $inv_bodega_id;
            $delivery_note_line_data['core_tercero_id'] = $delivery_note_header->core_tercero_id;

            $delivery_note_line_data[ 'inv_motivo_id' ] = $invoice_line->vtas_motivo_id; // Warning: $linea tiene un campo especifico
            $delivery_note_line_data[ 'inv_producto_id' ] = $invoice_line->inv_producto_id;
            $delivery_note_line_data[ 'costo_unitario' ] = $costo_unitario;
            $delivery_note_line_data[ 'cantidad' ] = $cantidad;
            $delivery_note_line_data[ 'costo_total' ] = $costo_total;

            InvDocRegistro::create( $delivery_note_line_data );

            if ( $invoice_line->item->tipo == 'producto')
            {
                InvMovimiento::create( $delivery_note_line_data );
            }  
        }
    }
    

    public static function contabilizar_registro_inv( $datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito )
    {
        ContabMovimiento::create( $datos + 
                            [ 'contab_cuenta_id' => $contab_cuenta_id ] +
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => $valor_debito] + 
                            [ 'valor_credito' => ($valor_credito * -1) ] + 
                            [ 'valor_saldo' => ( $valor_debito - $valor_credito ) ]
                        );
    }

    public function store_accounting_doc_head( $inv_doc_head_id, $detalle_operacion )
    {
        $inv_doc_head = InvDocEncabezado::find( $inv_doc_head_id );

        // Obtener líneas de registros del documento
        $registros_documento = [];
        if ( gettype($inv_doc_head) == "object" ) {
            $registros_documento = $inv_doc_head->lineas_registros;
        }


        $obj_accou_serv = new AccountingServices();
        foreach ($registros_documento as $linea)
        {
            $motivo = InvMotivo::find( $linea->inv_motivo_id );

            // Si el movimiento es de ENTRADA de inventarios, se DEBITA la cta. de inventarios vs la cta. contrapartida
            if ( $motivo->movimiento == 'entrada')
            {
                // Inventarios (DB)
                $cta_inventarios_id = InvProducto::get_cuenta_inventarios( $linea->inv_producto_id );
                $obj_accou_serv->contabilizar_registro( $inv_doc_head->toArray() + $linea->toArray(), $cta_inventarios_id, $detalle_operacion, abs($linea->costo_total), 0);
                
                // Cta. Contrapartida (CR)
                $cta_contrapartida_id = $motivo->cta_contrapartida_id;
                $obj_accou_serv->contabilizar_registro( $inv_doc_head->toArray() + $linea->toArray(), $cta_contrapartida_id, $detalle_operacion, 0, abs($linea->costo_total) );
            }

            // Si el movimiento es de SALIDA de inventarios, se ACREDITA la cta. de inventarios vs la cta. contrapartida
            if ( $motivo->movimiento == 'salida')
            {
                // Inventarios (CR)
                $cta_inventarios_id = InvProducto::get_cuenta_inventarios( $linea->inv_producto_id );
                $obj_accou_serv->contabilizar_registro( $inv_doc_head->toArray() + $linea->toArray(), $cta_inventarios_id, $detalle_operacion, 0, abs($linea->costo_total));
                
                // Cta. Contrapartida (DB)
                $cta_contrapartida_id = $motivo->cta_contrapartida_id;
                $obj_accou_serv->contabilizar_registro( $inv_doc_head->toArray() + $linea->toArray(), $cta_contrapartida_id, $detalle_operacion, abs($linea->costo_total), 0 );
            }
                
        }
    }    

    // Este método no hace validación de existencias
    // Dichas validaciones se debieron hacer antes.
    /**
     * Nota: Este método es el mismo que está en InventarioController. Queda pendiente refactorizar las clases que llaman al método anterior (el de InventarioController)
     */
    public function anular_documento_inventarios($doc_encabezado_id)
    {
        $documento = InvDocEncabezado::find($doc_encabezado_id);

        // Eliminar Movimineto contable
        ContabMovimiento::where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
            ->where('consecutivo', $documento->consecutivo)
            ->delete();

        // Eliminar movimiento de inventarios
        InvMovimiento::where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
            ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->where('consecutivo', $documento->consecutivo)
            ->delete();

        // Marcar registros del documento como anulados
        $registros = InvDocRegistro::where('inv_doc_encabezado_id', $documento->id)->get();
        
        // Calcular costos promedios de cada producto del documento, cuando el motivo del movimiento es de entrada
        $average_cost_serv = new AverageCost();
        foreach ($registros as $linea)
        {
            $motivo = InvMotivo::find($linea->inv_motivo_id);
            if ($motivo->movimiento == 'entrada')
            {
                // Se CALCULA el nuevo costo promedio del movimiento con el producto YA retirado
                $costo_prom = $average_cost_serv->calculate_average_cost($linea->inv_bodega_id, $linea->inv_producto_id, $linea->costo_unitario, $documento->fecha, $linea->cantidad);
                
                $this->actualizar_costo_promedio($linea->inv_bodega_id, $linea->inv_producto_id, $costo_prom, $documento->core_tipo_transaccion_id, $average_cost_serv);

                // Marcar cada registro del documento como Anulado
                $linea->update(['estado' => 'Anulado', 'modificado_por' => Auth::user()->email]);
            }
        }

        // Para una remisión de ventas, se activa nuevamente el pedido de ventas, si se generó con base en pedido
        if( $documento->core_tipo_transaccion_id == 24 ) 
        {
            $pedido = VtasDocEncabezado::find( $documento->vtas_doc_encabezado_origen_id );
            if( !is_null($pedido) )
            {

                $this->actualizar_cantidades_pendientes( $pedido, $documento, 'sumar' );

                $pedido->estado = "Pendiente";
                $pedido->save();

                $documento->vtas_doc_encabezado_origen_id = 0;
                $documento->save();
            }      
        }

        // Para una entrada de almacén, se activa nuevamente la orden de compras, si se generó con base en OC
        if( $documento->core_tipo_transaccion_id == 35 ) 
        {
            $orden_compra = ComprasDocEncabezado::where( 'entrada_almacen_id', $documento->id )->get()->first();
            if( !is_null($orden_compra) )
            {
                $orden_compra->entrada_almacen_id = 0;
                $orden_compra->estado = "Pendiente";
                $orden_compra->save();
            }       
        }

        // Si esta relacionado con una Orden de Trabajo
        if ( Schema::hasTable( 'nom_ordenes_de_trabajo' ) )
        {
            OrdenDeTrabajo::where( 'inv_doc_encabezado_id',$documento->id )->update(['inv_doc_encabezado_id'=>0]);
        }

        // Marcar documento como Anulado
        $documento->update(['estado' => 'Anulado', 'modificado_por' => Auth::user()->email]);
    }

    public function actualizar_costo_promedio($inv_bodega_id, $inv_producto_id, $costo_prom, $core_tipo_transaccion_id, $average_cost_serv)
    {
        $tipo_transferencia = 2;
        if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 1  )
        {
            // Actualizo/Almaceno el costo promedio
            $average_cost_serv->set_costo_promedio( $inv_bodega_id, $inv_producto_id, $costo_prom);
        }else{

            // Cuando no maneja costo promedio por bodegas (un solo costo para todo)

            // Solo se calcula costo promedio, si la entrada NO es por transferencia
            if ($core_tipo_transaccion_id != $tipo_transferencia) 
            {
                // Actualizo/Almaceno el costo promedio
                $average_cost_serv->set_costo_promedio( $inv_bodega_id, $inv_producto_id, $costo_prom);
            }
        }
    }

    public function actualizar_cantidades_pendientes( $encabezado_pedido, $encabezado_remision, $operacion )
    {
        $lineas_registros_remision = $encabezado_remision->lineas_registros;
        foreach( $lineas_registros_remision AS $linea_remision )
        {
            $linea_pedido = VtasDocRegistro::find( $linea_remision->linea_registro_doc_origen_id );
            
            if(is_null($linea_pedido) )
            {
                continue;
            }
            
            if ( $operacion == 'restar' )
            {
                $linea_pedido->cantidad_pendiente = $linea_pedido->cantidad_pendiente - abs($linea_remision->cantidad);
            }else{
                // sumar: al anular la remision
                $linea_pedido->cantidad_pendiente = $linea_pedido->cantidad_pendiente + abs($linea_remision->cantidad);
            }
                
            $linea_pedido->save();
        }
    }
}