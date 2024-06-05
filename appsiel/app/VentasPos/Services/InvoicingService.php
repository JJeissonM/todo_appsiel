<?php 

namespace App\VentasPos\Services;

use App\Core\EncabezadoDocumentoTransaccion;
use App\FacturacionElectronica\Services\DocumentHeaderService;
use App\Inventarios\Services\InvDocumentsService;
use App\Ventas\VtasMovimiento;
use App\VentasPos\DocRegistro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoicingService
{
    public function almacenar_factura_electronica( $request )
    {
        // Encabezado
        $doc_encabezado = (new DocumentHeaderService())->store_invoice( $request, 0 );

        $doc_encabezado->valor_total = $doc_encabezado->lineas_registros->sum('precio_total');
        $doc_encabezado->save();

        return $doc_encabezado;
    }

    public function almacenar_remision_factura( $encabezado_factura, $inv_bodega_id )
    {
        $obj_inv_serv = new InventoriesServices();
        $doc_remision = $obj_inv_serv->create_delivery_note_from_invoice( $encabezado_factura, $inv_bodega_id ); // Sin contabilizar

        $obj_inv_doc_serv = new InvDocumentsService();
        $obj_inv_doc_serv->store_accounting_doc_head( $doc_remision->id, '' );

        return $doc_remision;
    }

    public function almacenar_factura_pos( $request )
    {
        $lineas_registros = json_decode($request->lineas_registros);

        // Encabezado
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );

        $doc_encabezado = $encabezado_documento->crear_nuevo( $request->all() );

        // Lineas de registros
        $this->crear_registros_documento_pos($request, $doc_encabezado, $lineas_registros);  
        
        $doc_encabezado->valor_total = $doc_encabezado->lineas_registros->sum('precio_total');
        $doc_encabezado->save();

        // Movimiento
        $this->crear_movimiento_pos($doc_encabezado);

        $obj_acumm_serv = new AccumulationService( 0 );

        $obj_acumm_serv->accumulate_one_invoice($doc_encabezado->id);

        return $doc_encabezado;
    }

    public function crear_registros_documento_pos(Request $request, $doc_encabezado, array $lineas_registros)
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros

        $cantidad_registros = count($lineas_registros);

        for ($i = 0; $i < $cantidad_registros; $i++)
        {
            if ( (int)$lineas_registros[$i]->inv_producto_id == 0)
            {
                continue; // Evitar guardar registros con productos NO validos
            }
            
            $linea_datos = ['vtas_motivo_id' => (int)$request->inv_motivo_id] +
                            ['inv_producto_id' => (int)$lineas_registros[$i]->inv_producto_id] +
                            ['precio_unitario' => (float)$lineas_registros[$i]->precio_unitario] +
                            ['cantidad' => (float)$lineas_registros[$i]->cantidad] +
                            ['precio_total' => (float)$lineas_registros[$i]->precio_total] +
                            ['base_impuesto' => (float)$lineas_registros[$i]->base_impuesto] +
                            ['tasa_impuesto' => (float)$lineas_registros[$i]->tasa_impuesto] +
                            ['valor_impuesto' => (float)$lineas_registros[$i]->valor_impuesto] +
                            ['base_impuesto_total' => (float)$lineas_registros[$i]->base_impuesto_total] +
                            ['tasa_descuento' => (float)$lineas_registros[$i]->tasa_descuento] +
                            ['valor_total_descuento' => (float)$lineas_registros[$i]->valor_total_descuento] +
                            ['creado_por' => Auth::user()->email] +
                            ['estado' => 'Contabilizado'] +
                            ['vtas_pos_doc_encabezado_id' => $doc_encabezado->id];

            DocRegistro::create($linea_datos);
        }
    }

    public function crear_movimiento_pos($invoice)
    {
        $datos = $invoice->toArray();
        unset($datos['id']);
        
        $cliente = $invoice->cliente;

        $datos['zona_id'] = $cliente->zona_id;
        $datos['clase_cliente_id'] = $cliente->clase_cliente_id;
        $datos['equipo_ventas_id'] = $invoice->vendedor->equipo_ventas_id;
        $datos['estado'] = 'Contabilizado';

        $lineas_registros = $invoice->lineas_registros;
        foreach ($lineas_registros as $linea)
        {            
            // Movimiento de Ventas
            VtasMovimiento::create( $datos + $linea->toArray() );
        }
    }
}