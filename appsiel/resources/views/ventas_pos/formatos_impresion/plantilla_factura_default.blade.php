<!DOCTYPE html>
<html>
<head>
    <title> {{ $tipo_doc_app->descripcion }} No. </title>

    <style type="text/css">
        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: {{ config('ventas_pos.tamanio_fuente_factura') . 'px'  }};
        }

        @page {
          margin: 15px;
          size: {{ config('ventas_pos.ancho_formato_impresion') . 'in' }} 38.5in;
        }

        .page-break {
            page-break-after: always;
        }

        .lbl_doc_anulado{
            background-color: rgba(253, 1, 1, 0.33);
            width: 100%;
            top: 300px;
            transform: rotate(-45deg);
            text-align: center;
            font-size: 2em;
        }

        
        table {
            width:100%;
            border-collapse: collapse;
        }

        .table-bordered {
            border: 1px solid #ddd;
        }

        .table-bordered>tbody>tr>td, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>td, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>thead>tr>th {
            border: 1px solid #ddd;
        }

        @yield('estilos_adicionales')
    </style>
</head>
@if($datos_factura->core_tipo_transaccion_id == '')
    <body>
@else
    <body onload="window.print()">
@endif
    <?php
        $tamanino_fuente_2 = '0.9em';
    ?>
    <table border="0" style="margin-top: 12px !important; font-size: 11px;" width="100%">
        <tr>
            @yield('columnas_encabezado')
        </tr>
    </table>

    @if( $etiquetas['encabezado'] != '')
        <table style="width: 100%; font-size: 12px;">
            <tr>
                <td style="border: solid 1px #ddd; text-align: center;">
                    <b> {!! $etiquetas['encabezado'] !!} </b> 
                </td>
            </tr>
        </table>
    @endif

    <table border="0" style="margin-top: 12px !important; font-size: {{ $tamanino_fuente_2 }};" width="100%">
            <tr>
                <td>
                    <b>{{ $tipo_doc_app->descripcion }} No.</b> 
                    @if( !is_null( $resolucion ) )
                        {{ $resolucion->prefijo }}
                    @else
                        {{ $tipo_doc_app->prefijo }}
                    @endif
                    <div class="lbl_consecutivo_doc_encabezado" style="display: inline;">{{$datos_factura->lbl_consecutivo_doc_encabezado}}</div>
                </td>
                <td>
                    <b>Fecha:</b> <div id="lbl_fecha" style="display: inline;">{{$datos_factura->lbl_fecha}}</div>&nbsp;<div id="lbl_hora" style="display: inline;">{{$datos_factura->lbl_hora}}</div>
                </td>
            </tr>

            <tr id="tr_fecha_vencimiento">
                <td colspan="2">
                    <b>Condición pago:</b> <div class="lbl_condicion_pago" style="display: inline;">{{$datos_factura->lbl_condicion_pago}}</div>
                    
                    @if($datos_factura->lbl_condicion_pago != 'contado')
                        <br>
                            <b>Fecha vencimiento:</b> <div class="lbl_fecha_vencimiento" style="display: inline;">{{$datos_factura->lbl_fecha_vencimiento}}</div>
                    @endif                    
                </td>
            </tr>

    </table>

    <?php

        $json_dataico = (object)[];
        $errores_einvoice = '';
        $resultado_envio = null;

        if($datos_factura->core_tipo_transaccion_id == 52)
        {
            switch( config('facturacion_electronica.proveedor_tecnologico_default') )
            {
                case 'DATAICO':
                    $encabezado_doc = \App\FacturacionElectronica\Factura::find( $doc_encabezado->id );

                    if( Input::get('id_transaccion') == 47 )
                    {
                        $encabezado_doc = \App\VentasPos\FacturaPos::find( $doc_encabezado->id );
                    }
                    
                    $object_dataico = (new \App\FacturacionElectronica\DATAICO\FacturaGeneral( $encabezado_doc, 'factura' ));
                    $json_dataico =  $object_dataico->get_einvoice_in_dataico();

                    $errores_einvoice =  $object_dataico->get_errores($json_dataico);
                break;

                case 'OSEI':
                    $resultado_envio = (new \App\FacturacionElectronica\Services\SendingResultServices())->get_sending_result( $doc_encabezado->id );

                    if( $resultado_envio != null )
                    {
                        $errores_einvoice = $resultado_envio->reglasNotificacionDIAN;
                    }
                break;
            }            
        }
    ?>

    @if ($errores_einvoice != '')
        <div style="background: #f15f5f;">
            <h4>Factura no fue Enviada hacia la DIAN.<smal>Click Aquí para enviarla nuevamente: <a href="{{url('/fe_factura/' . $doc_encabezado->id . '?id=21&id_modelo=244&id_transaccion=52') }}" target="_blank">ENVIAR</a></smal></h4>
            {{ $errores_einvoice }}
        </div>
    @endif
    
    <div class="lbl_doc_anulado" style="display: none;">
        Documento Anulado
    </div>

    <div style="border: solid 1px #ddd;">
        <table width="100%" style=" font-size: {{ $tamanino_fuente_2 }};">
            <tr>
                <td>
                    <b>Cliente:</b> <div class="lbl_cliente_descripcion" style="display: inline;"> {{ $cliente->tercero->descripcion }} </div> 
                    <br>
                    <b>{{ config("configuracion.tipo_identificador") }}/CC:</b> <div class="lbl_cliente_nit" style="display: inline;">
					@if( config("configuracion.tipo_identificador") == 'NIT') 
                    {{ number_format( $cliente->tercero->numero_identificacion, 0, ',', '.') }}	
                    @else {{ $cliente->tercero->numero_identificacion}} @endif </div> 
                    <br>
                    <b>Dirección:</b> <div class="lbl_cliente_direccion" style="display: inline;"> {{ $cliente->tercero->direccion1 }} </div>
                    <br>
                    <b>Teléfono:</b> <div class="lbl_cliente_telefono" style="display: inline;"> {{ $cliente->tercero->telefono1 }} </div>
                    <br>
                    <b>Atendido por: &nbsp;&nbsp;</b> <div class="lbl_atendido_por" style="display: inline;"> {{ $cliente->vendedor->tercero->descripcion }} </div>
                    <br>
                    <span style="font-size: 1.1em; font-weight: bold;">
                        <b>Detalle: &nbsp;&nbsp;</b> <div class="lbl_descripcion_doc_encabezado" style="display: inline;"> {{$datos_factura->lbl_descripcion_doc_encabezado}} </div>
                    </span>
                </td>
            </tr>
        </table>        
    </div>

    <?php
        $lbl_impuesto = config('ventas.etiqueta_impuesto_principal');
        $impuesto_impoconsumo_id = (int) config('contabilidad.impoconsumo_default_id');
        $tax_category_default = null;
        $tax_category_impoconsumo = null;

        foreach ($datos_factura->obj_lineas_registros as $linea) {
            if (!is_null($linea->impuesto)) {
                if ((int) $linea->impuesto_id === $impuesto_impoconsumo_id) {
                    $tax_category_impoconsumo = 'INC';
                    break;
                }

                if ($tax_category_default === null) {
                    $tax_category_default = $linea->impuesto->tax_category;
                }
            }
        }

        $lbl_impuesto = $tax_category_impoconsumo ?? $tax_category_default ?? $lbl_impuesto;
    ?>
    <table style="width: 100%; font-size: {{ $tamanino_fuente_2 }};" id="tabla_productos_facturados">
        {{ Form::bsTableHeader(['Producto (U.M.)','Cant. (Precio)','%'.$lbl_impuesto,'Total']) }}
        <tbody>
            {!! $datos_factura->lineas_registros !!}
        </tbody>
    </table>

    @if( (int)config('ventas_pos.manejar_propinas') || (int)config('ventas_pos.manejar_datafono') )
        @include('ventas_pos.formatos_impresion.plantilla_factura_default_tabla_totales_con_recargos')
    @else
        @include('ventas_pos.formatos_impresion.plantilla_factura_default_tabla_totales')
    @endif

    @if( (int)config('ventas_pos.mostrar_saldo_pendiente_cxc_al_imprimir') )        
        <div style="text-align: left; width: 100%; font-weight: bold; font-size: 12px; padding-top: 4px; padding-bottom: 4px; background: #ddd;">
            Saldo Cartera:
            <span id="lbl_saldo_pendiente_cxc" style="font-weight: bold;">${{ number_format( $datos_factura->saldo_pendiente_cxc,0,',','.') }}</span>
        </div>
        <br>
    @endif

    @include('ventas_pos.formatos_impresion.tabla_medios_pago')
    
    @if(isset($medios_pago))
        <div style="font-style: normal; font-weight: 100;">
            {!! $medios_pago !!}
        </div>    
    @endif

    @include('ventas_pos.formatos_impresion.tabla_resumen_impuestos')

    @if($datos_factura->lineas_impuesto != '')
        {!! $datos_factura->lineas_impuesto !!}
    @endif

    <table style="width: 100%; font-size: 11px;" class="table table-bordered">
        <tbody>
            <tr>
                <td colspan="4">
                    &nbsp;
                </td>
            </tr>
            @if( !is_null($resolucion) ) 
                <tr>
                    <td colspan="4">
                        Factura {{ $resolucion->tipo_solicitud }} por la DIAN. Resolución No. {{ $resolucion->numero_resolucion }} del {{ $resolucion->fecha_expedicion }}. Prefijo {{ $resolucion->prefijo }} desde {{ $resolucion->numero_fact_inicial }} hasta {{ $resolucion->numero_fact_final }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="box" style="inline-size: 100%; overflow-wrap: break-word;">
        @if($datos_factura->core_tipo_transaccion_id == 52)
            @include('ventas.formatos_impresion.datos_print_factura_electronica')
        @else
            <b> Firma del aceptante: </b> <br><br><br><br>
        @endif    
                
        @if( $etiquetas['pie_pagina'] != '')
            <br>
            <div style="width: 100%; text-align: justify; font-style: italic;">
                <b> {!! $etiquetas['pie_pagina'] !!} </b>
            </div>
        @endif
        <br>
        <div id="lbl_creado_por_fecha_y_hora"></div>{{$datos_factura->lbl_creado_por_fecha_y_hora}}
    </div>
        
    
    <br><br>

    @yield('pagina_adicional')

    <script type="text/javascript">
        window.onkeydown = function( event ) {
            // Si se presiona la tecla q (Quit)
            if ( event.keyCode == 81 )
            {
                window.close();
            }
        };
    </script>

</body>

</html>
