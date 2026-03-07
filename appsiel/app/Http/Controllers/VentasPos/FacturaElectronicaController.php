<?php

namespace App\Http\Controllers\VentasPos;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Core\TransaccionController;

use App\VentasPos\Services\AccumulationService;

use App\VentasPos\FacturaPos;

use App\Ventas\VtasPedido;

use App\Ventas\VtasDocEncabezado;

use App\FacturacionElectronica\Factura;
use App\FacturacionElectronica\Services\DocumentHeaderService;
use App\VentasPos\Services\CxCService;
use App\VentasPos\Services\InvoicingService;
use App\VentasPos\Services\TreasuryService;

class FacturaElectronicaController extends TransaccionController
{
    protected $doc_encabezado;

    /**
     * ALMACENA FACTURA ELECTRONICA DESDE VENTAS POS - ES LLAMADO VÍA AJAX
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $invoice_service = new InvoicingService();
        
        if ( !isset($request['creado_por']) ) {
            $request['creado_por'] = Auth::user()->email;
        }
        
        $request['estado'] = 'Pendiente';
        
        $crear_cruce_con_anticipos = false;
        $crear_abonos = false; // Cuando es credito y se ingresa alguna línea de Medio de pago
        if ( $request->object_anticipos != 'null' && $request->object_anticipos != '' )
        {
            $request['forma_pago'] = 'credito'; // Si hay anticipos, se asume que es crédito

            $crear_cruce_con_anticipos = true; // Si hay anticipos, se crea el cruce con los anticipos
            $crear_abonos = true; // Si hay anticipos, se crean los abonos
        }
        $todos_los_pedidos = collect([]);
        $lineas_registros = json_decode($request->lineas_registros);

        DB::beginTransaction();
        try {
            if ((int)$request->pedido_id != 0) {
                $pedido = VtasPedido::where('id', (int)$request->pedido_id)->lockForUpdate()->first();

                if ( is_null($pedido) || $pedido->estado != 'Pendiente' || (int)$pedido->ventas_doc_relacionado_id != 0 ) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'warning',
                        'message' => 'El pedido seleccionado ya fue facturado o no está disponible. Actualice la lista de pendientes.'
                    ], 409);
                }

                if ((int)config('ventas_pos.agrupar_pedidos_por_cliente') == 1) {
                    $todos_los_pedidos = VtasPedido::where('cliente_id', $pedido->cliente_id)
                        ->where('estado', 'Pendiente')
                        ->where('ventas_doc_relacionado_id', 0)
                        ->whereIn('core_tipo_transaccion_id', [42, 60])
                        ->lockForUpdate()
                        ->get();

                    if ($todos_los_pedidos->isEmpty()) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'warning',
                            'message' => 'Los pedidos de esta mesa/cliente ya no están disponibles para facturar.'
                        ], 409);
                    }
                } else {
                    $todos_los_pedidos = collect([$pedido]);
                }

                $validar_cantidades = ((int)config('ventas_pos.agrupar_pedidos_por_cliente') != 1);
                if ( !$this->lineas_factura_corresponden_a_pedidos($lineas_registros, $todos_los_pedidos, $validar_cantidades) ) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'warning',
                        'message' => 'Los productos de la factura no corresponden a los pedido(s) cargado(s). Vuelva a cargar los pedidos.'
                    ], 409);
                }
            }

            $factura_pos_encabezado = $invoice_service->almacenar_factura_pos( $request ); // Con su Remision

            if ( $request->pedido_id != 0) {
                foreach ($todos_los_pedidos as $un_pedido) {
                    $un_pedido->ventas_doc_relacionado_id = $factura_pos_encabezado->id;
                    $un_pedido->estado = 'Facturado';
                    $this->guardar_pedido_sin_tocar_updated_at($un_pedido); 
                    
                    self::actualizar_cantidades_pendientes( $un_pedido, 'restar' );
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        // Acumular la factura ////////////////////
        $obj_acumm_serv = new AccumulationService( $factura_pos_encabezado->pdv_id );    
        
        // Realizar preparaciones de recetas
        $obj_acumm_serv->hacer_preparaciones_recetas( 'Creado por factura POS ' . $factura_pos_encabezado->get_label_documento(), $factura_pos_encabezado->fecha );

        // Realizar desarme automático
        $obj_acumm_serv->hacer_desarme_automatico( 'Creado por factura POS ' . $factura_pos_encabezado->get_label_documento(), $factura_pos_encabezado->fecha);

        $obj_acumm_serv->accumulate_one_invoice( $factura_pos_encabezado->id ); 
        ////////////////////////////////

        // Convertir a factura electrónica
        $doc_header_serv = new DocumentHeaderService();
        $result = $doc_header_serv->convert_to_electronic_invoice( $factura_pos_encabezado->id );

        // Enviar al proveedor tecnológico
        $vtas_document_header = Factura::find( (int)$result->new_document_header_id );
        
        $mensaje = $vtas_document_header->enviar_al_proveedor_tecnologico();
        $mensaje = $vtas_document_header->enviar_al_proveedor_tecnologico();

        if ( $mensaje->tipo != 'mensaje_error' )
        {
            $factura_pos_encabezado->estado = 'Enviada';
            $factura_pos_encabezado->save();
            
            $vtas_document_header->estado = 'Enviada';
            $vtas_document_header->save();
        }
        
        if( $crear_cruce_con_anticipos )
        {
            (new CxCService())->crear_cruce_con_anticipos( $vtas_document_header, $request->object_anticipos );
        }

        if ( $crear_abonos) {
            $datos = $factura_pos_encabezado->toArray();
            (new TreasuryService())->crear_abonos_documento( $vtas_document_header, $datos['lineas_registros_medios_recaudos'] );
        }

        $url_print = url('/') . '/vtas_imprimir/' . $result->new_document_header_id . '?id=21&id_modelo=244&id_transaccion=52&formato_impresion_id=pos';

        return $url_print;
    }

    /**
     * En uso
     */
    public static function actualizar_cantidades_pendientes( $encabezado_pedido, $operacion )
    {
        $lineas_registros_pedido = $encabezado_pedido->lineas_registros;
        foreach( $lineas_registros_pedido AS $linea_pedido )
        {
            if ( $operacion == 'restar' )
            {
                $linea_pedido->cantidad_pendiente = 0;
            }else{
                // sumar: al anular
                $linea_pedido->cantidad_pendiente = $linea_pedido->cantidad;
            }
                
            $linea_pedido->save();
        }
    }

