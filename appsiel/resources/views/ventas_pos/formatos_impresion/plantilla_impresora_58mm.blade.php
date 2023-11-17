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
          size: 2in 38.5in;
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
    </style>
</head>
<body>
    <?php
        $tamanino_fuente_2 = '0.7em';
        $url_img = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen;

        $ciudad = DB::table('core_ciudades')->where( 'id', $empresa->codigo_ciudad )->get()[0];
    ?>
    <table border="0" style="margin-top: 12px !important; font-size: 11px;" width="100%">
        <tr>
            <td style="text-align: center;">
                <img src="{{ $url_img }}" style="max-height: 110px; width: 100%;" />
                <br>
                @include('ventas_pos.formatos_impresion.datos_encabezado_factura')
            </td>
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
                    <div class="lbl_consecutivo_doc_encabezado" style="display: inline;"></div>
                    <br>
                    <b>Fecha:</b> <div id="lbl_fecha" style="display: inline;"></div> / 
                    <b>Hora:</b> <div id="lbl_hora" style="display: inline;"></div>
                </td>
            </tr>

            <tr id="tr_fecha_vencimiento" style="display: none;">
                <td>
                    <b>Condición pago:</b> <div class="lbl_condicion_pago" style="display: inline;"></div>
                    <br>
                    <b>Fecha vencimiento:</b> <div class="lbl_fecha_vencimiento" style="display: inline;"></div>
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
                </td>
            </tr>
            <tr>
                <td>
                    <b>Dirección:</b> <div class="lbl_cliente_direccion" style="display: inline;"> {{ $cliente->tercero->direccion1 }} </div>
                    <br>
                    <b>Teléfono:</b> <div class="lbl_cliente_telefono" style="display: inline;"> {{ $cliente->tercero->telefono1 }} </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <b>Atendido por: &nbsp;&nbsp;</b> <div class="lbl_atendido_por" style="display: inline;"> {{ $cliente->vendedor->tercero->descripcion }} </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <b>Detalle: &nbsp;&nbsp;</b> <div class="lbl_descripcion_doc_encabezado" style="display: inline;"> </div>
                </td>
            </tr>
        </table>        
    </div>

    <table  style="width: 100%; font-size: 11px;" id="tabla_productos_facturados">
        {{ Form::bsTableHeader(['Producto','Cant. (Precio)','Total']) }}
        <tbody>
        </tbody>
    </table>

    @if( (int)config('ventas_pos.manejar_propinas') )
        @include('ventas_pos.formatos_impresion.plantilla_factura_default_tabla_totales_con_propina')
    @else
        @include('ventas_pos.formatos_impresion.plantilla_factura_default_tabla_totales')
    @endif

    @include('ventas_pos.formatos_impresion.tabla_medios_pago')

    <table style="width: 100%; font-size: 11px; border-collapse: collapse;" class="table table-bordered">
        <thead>
            <tr>
                <th style="border: 1px solid;">Tipo Impuesto</th>
                <th style="border: 1px solid;">Vlr. Compra</th>
                <th style="border: 1px solid;">Base IVA</th>
                <th style="border: 1px solid;">Vlr. IVA</th>
            </tr>            
        </thead>
        <tbody>
            <tr>
                <td style="border: 1px solid;"> {{ config('ventas.etiqueta_impuesto_principal') }}=8% </td>
                <td style="border: 1px solid;"> <div class="lbl_total_factura" style="display: inline;"> </div> </td>
                <td style="border: 1px solid;"> <div class="lbl_base_impuesto_total" style="display: inline;"> </div> </td>
                <td style="border: 1px solid;"> <div class="lbl_valor_impuesto" style="display: inline;"> </div> </td>
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

    <table style="width: 100%; font-size: 11px;">
        <tr>
            <td style="border: solid 1px black;"> <b> Firma del aceptante: </b> <br><br><br><br> </td>
        </tr>
        @if( $etiquetas['pie_pagina'] != '')
            <tr>
                <td style="border: solid 1px #ddd; text-align: center; font-style: italic;">
                    <b> {!! $etiquetas['pie_pagina'] !!} </b>
                </td>
            </tr>
        @endif
        <tr>
            <td align="right"><div id="lbl_creado_por_fecha_y_hora"></div></td>
        </tr>
    </table>
    
    <br><br>

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