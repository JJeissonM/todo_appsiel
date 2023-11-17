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
                    <b>Fecha:</b> <div id="lbl_fecha" style="display: inline;">{{$datos_factura->lbl_fecha}}</div> 
                    @if($datos_factura->lbl_hora != '')
                    / 
                    <b>Hora:</b> <div id="lbl_hora" style="display: inline;">{{$datos_factura->lbl_hora}}</div>
                    @endif
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
                    <b>Detalle: &nbsp;&nbsp;</b> <div class="lbl_descripcion_doc_encabezado" style="display: inline;"> {{$datos_factura->lbl_descripcion_doc_encabezado}} </div>
                </td>
            </tr>
        </table>        
    </div>

    <table style="width: 100%; font-size: {{ $tamanino_fuente_2 }};" id="tabla_productos_facturados">
        {{ Form::bsTableHeader(['Producto','Cant. (Precio)',config('ventas.etiqueta_impuesto_principal'),'Total']) }}
        <tbody>
            {!! $datos_factura->lineas_registros !!}
        </tbody>
    </table>

    @if( (int)config('ventas_pos.manejar_propinas') )
        @include('ventas_pos.formatos_impresion.plantilla_factura_default_tabla_totales_con_propina')
    @else
        @include('ventas_pos.formatos_impresion.plantilla_factura_default_tabla_totales')
    @endif    

    @include('ventas_pos.formatos_impresion.tabla_medios_pago')

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
            <tr>
                <td colspan="4">
                    Esta factura se asimila en todos sus efectos a una Letra de Cambio según Art. 774 del Código de Comercio.
                </td>
            </tr>

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
            <b> {!! $etiquetas['pie_pagina'] !!} </b>
                   
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