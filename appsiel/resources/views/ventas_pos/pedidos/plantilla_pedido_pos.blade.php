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
            <div style="text-align: center; font-size: 1.1em;">
                @if($empresa->razon_social != '')
                    <b>{{ $empresa->razon_social }} </b>
                    <br>
                @endif
                <span style="text-align: center; font-size: 0.8em;">
                    <b>{{ $empresa->nombre1 }} {{ $empresa->otros_nombres }} {{ $empresa->apellido1 }} {{ $empresa->apellido2 }}</b>
                    <br>
                    <b>{{ config("configuracion.tipo_identificador") }}:
                        @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format(     $empresa->numero_identificacion, 0, ',', '.') }}	@else {{ $empresa->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}</b><br/>
                </span>
                    {{ $empresa->direccion1 }} <br/>
                    Teléfono(s): {{ $empresa->telefono1 }}
                @if( $empresa->pagina_web != '' )
                    <br/>
                    <b style="color: blue; font-weight: bold;">{{ $empresa->pagina_web }}</b>
                @endif
                @if( $empresa->email != '' )
                    <br/>
                    <b>Email:</b> <b style="color: blue; font-weight: bold;">{{ $empresa->email }}</b><br/>
                @endif
            </div>
        </tr>
    </table>

    <table border="0" style="margin-top: 12px !important; font-size: {{ $tamanino_fuente_2 }};" width="100%">
            <tr>
                <td>
                    <b>{{ $tipo_doc_app->descripcion }} No. </b>
                        {{ $tipo_doc_app->prefijo }}
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
    </table>

    <div style="border: solid 1px #ddd;">
        <table width="100%" style=" font-size: {{ $tamanino_fuente_2 }};">
            <tr>
                <td>
                    <b>Cliente:</b> <div class="lbl_cliente_descripcion" style="display: inline;"> {{ $cliente->tercero->descripcion }} </div> 
                    <br>
                    <b>Atendido por: &nbsp;&nbsp;</b> <div class="lbl_atendido_por" style="display: inline;"> {{ $cliente->vendedor->tercero->descripcion }} </div>
                    <br>
                    <b>Detalle: &nbsp;&nbsp;</b> <div class="lbl_descripcion_doc_encabezado" style="display: inline;"> {{$datos_factura->lbl_descripcion_doc_encabezado}} </div>
                </td>
            </tr>
        </table>        
    </div>

    <table style="width: 100%; font-size: {{ $tamanino_fuente_2 }};" id="tabla_productos_facturados">
        {{ Form::bsTableHeader(['Producto','Cant. (Precio)','Total']) }}
        <tbody>
            {!! $datos_factura->lineas_registros !!}
        </tbody>
    </table>

    <table style="width: 100%; font-size: {{ $tamanino_fuente_2 }};" id="tabla_totales">
        <tbody>
            <tr style="font-weight: bold;">
                <td style="text-align: right;" id="tr_total_factura"> Total factura: </td>
                <td style="text-align: right;">
                    <div class="lbl_total_factura" style="display: inline; margin-right: 15px;">{{$datos_factura->lbl_total_factura}} </div>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="box" style="inline-size: 100%; overflow-wrap: break-word;">
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