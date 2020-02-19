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
    </style>
</head>
<body>

    <table class="table table-bordered">
        <tr>
            <td style="border: solid 1px #ddd; margin-top: -40px;">
                    @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir' ] )
            </td>
            <td style="border: solid 1px #ddd; padding-top: -20px;">

                <b style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado->documento_transaccion_descripcion }}</b>

                <table>
                    <tr>
                        <td><b>Documento:</b></td> <td> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }} </td>
                    </tr>
                    <tr>
                        <td><b>Fecha:</b></td> <td> {{ $doc_encabezado->fecha }} </td>
                    </tr>
                </table>
                
            </td>
        </tr>
    </table>

    <table class="table table-bordered">
        <tr>
            <td style="border: solid 1px #ddd;">
                <b>Tercero:</b> {{ $doc_encabezado->tercero_nombre_completo }}
                <br/>
                <b>Documento ID: &nbsp;&nbsp;</b> {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}
                <br/>
                <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
                <br/>
                <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
            </td>
            <td style="border: solid 1px #ddd;">
                &nbsp;
            </td>
        </tr>
        <tr>        
            <td colspan="2" style="border: solid 1px #ddd;">
                <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
            </td>
        </tr>
    </table>

    <br>

    <table class="table table-bordered">
        <tr>
            <td style="text-align: center; background-color: #ddd;"> <span style="text-align: right; font-weight: bold;"> Documentos pagados </span> </td>
        </tr>
    </table>
    
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Tercero','Documento','Fecha','Detalle','Abono']) }}
        </thead>
        <tbody>
            <?php 
            
            $total_abono = 0;

            ?>
            @foreach($doc_pagados as $linea )

                <?php 
            
                    $el_documento = app( App\Sistema\TipoTransaccion::find( $linea->doc_cxc_transacc_id )->modelo_encabezados_documentos )->where('core_tipo_transaccion_id',$linea->doc_cxc_transacc_id)
                    ->where('core_tipo_doc_app_id',$linea->doc_cxc_tipo_doc_id)
                    ->where('consecutivo',$linea->doc_cxc_consecutivo)
                    ->get()->first();

                ?>

                <tr>
                    <td> {{ $linea->tercero_nombre_completo }} </td>
                    <td> {{ $linea->documento_prefijo_consecutivo }} </td>
                    <td> {{ $el_documento->fecha }} </td>
                    <td> {{ $el_documento->descripcion }} </td>
                    <td> {{ '$ '.number_format( $linea->abono, 0, ',', '.') }} </td>
                </tr>
                <?php 
                    $total_abono += $linea->abono;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">&nbsp;</td>
                <td> {{ number_format($total_abono, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>

    <table class="table table-bordered">
        <tr>
            <td style="text-align: center; background-color: #ddd;"> <span style="text-align: right; font-weight: bold;"> Registros contables </span> </td>
        </tr>
    </table>
    
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Código</th>
                <th>Cuenta</th>
                <th>Débito</th>
                <th>Crédito</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_valor_debito = 0;
                $total_valor_credito = 0;
            @endphp
            @foreach( $registros_contabilidad as $fila )
                <tr>
                    <td> {{ $fila['cuenta_codigo'] }}</td>
                    <td> {{ $fila['cuenta_descripcion'] }}</td>
                    <td> {{ number_format(  $fila['valor_debito'], 0, ',', '.') }}</td>
                    <td> {{ number_format(  $fila['valor_credito'] * -1, 0, ',', '.') }}</td>
                </tr>
                @php
                    $total_valor_debito += $fila['valor_debito'];
                    $total_valor_credito += $fila['valor_credito'] * -1;
                @endphp
            @endforeach
        </tbody>
        <tfoot>            
                <tr>
                    <td colspan="2"> &nbsp; </td>
                    <td> {{ number_format( $total_valor_debito, 0, ',', '.') }}</td>
                    <td> {{ number_format( $total_valor_credito, 0, ',', '.') }}</td>
                </tr>
        </tfoot>
    </table>

</body>
</html>