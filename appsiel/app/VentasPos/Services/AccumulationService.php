<?php

namespace App\VentasPos\Services;

use App\Contabilidad\ContabMovimiento;

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
        $this->invoices = FacturaPos::where('pdv_id', $this->pos->id)
                                ->where('estado', 'Pendiente')
                                ->orderBy('fecha')
                                ->get();

        if (is_null($this->invoices)) {
            return false;
        }

        return true;
    }

    public function hacer_desarme_automatico( $detalle_operacion = '', $fecha = null )
    {
        $pdv_id = $this->pos->id;
        $bodega_default_id = $this->pos->bodega_default_id;
        
        if ( $fecha == null ) {
            $fecha = $this->pos->ultima_fecha_apertura();
        }
        
        $parametros_config_inventarios = config('inventarios');

        $obj_inv_doc_serv = new InventoriesServices();
        return $obj_inv_doc_serv->create_document_making( $pdv_id, $bodega_default_id, $fecha, $parametros_config_inventarios, $detalle_operacion );
    }

    public function hacer_preparaciones_recetas( $detalle_operacion = '', $fecha = null )
    {
        $pdv_id = $this->pos->id;
        $bodega_default_id = $this->pos->bodega_default_id;
        
        if ( $fecha == null ) {
            $fecha = $this->pos->ultima_fecha_apertura();
        }

        $parametros_config_inventarios = config('inventarios');

        $obj_inv_doc_serv = new RecipeServices();
        
        $cantidades_facturadas = $obj_inv_doc_serv->resumen_cantidades_facturadas($pdv_id);
        
        return $obj_inv_doc_serv->create_document_making( $cantidades_facturadas, $bodega_default_id, $fecha, $parametros_config_inventarios, $detalle_operacion );
    }

    /**
     * Acumular y Contabilizar una factura
    */
    public function accumulate_one_invoice($invoice_id)
    {
        $invoice = FacturaPos::find($invoice_id);

        if ($invoice->estado != 'Pendiente') {
            return 1;
        }

        if ( $invoice->core_tercero_id == 0 )
        {
            $invoice->core_tercero_id = $invoice->pdv->cliente->tercero->core_tercero_id;
            $invoice->cliente_id = $invoice->pdv->cliente->id;
            $invoice->save();

            $cliente = $invoice->pdv->cliente;
        }else{
            $cliente = $invoice->cliente;
        }

        // Crear remisión si no existe
        $bodega_default_id = $invoice->pdv->bodega_default_id;

        if ($invoice->remision_doc_encabezado_id == 0) {
            $obj_inv_serv = new InventoriesServices();
            $doc_remision = $obj_inv_serv->create_delivery_note_from_invoice( $invoice, $bodega_default_id );
            
            $invoice->remision_doc_encabezado_id = $doc_remision->id;
            $invoice->save();
        }

        $datos = $invoice->toArray();

        unset($datos['id']);
        
        $array_wheres = [
            [ 'core_tipo_transaccion_id','=',$invoice->core_tipo_transaccion_id],
            [ 'core_tipo_doc_app_id','=',$invoice->core_tipo_doc_app_id],
            [ 'consecutivo','=',$invoice->consecutivo]
        ];

        $datos['zona_id'] = $cliente->zona_id;
        $datos['clase_cliente_id'] = $cliente->clase_cliente_id;
        $datos['equipo_ventas_id'] = $invoice->vendedor->equipo_ventas_id;
        $datos['estado'] = 'Activo';
        $datos['inv_bodega_id'] = $bodega_default_id;

        //if ($this->is_pending_accounting($array_wheres)) {
            $lineas_registros = $invoice->lineas_registros;

            foreach ($lineas_registros as $linea)
            {
                if ($linea->estado == 'Acumulado') {
                    continue;
                }
                
                // Movimiento de Ventas
                VtasMovimiento::create( $datos + $linea->toArray() );
                
                $linea->estado = 'Acumulado';
                $linea->save();
            }
        //}       

        // Actualiza Movimiento POS
        $movim_pos = Movimiento::where($array_wheres)->get();
        foreach ($movim_pos as $mov_line) {
            $mov_line->estado = 'Acumulado';
            $mov_line->save();
        }                 

        $datos['estado'] = 'Activo';

        // Movimiento de Tesoreria ó CxC
        //if ($this->is_pending_registro_pago($invoice->forma_pago,$array_wheres)) {
            $this->crear_registro_pago( $invoice->forma_pago, $datos, $invoice->valor_total + $invoice->valor_ajuste_al_peso + $invoice->valor_total_bolsas, $invoice->descripcion);
        //}

        $invoice->estado = 'Acumulado';
        $invoice->save();

        //if ($this->is_pending_accounting($array_wheres)) {
            $this->accounting_one_invoice($invoice_id);
        //}

        return 1;
    }

    public function is_pending_mov_ventas($array_wheres)
    {
        $doc = VtasMovimiento::where($array_wheres)->get()->first();
        if ( $doc != null ) {
            return false;
        }

        return true;
    }

    public function is_pending_registro_pago($forma_pago,$array_wheres)
    {
        if ($forma_pago == 'credito') {
            $doc = DocumentosPendientes::where($array_wheres)->get()->first();
            if ( $doc != null ) {
                return false;
            }
        }

        if ($forma_pago == 'contado') {
            $doc = TesoMovimiento::where($array_wheres)->get()->first();
            if ( $doc != null ) {
                return false;
            }
        }

        return true;
    }

    public function is_pending_accounting($array_wheres)
    {
        $doc = ContabMovimiento::where($array_wheres)->get()->first();
        if ( $doc == null ) {
            return true;
        }

        return false;
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

            return true;
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

            if ( !is_null($lineas_recaudos) )
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

    public function accounting_one_invoice($invoice_id)
    {
        $invoice = FacturaPos::find($invoice_id);

        if( $invoice->estado == 'Contabilizado' )
        {
            // La factura se pudo haber acumulado (y no Contabilizado) en un proceso anterior que se haya "caido"
            return 0;
        }

        $detalle_operacion = 'Acumulación PDV: ' . $invoice->pdv->descripcion;
        

        if( $invoice->estado == 'Pendiente')
        {
            return 0;
        }

        $invoice_lines = $invoice->lineas_registros;
        $obj_sales_serv = new SalesServices();

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
        $movim_pos = Movimiento::where('core_tipo_transaccion_id', $invoice->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id', $invoice->core_tipo_doc_app_id)
                        ->where('consecutivo', $invoice->consecutivo)
                        ->get();
        foreach ($movim_pos as $mov_line) {
            $mov_line->estado = 'Contabilizado';
            $mov_line->save();
        }  

        // Contabilizar Caja y Bancos ó Cartera de clientes
        $datos = $invoice->toArray();
        $datos['estado'] = 'Activo';
        $obj_sales_serv->contabilizar_movimiento_debito( $invoice->forma_pago, $datos, $datos['valor_total'] + $datos['valor_ajuste_al_peso'] + $datos['valor_total_bolsas'], $detalle_operacion, $invoice->pdv->caja_default_id );

        // Inventarios (Inventarios y Costos)
        $obj_inv_doc_serv = new InvDocumentsService();
        $obj_inv_doc_serv->store_accounting_doc_head( $invoice->remision_doc_encabezado_id, $detalle_operacion );

        // Contabilizar redondeo
        if ($invoice->valor_ajuste_al_peso != 0)
        {
            $obj_accou_serv = new AccountingServices();

            if ($invoice->valor_ajuste_al_peso > 0) {
                $obj_accou_serv->contabilizar_registro($datos, (int)config('ventas_pos.cta_ingresos_redondeo'), 'Redondeo factura', 0, abs($invoice->valor_ajuste_al_peso), null, null);
            }else{
                $obj_accou_serv->contabilizar_registro($datos, (int)config('ventas_pos.cta_gastos_redondeo'), 'Redondeo factura', abs($invoice->valor_ajuste_al_peso), 0, null, null);
            }
        }

        // Contabilizar valor bolsas
        if ($invoice->valor_total_bolsas != 0)
        {
            $obj_accou_serv = new AccountingServices();

            $cta_ingresos_id = (int)config('ventas_pos.cta_ingresos_facturacion_bolsas');

            if ( $cta_ingresos_id != null && $cta_ingresos_id != 0 ) {
                $obj_accou_serv->contabilizar_registro($datos, $cta_ingresos_id, 'Cobro de bolsas en factura de ventas', 0, abs($invoice->valor_total_bolsas), null, null);
            }
        }

        // Actualizar encabezado de factura
        $invoice->estado = 'Contabilizado';
        $invoice->save();
    }
}