    /**
     * En uso
     */
    public function get_todos_los_pedidos_mesero_para_la_mesa($pedido)
    {
        return VtasPedido::where(
                            [
                                ['cliente_id','=',$pedido->cliente_id],
                                ['estado','=','Pendiente']
                            ]
                        )
                ->where('ventas_doc_relacionado_id', 0)
                ->whereIn('core_tipo_transaccion_id', [42, 60])
                ->get();
    }

    protected function guardar_pedido_sin_tocar_updated_at($pedido)
    {
        $pedido->timestamps = false;
        $pedido->save();
        $pedido->timestamps = true;
    }

    protected function lineas_factura_corresponden_a_pedidos($lineas_registros, $todos_los_pedidos, $validar_cantidades = true)
    {
        $cantidades_pedido = [];
        foreach ($todos_los_pedidos as $un_pedido) {
            foreach ($un_pedido->lineas_registros as $linea_pedido) {
                $inv_producto_id = (int)$linea_pedido->inv_producto_id;
                $cantidad_pedido = (float)$linea_pedido->cantidad;
                if ($inv_producto_id <= 0 || $cantidad_pedido <= 0) {
                    continue;
                }

                if (!isset($cantidades_pedido[$inv_producto_id])) {
                    $cantidades_pedido[$inv_producto_id] = 0;
                }
                $cantidades_pedido[$inv_producto_id] += $cantidad_pedido;
            }
        }

        $cantidades_factura = [];
        foreach ($lineas_registros as $linea_factura) {
            if (!isset($linea_factura->inv_producto_id) || !isset($linea_factura->cantidad)) {
                continue;
            }

            $inv_producto_id = (int)$linea_factura->inv_producto_id;
            $cantidad_factura = (float)$linea_factura->cantidad;
            if ($inv_producto_id <= 0 || $cantidad_factura <= 0) {
                continue;
            }

            if (!isset($cantidades_factura[$inv_producto_id])) {
                $cantidades_factura[$inv_producto_id] = 0;
            }
            $cantidades_factura[$inv_producto_id] += $cantidad_factura;
        }

        foreach ($cantidades_factura as $inv_producto_id => $cantidad_factura) {
            if (!isset($cantidades_pedido[$inv_producto_id])) {
                return false;
            }

            if (!$validar_cantidades) {
                continue;
            }

            if ($cantidad_factura > ($cantidades_pedido[$inv_producto_id] + 0.0001)) {
                return false;
            }
        }

        return true;
    }

    
    public function convertir_en_factura_electronica($factura_pos_encabezado_id)
    {
        $factura_pos_encabezado = FacturaPos::find($factura_pos_encabezado_id);

        if ( $factura_pos_encabezado->cliente->tercero->tipo == 'Interno') {
            return url('/') . '/vtas_imprimir/' . $factura_pos_encabezado_id . '?id=20&id_modelo=244&id_transaccion=52&formato_impresion_id=pos';
        }

        $doc_header_serv = new DocumentHeaderService();
        $result = $doc_header_serv->convert_to_electronic_invoice( $factura_pos_encabezado->id );

        $mensaje = Factura::find((int)$result->new_document_header_id)->enviar_al_proveedor_tecnologico();
        
        if ( $mensaje->tipo != 'mensaje_error' )
        {
            $factura_pos_encabezado->estado = 'Enviada';
            $factura_pos_encabezado->save();
            
            $vtas_document_header = VtasDocEncabezado::find( (int)$result->new_document_header_id );
            $vtas_document_header->estado = 'Enviada';
            $vtas_document_header->save();
        }

        $url_print = url('/') . '/vtas_imprimir/' . $result->new_document_header_id . '?id=21&id_modelo=244&id_transaccion=52&formato_impresion_id=pos';

        return $url_print;
    }
}
