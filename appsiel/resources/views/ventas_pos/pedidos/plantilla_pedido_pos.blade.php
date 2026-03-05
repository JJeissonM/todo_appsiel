<!DOCTYPE html>
<html>
<head>
    <title>
        <span>{{ $tipo_doc_app->descripcion }} {{ $tipo_doc_app->prefijo }}</span>
        <span class="lbl_consecutivo_doc_encabezado">{{ $datos_factura->lbl_consecutivo_doc_encabezado }}</span>
    </title>
    <style type="text/css">
        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: {{ config('ventas_pos.tamanio_fuente_factura') . 'px'  }};
        }

        @page {
          margin: 15px;
          size: {{ config('ventas_pos.ancho_formato_impresion') . 'in' }} 38.5in;
        }

        .lbl_doc_anulado{
            position: absolute;
            background-color: rgba(253, 1, 1, 0.33);
            width: 100%;
            top: 100px;
            transform: rotate(-45deg);
            text-align: center;
            font-size: 2em;
        }

        table {
            width:100%;
            border-collapse: collapse;
        }

        .table {
            width: 100%;
        }

        .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
            line-height: 1.42857143;
            vertical-align: top;
            border-top: 1px solid gray;
        }

        .table-bordered {
            border: 1px solid gray;
        }

        .table-bordered>tbody>tr>td, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>td, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>thead>tr>th {
            border: 1px solid gray;
        }
    </style>
</head>

@if($datos_factura->core_tipo_transaccion_id == '')
    <body>
@else
    <body onload="window.print()">
@endif
    <table border="0" style="margin-top: 0 !important;" width="100%">
        <tr>
            <td>
                <div class="headempresap" style="text-align: center;">
                    <br/>
                    <b>{{ $empresa->descripcion }}</b><br/>
                </div>
            </td>
        </tr>
        <tr>
            <td style="font-size: 15px;">
                <div class="headdocp" style="text-align: center;">
                    <b>
                        <div style="display: inline;" id="doc_encabezado_documento_transaccion_descripcion">{{ $tipo_doc_app->descripcion }}</div>
                        <br>
                        No.
                    </b>
                    {{ $tipo_doc_app->prefijo }}
                    <div style="display: inline;" id="doc_encabezado_documento_transaccion_prefijo_consecutivo" class="lbl_consecutivo_doc_encabezado">{{ $datos_factura->lbl_consecutivo_doc_encabezado }}</div>
                    <br>
                    <b>Fecha:</b> <div style="display: inline;" id="lbl_fecha">{{ $datos_factura->lbl_fecha }}</div>
                    &nbsp;&nbsp;&nbsp;
                    <b>Hora:</b> <div style="display: inline;" id="lbl_hora">{{ $datos_factura->lbl_hora }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="subheadp">
        <b>Cliente:</b> <div style="display: inline;" id="doc_encabezado_tercero_nombre_completo" class="lbl_cliente_descripcion">{{ $cliente->tercero->descripcion }}</div>
        <br>
        <b>Atendido por: &nbsp;&nbsp;</b>
        <div style="display: inline;" id="doc_encabezado_vendedor_descripcion" class="lbl_atendido_por">{{ $cliente->vendedor->tercero->descripcion }}</div>
        <br>
    </div>

    <table class="table table-bordered" style="width: 100%; font-size: 13px;" id="tabla_productos_facturados">
        {{ Form::bsTableHeader(['Producto','Cant. pedida','Despachada']) }}
        <tbody>
            {!! $datos_factura->lineas_registros !!}
        </tbody>
    </table>

    <br>
    <b>Cantidad de items&nbsp;:</b> <div style="display: inline;" id="cantidad_total_productos">{{ is_array($datos_factura->obj_lineas_registros) ? count($datos_factura->obj_lineas_registros) : 0 }}</div>
    <br>
    <b>Despachado por &nbsp;&nbsp;&nbsp;:</b> _____________________
    <br>
    <b>Detalle: &nbsp;&nbsp;</b> <div style="display: inline;" id="doc_encabezado_descripcion" class="lbl_descripcion_doc_encabezado">{{ $datos_factura->lbl_descripcion_doc_encabezado }}</div>
    <br>
    <b>Total pedido:</b> <div style="display: inline;" class="lbl_total_factura">{{ $datos_factura->lbl_total_factura }}</div>
    <br><br>

    <div style="inline-size: 100%; overflow-wrap: break-word;">
        <div id="lbl_creado_por_fecha_y_hora">{{ $datos_factura->lbl_creado_por_fecha_y_hora }}</div>
    </div>

    <script type="text/javascript">
        window.onkeydown = function( event ) {
            if ( event.keyCode == 81 ) {
                window.close();
            }
        };
    </script>
</body>
</html>
