<?php

namespace App\Http\Controllers\VentasPos;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Core\TransaccionController;

use App\VentasPos\Services\AccumulationService;

use App\VentasPos\FacturaPos;

use App\Ventas\VtasPedido;

use App\Ventas\VtasDocEncabezado;

use App\FacturacionElectronica\Factura;
use App\FacturacionElectronica\Services\DocumentHeaderService;

use App\VentasPos\Services\InvoicingService;

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

        //$request['core_tipo_transaccion_id'] = config('facturacion_electronica.transaction_type_id_default');
        //$request['core_tipo_doc_app_id'] = config('facturacion_electronica.document_type_id_default');
        
        if ( !isset($request['creado_por']) ) {
            $request['creado_por'] = Auth::user()->email;
        }
        
        $request['estado'] = 'Pendiente';

        $factura_pos_encabezado = $invoice_service->almacenar_factura_pos( $request ); // Con su Remision
        
        $obj_acumm_serv = new AccumulationService( 0 );

        $obj_acumm_serv->accumulate_one_invoice( $factura_pos_encabezado->id );

        $doc_header_serv = new DocumentHeaderService();
        $result = $doc_header_serv->convert_to_electronic_invoice( $factura_pos_encabezado->id );

        if ( $request->pedido_id != 0) {
            $pedido = VtasPedido::find($request->pedido_id);
            if ( $pedido != null )
            {
                if ((int)config('ventas_pos.agrupar_pedidos_por_cliente') == 1) {
                    $todos_los_pedidos = $this->get_todos_los_pedidos_mesero_para_la_mesa($pedido);

                    foreach ($todos_los_pedidos as $un_pedido) {
                        $un_pedido->ventas_doc_relacionado_id = $factura_pos_encabezado->id;
                        $un_pedido->estado = 'Facturado';
                        $un_pedido->save(); 
                        
                        self::actualizar_cantidades_pendientes( $un_pedido, 'restar' );
                    }
                }else{
                    $pedido->ventas_doc_relacionado_id = $factura_pos_encabezado->id;
                    $pedido->estado = 'Facturado';
                    $pedido->save();
                    self::actualizar_cantidades_pendientes( $pedido, 'restar' );
                }
            }
        }

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
                                ['vendedor_id','=',$pedido->vendedor_id],
                                ['estado','=','Pendiente']
                            ]
                        )
                ->get();
    }

    
    public function convertir_en_factura_electronica($factura_pos_encabezado_id)
    {
        $factura_pos_encabezado = FacturaPos::find($factura_pos_encabezado_id);

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