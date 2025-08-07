<?php 

namespace App\VentasPos\Services;

use App\Contabilidad\ContabMovimiento;
use App\Core\Services\ResolucionFacturacionService;
use App\CxC\CxcAbono;
use App\CxC\CxcMovimiento;
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvGrupo;
use App\Inventarios\Services\InvDocumentsService;
use App\Sistema\Services\ModeloService;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMovimiento;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\ResolucionFacturacion;
use App\Ventas\Services\PrintServices;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasMovimiento;
use App\Ventas\VtasPedido;
use App\VentasPos\DocRegistro;
use App\VentasPos\FacturaPos;
use App\VentasPos\Movimiento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class FacturaPosService
{
    public function get_msj_resolucion_facturacion( $pdv )
    {
        $obj_resolucion_facturacion = $this->get_obj_resolucion_facturacion( $pdv );

        $msj_resolucion_facturacion = '';
        $status = 'success';
        
        if ( $obj_resolucion_facturacion->status != 'success' )
        {
            $msj_resolucion_facturacion = $obj_resolucion_facturacion->message;
            $status = $obj_resolucion_facturacion->status;
        }

        return (object)[
            'status' => $status,
            'message' => $msj_resolucion_facturacion
        ];
    }

    public function get_obj_resolucion_facturacion( $pdv )
    {
        return (new ResolucionFacturacionService())->validate_resolucion_facturacion($pdv->tipo_doc_app, $pdv->core_empresa_id);
    }

    public function ajustar_campos( $lista_campos, $pdv, $vendedor, $transaccion )
    {
        $cantidad_campos = count($lista_campos);

        $lista_campos = (new ModeloService())->personalizar_campos($transaccion->id, $transaccion, $lista_campos, $cantidad_campos, 'create', null);

        //Personalización de la lista de campos
        for ($i = 0; $i < $cantidad_campos; $i++)
        {
            switch ($lista_campos[$i]['name']) {

                case 'core_tipo_doc_app_id':
                    $lista_campos[$i]['opciones'] = [$pdv->tipo_doc_app_default_id => $pdv->tipo_doc_app->prefijo . " - " . $pdv->tipo_doc_app->descripcion];
                    break;

                case 'cliente_input':
                    $lista_campos[$i]['value'] = $pdv->cliente->tercero->descripcion;
                    break;

                case 'vendedor_id':
                    $lista_campos[$i]['value'] = [$vendedor->id];
                    break;

                case 'forma_pago':
                    $lista_campos[$i]['value'] = $pdv->cliente->forma_pago( date('Y-m-d') );
                    break;

                case 'fecha':
                    $lista_campos[$i]['value'] = $pdv->ultima_fecha_apertura();
                    break;

                case 'fecha_vencimiento':
                    $lista_campos[$i]['value'] = $pdv->cliente->fecha_vencimiento_pago( $pdv->ultima_fecha_apertura() );
                    break;

                case 'inv_bodega_id':
                    $lista_campos[$i]['value'] = $pdv->bodega_default_id;
                    break;
                            
                default:
                    # code...
                    break;
            }
        }

        return $lista_campos;
    }

    public function get_productos($pdv,$productos)
    {
        $items_en_lista_precios = ListaPrecioDetalle::where('lista_precios_id',$pdv->cliente->lista_precios_id)->get()->pluck('inv_producto_id')->toArray();

        $productosTemp = null;
        foreach ($productos as $pr)
        {
            $grupo_inventario = InvGrupo::find($pr->inv_grupo_id);

            if ( !$grupo_inventario->mostrar_en_pagina_web ) {
                continue;
            }

            if ((int)config('ventas_pos.mostrar_solo_items_con_precios_en_lista_cliente_default')) {
                if (!in_array($pr->id,$items_en_lista_precios)) {
                    continue;
                }
            }            
            
            if ( $grupo_inventario == null )
            {
                return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', 'El producto ' . $pr->descripcion . ' no tiene un grupo de inventario válido.' );
            }

            $pr->categoria = $grupo_inventario->descripcion;
            $productosTemp[$pr->categoria][] = $pr;
        }

        return $productosTemp;
    }

    public function generar_plantilla_factura($pdv, $empresa)
    {
        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id', $pdv->tipo_doc_app_default_id)->where('estado', 'Activo')->get()->last();

        if ( $pdv->direccion != '' )
        {
            $empresa->direccion1 = $pdv->direccion;
            $empresa->telefono1 = $pdv->telefono;
            $empresa->email = $pdv->email;
        }

        $etiquetas = (new PrintServices())->get_etiquetas();

        $plantilla_factura_pos_default = config('ventas_pos.plantilla_factura_pos_default');
        if ($pdv->plantilla_factura_pos_default != null && $pdv->plantilla_factura_pos_default != '') {
            $plantilla_factura_pos_default = $pdv->plantilla_factura_pos_default;
        }

        $datos_factura = (object)[
            'core_tipo_transaccion_id' => '',
            'lbl_consecutivo_doc_encabezado' => '',
            'lbl_fecha' => '',
            'lbl_hora' => '',
            'lbl_condicion_pago' => '',
            'lbl_fecha_vencimiento' => '',
            'lbl_descripcion_doc_encabezado' => '',
            'lbl_total_factura' => '',
            'lbl_total_propina' => '',
            'total_factura_mas_propina' => '',
            'lbl_total_datafono' => '',
            'total_factura_mas_datafono' => '',
            'lbl_ajuste_al_peso' => 0,
            'lbl_total_recibido' => 0,
            'lbl_total_cambio' => 0,
            'lbl_valor_total_bolsas' => 0,
            'lbl_creado_por_fecha_y_hora' => '',
            'lineas_registros' => '',
            'lineas_impuesto' => ''
        ];

        $cliente = $pdv->cliente;
        $tipo_doc_app = $pdv->tipo_doc_app;
        $pdv_descripcion = $pdv->descripcion;

        return View::make('ventas_pos.formatos_impresion.' . $plantilla_factura_pos_default, compact('empresa', 'resolucion', 'etiquetas', 'pdv_descripcion', 'cliente', 'tipo_doc_app', 'plantilla_factura_pos_default','datos_factura'))->render();
    } 

    public function get_parametros_complemento_JSPrintManager($pdv)
    {
        $usar_complemento_JSPrintManager = 0;

        if ($pdv->usar_complemento_JSPrintManager != null) {
            $usar_complemento_JSPrintManager = $pdv->usar_complemento_JSPrintManager;
        }

        return (object)[
            'usar_complemento_JSPrintManager' => $usar_complemento_JSPrintManager,
            'enviar_impresion_directamente_a_la_impresora' => $pdv->enviar_impresion_directamente_a_la_impresora,
            'impresora_cocina_por_defecto' => $pdv->impresora_cocina_por_defecto,
            'impresora_principal_por_defecto' => $pdv->impresora_principal_por_defecto,
            'imprimir_factura_automaticamente' => $pdv->imprimir_factura_automaticamente,
        ];
    }

    public function get_resolucion_facturacion_electronica()
    {
        $resolucion_facturacion_electronica = null;
        if ( (int)config('ventas_pos.modulo_fe_activo') )
        {
            $resolucion_facturacion_electronica = ResolucionFacturacion::where([
                    ['tipo_doc_app_id', '=', config('facturacion_electronica.document_type_id_default')],
                    ['estado', '=', 'Activo']
                ])
                                    ->get()
                                    ->last();
        }

        return $resolucion_facturacion_electronica;
    }

    public function verificar_datos_por_defecto( $pdv )
    {
        if ( is_null( $pdv->cliente ) ) {
            return 'El punto de ventas NO tiene asociado un Cliente por defecto.';
        }

        if ( is_null( $pdv->bodega ) ) {
            return 'El punto de ventas NO tiene asociada una Bodega por defecto.';
        }

        if ( is_null( $pdv->caja ) ) {
            return 'El punto de ventas NO tiene asociada una Caja por defecto.';
        }

        if ( is_null( $pdv->cajero ) ) {
            return 'El punto de ventas NO tiene asociado un Cajero por defecto.';
        }

        if ( is_null( $pdv->tipo_doc_app ) ) {
            return 'El punto de ventas NO tiene asociado un Tipo de documento por defecto.';
        }

        return 'ok';
    }

    public function get_motivos_tesoreria()
    {
        $vec['']='';

        $motivo = TesoMotivo::find((int)config('tesoreria.motivo_tesoreria_ventas_contado'));
        if ( $motivo != null ) {
            $vec[$motivo->id] = $motivo->descripcion;
        }

        $motivo = TesoMotivo::find((int)config('ventas_pos.motivo_tesoreria_propinas'));
        if ( $motivo != null ) {
            $vec[$motivo->id] = $motivo->descripcion;
        }

        $motivo = TesoMotivo::find((int)config('ventas_pos.motivo_tesoreria_datafono'));
        if ( $motivo != null ) {
            $vec[$motivo->id] = $motivo->descripcion;
        }

        return $vec;
        
    }

    public function get_precio_bolsa( $lista_precios_id )
    {
        return ListaPrecioDetalle::get_precio_producto( $lista_precios_id, date('Y-m-d'),  (int)config('ventas_pos.item_bolsa_id'));
    }

    public function anular_factura_contabilizada( int $factura_id, bool $anular_remision )
    {
        $factura = FacturaPos::find($factura_id);

        $array_wheres = [
            'core_empresa_id' => $factura->core_empresa_id,
            'core_tipo_transaccion_id' => $factura->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $factura->core_tipo_doc_app_id,
            'consecutivo' => $factura->consecutivo
        ];

        $modificado_por = Auth::user()->email;

        // 1ro. Anular documento asociado de inventarios
        // Obtener las remisiones relacionadas con la factura y anularlas o dejarlas en estado Pendiente
        $ids_documentos_relacionados = explode(',', $factura->remision_doc_encabezado_id);
        $cant_registros = count($ids_documentos_relacionados);
        for ($i = 0; $i < $cant_registros; $i++) {
            $remision = InvDocEncabezado::find($ids_documentos_relacionados[$i]);
            if (!is_null($remision)) {
                if ($anular_remision) // anular_remision es tipo boolean
                {
                    (new InvDocumentsService())->anular_documento_inventarios($remision->id);
                } else {
                    $remision->update(['estado' => 'Pendiente', 'modificado_por' => $modificado_por]);
                }
            }
        }

        // 2do. Borrar registros contables del documento
        ContabMovimiento::where($array_wheres)->delete();

        // 3ro. Se elimina el documento del movimimeto de cuentas por cobrar y de tesorería
        CxcMovimiento::where($array_wheres)->delete();
        TesoMovimiento::where($array_wheres)->delete();

        // 4to. Se elimina el movimiento de ventas POS y Ventas Estándar
        Movimiento::where($array_wheres)->delete();
        VtasMovimiento::where($array_wheres)->delete();

        // 5to. Se marcan como anulados los registros del documento
        DocRegistro::where('vtas_pos_doc_encabezado_id', $factura->id)->update(['estado' => 'Anulado', 'modificado_por' => $modificado_por]);

        // Si la factura se hizo desde un pedido
        $pedido = VtasDocEncabezado::where( 'ventas_doc_relacionado_id' , $factura->id )->get()->first();
        if( $pedido != null )
        {
            if ((int)config('ventas_pos.agrupar_pedidos_por_cliente') == 1) {
                $todos_los_pedidos = $this->get_todos_los_pedidos_mesero_para_la_mesa($pedido);

                foreach ($todos_los_pedidos as $un_pedido) {
                    $un_pedido->estado = "Pendiente";
                    $un_pedido->ventas_doc_relacionado_id = 0;
                    $un_pedido->save();

                    $this->actualizar_cantidades_pendientes( $un_pedido, 'sumar' );
                }
            }else{
                $pedido->estado = "Pendiente";
                $pedido->ventas_doc_relacionado_id = 0;
                $pedido->save();

                $this->actualizar_cantidades_pendientes( $pedido, 'sumar' );
            }
        }

        // 6to. Se marca como anulado el documento
        $factura->update(['estado' => 'Anulado', 'remision_doc_encabezado_id' => '', 'modificado_por' => $modificado_por]);
    }

    public function factura_tiene_abonos_cxc( $factura_id )
    {
        $factura = FacturaPos::find( $factura_id );

        // Verificar si la factura tiene abonos, si tiene no se puede eliminar
        $cantidad = CxcAbono::where('doc_cxc_transacc_id', $factura->core_tipo_transaccion_id)
            ->where('doc_cxc_tipo_doc_id', $factura->core_tipo_doc_app_id)
            ->where('doc_cxc_consecutivo', $factura->consecutivo)
            ->count();

        if ( $cantidad != 0 ) {
            return (object)[
                'status' => true,
                'message' => 'Factura NO puede ser eliminada. Se le han hecho Recaudos de CXC (Tesorería).'
            ];
        }
        
        return (object)[
            'status' => false,
            'message' => null
        ];
    }

    public function actualizar_cantidades_pendientes( $encabezado_pedido, $operacion )
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
}