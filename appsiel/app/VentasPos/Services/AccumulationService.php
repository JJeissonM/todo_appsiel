<?php

namespace App\VentasPos\Services;

use App\Contabilidad\ContabMovimiento;
use Illuminate\Http\Request;

use Auth;

use App\VentasPos\Pdv;
use App\VentasPos\FacturaPos;
use App\VentasPos\Movimiento;

use App\VentasPos\Services\InventoriesServices;
use App\VentasPos\Services\SalesServices;

use App\Ventas\VtasMovimiento;

use App\CxC\DocumentosPendientes;
use App\Inventarios\Services\InvDocumentsService;
use App\Tesoreria\TesoMovimiento;

class AccumulationService
{
    public $pos;
    public $invoices;

    public function __construct(int $pos_id)
    {
        $this->pos = Pdv::find($pos_id);
    }

    public function thereis_documents()
    {
        $this->invoices = FacturaPos::where('pdv_id', $this->pos->id)->whereIn('estado', ['Pendiente', 'Acumulado'])->get();

        if (is_null($this->invoices)) {
            return false;
        }

        return true;
    }

    public function hacer_desarme_automatico()
    {
        $pdv_id = $this->pos->id;
        $bodega_default_id = $this->pos->bodega_default_id;
        $fecha = $this->pos->ultima_fecha_apertura();
        $parametros_config_inventarios = config('inventarios');

        $obj_inv_doc_serv = new InventoriesServices();
        return $obj_inv_doc_serv->create_document_making( $pdv_id, $bodega_default_id, $fecha, $parametros_config_inventarios );
    }

    public function hacer_preparaciones_recetas()
    {
        $pdv_id = $this->pos->id;
        $bodega_default_id = $this->pos->bodega_default_id;
        $fecha = $this->pos->ultima_fecha_apertura();
        $parametros_config_inventarios = config('inventarios');

        $obj_inv_doc_serv = new RecipeServices();
        return $obj_inv_doc_serv->create_document_making( $pdv_id, $bodega_default_id, $fecha, $parametros_config_inventarios );
    }

