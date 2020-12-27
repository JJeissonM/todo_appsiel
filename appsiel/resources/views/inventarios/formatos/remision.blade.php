<!DOCTYPE html>
<html>
<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    <style type="text/css">
        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
        }

        .page-break {
            page-break-after: always;
        }

        table{
            width: 100%;
            border-collapse: collapse;
        }

        table.table-bordered, .table-bordered>tbody>tr>td{
            border: 1px solid #ddd;
        }

        table.table-bordered, .table-bordered>tfoot>tr>td{
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>

    <table class="table table-bordered">
        <tr>
            <td style="border: solid 1px #ddd; margin-top: -40px;" colspan="3" width="70%">
                    @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir' ] )
            </td>
            <td style="border: solid 1px #ddd; padding-top: -20px;" colspan="2">

                <b style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado->documento_transaccion_descripcion }}</b>
                    <br/>
                    <b>Documento:</b> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
                    <br/>
                    <b>Fecha:</b> {{ $doc_encabezado->fecha }}
                
                @if($doc_encabezado->estado == 'Anulado')
                    <br><br>
                    <div style="background-color: #ddd;">
                        <strong>Documento Anulado</strong>
                    </div>
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="5">
                <b>Tercero:</b> {{ $doc_encabezado->tercero_nombre_completo }}
                <br/>
                <b>NIT: &nbsp;&nbsp;</b> {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}
            </td>
        </tr>
        <tr>        
            <td colspan="5">
                <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
            </td>
        </tr>
        <tr style="text-align: center; background-color: #ddd; font-weight: bolder;">
            <td>No.</td>
            <td>Cód.</td>
            <td>Producto</td>
            <td>U.M.</td>
            <td>Cantidad</td>
        </tr>

            <?php
                $total_cantidad = 0;
                $numero = 1;
            ?>
            @foreach($doc_registros as $linea )
                <tr>
                    <td style="text-align: center;"> {{ $numero }} </td>
                    <td style="text-align: center;"> {{ $linea->producto_id }} </td>
                    <td> {{ $linea->producto_descripcion }} </td>
                    <td style="text-align: center;"> {{ $linea->unidad_medida1 }} </td>
                    <td style="text-align: right;"> {{ number_format( abs($linea->cantidad), 2, ',', '.') }} </td>
                </tr>
                <?php 
                    $total_cantidad += $linea->cantidad;
                    $numero++;
                ?>
            @endforeach
            <tr>
                <td colspan="4">&nbsp;</td>
                <td style="text-align: right;"> {{ number_format( abs($total_cantidad), 2, ',', '.') }} </td>
            </tr>
    </table>

    <br/><br/><br/><br/>

    <table border="0">
        <tr>
            <td width="50px"> &nbsp; </td>
            <td align="center"  style="border-bottom: 1px solid;"> </td>
            <td align="center"> &nbsp;  </td>
            <td align="center" style="border-bottom: 1px solid;"> </td>
            <td width="50px">&nbsp;</td>
        </tr>
        <tr>
            <td width="50px"> &nbsp; </td>
            <td align="center"> Elaboró: {{ explode('@',$doc_encabezado->creado_por)[0] }} </td>
            <td align="center"> &nbsp;  </td>
            <td align="center"> Recibe </td>
            <td width="50px">&nbsp;</td>
        </tr>
    </table>

</body>
</html>