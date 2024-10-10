<?php 

namespace App\VentasPos\Services;

use App\Core\Services\ResolucionFacturacionService;
use App\Inventarios\InvGrupo;
use App\Sistema\Services\ModeloService;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\ResolucionFacturacion;
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

        $etiquetas = $this->get_etiquetas();

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
            'lbl_ajuste_al_peso' => '',
            'lbl_total_recibido' => '0',
            'lbl_total_cambio' => '',
            'lbl_creado_por_fecha_y_hora' => '',
            'lineas_registros' => '',
            'lineas_impuesto' => ''
        ];

        $cliente = $pdv->cliente;
        $tipo_doc_app = $pdv->tipo_doc_app;
        $pdv_descripcion = $pdv->descripcion;

        return View::make('ventas_pos.formatos_impresion.' . $plantilla_factura_pos_default, compact('empresa', 'resolucion', 'etiquetas', 'pdv_descripcion', 'cliente', 'tipo_doc_app', 'plantilla_factura_pos_default','datos_factura'))->render();
    }

    public function get_etiquetas()
    {
        $parametros = config('ventas');

        $encabezado = '';

        if ($parametros['encabezado_linea_1'] != '') {
            $encabezado .= $parametros['encabezado_linea_1'];
        }

        if ($parametros['encabezado_linea_2'] != '') {
            $encabezado .= '<br>' . $parametros['encabezado_linea_2'];
        }

        if ($parametros['encabezado_linea_3'] != '') {
            $encabezado .= '<br>' . $parametros['encabezado_linea_3'];
        }


        $pie_pagina = '';

        if ($parametros['pie_pagina_linea_1'] != '') {
            $pie_pagina .= $parametros['pie_pagina_linea_1'];
        }

        if ($parametros['pie_pagina_linea_2'] != '') {
            $pie_pagina .= '<br>' . $parametros['pie_pagina_linea_2'];
        }

        if ($parametros['pie_pagina_linea_3'] != '') {
            $pie_pagina .= '<br>' . $parametros['pie_pagina_linea_3'];
        }

        return ['encabezado' => $encabezado, 'pie_pagina' => $pie_pagina];
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
}