    public function accumulate_invoicing()
    {
        $invoices_heads = $this->invoices;
        
        $lote = uniqid();

        foreach ( $invoices_heads as $invoice )
        {
            if( $invoice->estado == 'Acumulado' )
            {
                // La factura se pudo haber acumulado (y no Contabilizado) en un proceso anterior que se haya "caido"
                continue;
            }

            if ( $invoice->core_tercero_id == 0 )
            {
                $invoice->core_tercero_id = $this->pos->cliente->tercero->id;
            }

            $cliente = $invoice->cliente;

            $lineas_registros = $invoice->lineas_registros;

            $obj_inv_serv = new InventoriesServices();
            $doc_remision = $obj_inv_serv->create_delivery_note_from_invoice( $invoice, $this->pos->bodega_default_id );

            $invoice->remision_doc_encabezado_id = $doc_remision->id;

            foreach ($lineas_registros as $linea)
            {
                if( $linea->estado == 'Acumulado' )
                {
                    // La línea se pudo haber acumulado (y no Contabilizado) en un proceso anterior que se haya "caido"
                    continue;
                }

                $datos = $invoice->toArray() + $linea->toArray();

                // Movimiento de Ventas
                $datos['zona_id'] = $cliente->zona_id;
                $datos['clase_cliente_id'] = $cliente->clase_cliente_id;
                $datos['equipo_ventas_id'] = $invoice->vendedor->equipo_ventas_id;
                $datos['detalle'] = $lote;
                $datos['estado'] = 'Activo';

                VtasMovimiento::create( $datos );

                $linea->estado = 'Acumulado';
                $linea->save();
            }
            
            // Actualiza Movimiento POS
            Movimiento::where('core_tipo_transaccion_id', $invoice->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id', $invoice->core_tipo_doc_app_id)
                        ->where('consecutivo', $invoice->consecutivo)
                        ->update(['estado' => 'Acumulado']);

            // Movimiento de Tesoreria ó CxC
            $datos['estado'] = 'Activo';
            $this->crear_registro_pago( $invoice->forma_pago, $datos, $invoice->valor_total, $invoice->descripcion);

            $invoice->estado = 'Acumulado';
            $invoice->lote_acumulacion = $lote;
            $invoice->save();
        }
    }

    public function accumulate_one_invoice($invoice_id)
    {
        $invoice = FacturaPos::find($invoice_id);

        if( !$this->is_pending($invoice) )
        {
            return 0;
        }

        if ( $invoice->core_tercero_id == 0 )
        {
            $invoice->core_tercero_id = $invoice->pdv->cliente->tercero->core_tercero_id;
            $invoice->cliente_id = $invoice->pdv->cliente->id;
            $cliente = $invoice->pdv->cliente;
        }else{
            $cliente = $invoice->cliente;
        }        

        $lineas_registros = $invoice->lineas_registros;

        $obj_inv_serv = new InventoriesServices();
        $doc_remision = $obj_inv_serv->create_delivery_note_from_invoice( $invoice, $invoice->pdv->bodega_default_id );

        $invoice->remision_doc_encabezado_id = $doc_remision->id;

        foreach ($lineas_registros as $linea)
        {
            if( $linea->estado == 'Acumulado' )
            {
                // La línea se pudo haber acumulado (y no Contabilizado) en un proceso anterior que se haya "caido"
                continue;
            }

            $datos = $invoice->toArray() + $linea->toArray();

            // Movimiento de Ventas
            $datos['zona_id'] = $cliente->zona_id;
            $datos['clase_cliente_id'] = $cliente->clase_cliente_id;
            $datos['equipo_ventas_id'] = $invoice->vendedor->equipo_ventas_id;
            $datos['estado'] = 'Activo';

            VtasMovimiento::create( $datos );

            $linea->estado = 'Acumulado';
            $linea->save();
        }
        
        // Actualiza Movimiento POS
        Movimiento::where('core_tipo_transaccion_id', $invoice->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id', $invoice->core_tipo_doc_app_id)
                    ->where('consecutivo', $invoice->consecutivo)
                    ->update(['estado' => 'Acumulado']);

        // Movimiento de Tesoreria ó CxC
        $datos['estado'] = 'Activo';
        $this->crear_registro_pago( $invoice->forma_pago, $datos, $invoice->valor_total, $invoice->descripcion);

        $invoice->estado = 'Acumulado';
        $invoice->save();

        $this->accounting_one_invoice($invoice_id);

        return 1;
    }

    public function is_pending($invoice)
    {
        if( $invoice->estado != 'Pendiente' )
        {
            // La factura se pudo haber acumulado (y no Contabilizado) en un proceso anterior que se haya "caido"
            return false;
        }
        
        $sales_movement = VtasMovimiento::where([
            ['core_tipo_transaccion_id','=',$invoice->core_tipo_transaccion_id],
            ['core_tipo_doc_app_id','=',$invoice->core_tipo_doc_app_id],
            ['consecutivo','=',$invoice->consecutivo]
        ])->get()->first();

        if( $sales_movement != null )
        {
            return false;
        }
        
        $contab_movement = ContabMovimiento::where([
            ['core_tipo_transaccion_id','=',$invoice->core_tipo_transaccion_id],
            ['core_tipo_doc_app_id','=',$invoice->core_tipo_doc_app_id],
            ['consecutivo','=',$invoice->consecutivo]
        ])->get()->first();

        if( $contab_movement != null )
        {
            return false;
        }
        
        return true;
    }

    public function crear_registro_pago( $forma_pago, $datos, $total_documento )
    {
        // Cargar la cuenta por cobrar (CxC)
        if ($forma_pago == 'credito') {
            $datos['modelo_referencia_tercero_index'] = 'App\Ventas\Cliente';
            $datos['referencia_tercero_id'] = $datos['cliente_id'];
            $datos['valor_documento'] = $total_documento;
            $datos['valor_pagado'] = 0;
            $datos['saldo_pendiente'] = $total_documento;
            $datos['estado'] = 'Pendiente';
            DocumentosPendientes::create($datos);
        }

        // Agregar el movimiento a tesorería
        if ($forma_pago == 'contado')
        {
            /*
                lineas_registros_medios_recaudos =  esta variable es un campo de vtas_pos_doc_encabezados
            */
            if (!isset($datos['lineas_registros_medios_recaudos'])) {
                $datos['lineas_registros_medios_recaudos'] = '[{"teso_medio_recaudo_id":"1-Efectivo","teso_motivo_id":"1-Recaudo clientes","teso_caja_id":"1-Caja general","teso_cuenta_bancaria_id":"0-","valor":"$'.$total_documento.'"}]';
            }
            
            $lineas_recaudos = json_decode($datos['lineas_registros_medios_recaudos']);

            if ( !is_null($lineas_recaudos) ) //&& $datos['lineas_registros_medios_recaudos'] != '' )
            {
                foreach ($lineas_recaudos as $linea)
                {
                    $datos['teso_motivo_id'] = explode("-", $linea->teso_motivo_id)[0];
                    $datos['teso_caja_id'] = explode("-", $linea->teso_caja_id)[0];
                    $datos['teso_cuenta_bancaria_id'] = explode("-", $linea->teso_cuenta_bancaria_id)[0];
                    $datos['teso_medio_recaudo_id'] = explode("-", $linea->teso_medio_recaudo_id)[0];
                    $datos['valor_movimiento'] = (float)substr($linea->valor, 1);
                    TesoMovimiento::create($datos);
                }
            }
        }
    }    

    public function store_accounting()
    {
        $invoices_heads = $this->invoices;

        $detalle_operacion = 'Acumulación PDV: ' . $this->pos->descripcion;
        
        $obj_sales_serv = new SalesServices();

        foreach ( $invoices_heads as $invoice_head )
        {
            if( $invoice_head->estado == 'Pendiente')
            {
                continue;
            }

            $invoice_lines = $invoice_head->lineas_registros;

            foreach ( $invoice_lines as $invoice_line )
            {
                if( $invoice_line->estado == 'Pendiente')
                {
                    continue;
                }

                $data_invoice_line = $invoice_head->toArray() + $invoice_line->toArray();

                $data_invoice_line['estado'] = 'Activo';

                $obj_sales_serv->contabilizar_movimiento_credito( $data_invoice_line, $detalle_operacion );

                $invoice_line->estado = 'Contabilizado';
                $invoice_line->save();
            }

            // Actualiza Movimiento POS
            Movimiento::where('core_tipo_transaccion_id', $invoice_head->core_tipo_transaccion_id)
                ->where('core_tipo_doc_app_id', $invoice_head->core_tipo_doc_app_id)
                ->where('consecutivo', $invoice_head->consecutivo)
                ->update(['estado' => 'Contabilizado']);

            // Contabilizar Caja y Bancos ó Cartera de clientes
            $datos = $invoice_head->toArray();
            $datos['estado'] = 'Activo';
            $obj_sales_serv->contabilizar_movimiento_debito( $invoice_head->forma_pago, $datos, $datos['valor_total'], $detalle_operacion, $this->pos->caja_default_id );

            // Inventarios (Inventarios y Costos)
            $obj_inv_doc_serv = new InvDocumentsService();
            $obj_inv_doc_serv->store_accounting_doc_head( $invoice_head->remision_doc_encabezado_id, $detalle_operacion );

            // Actualizar encabezado de factura
            $invoice_head->estado = 'Contabilizado';
            $invoice_head->save();
        }
    } 

    public function accounting_one_invoice($invoice_id)
    {
        $invoice = FacturaPos::find($invoice_id);

        if( $invoice->estado == 'Contabilizado' )
        {
            // La factura se pudo haber acumulado (y no Contabilizado) en un proceso anterior que se haya "caido"
            return 0;
        }

        $detalle_operacion = 'Acumulación PDV: ' . $invoice->pdv->descripcion;
        
        $obj_sales_serv = new SalesServices();

        if( $invoice->estado == 'Pendiente')
        {
            return 0;
        }

        $invoice_lines = $invoice->lineas_registros;

        foreach ( $invoice_lines as $invoice_line )
        {
            if( $invoice_line->estado == 'Pendiente')
            {
                continue;
            }

            $data_invoice_line = $invoice->toArray() + $invoice_line->toArray();

            $data_invoice_line['estado'] = 'Activo';

            $obj_sales_serv->contabilizar_movimiento_credito( $data_invoice_line, $detalle_operacion );

            $invoice_line->estado = 'Contabilizado';
            $invoice_line->save();
        }

        // Actualiza Movimiento POS
        Movimiento::where('core_tipo_transaccion_id', $invoice->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $invoice->core_tipo_doc_app_id)
            ->where('consecutivo', $invoice->consecutivo)
            ->update(['estado' => 'Contabilizado']);

        // Contabilizar Caja y Bancos ó Cartera de clientes
        $datos = $invoice->toArray();
        $datos['estado'] = 'Activo';
        $obj_sales_serv->contabilizar_movimiento_debito( $invoice->forma_pago, $datos, $datos['valor_total'], $detalle_operacion, $invoice->pdv->caja_default_id );

        // Inventarios (Inventarios y Costos)
        $obj_inv_doc_serv = new InvDocumentsService();
        $obj_inv_doc_serv->store_accounting_doc_head( $invoice->remision_doc_encabezado_id, $detalle_operacion );

        // Actualizar encabezado de factura
        $invoice->estado = 'Contabilizado';
        $invoice->save();
    }
}
