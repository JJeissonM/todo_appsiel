<!DOCTYPE html>
<html>
<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    <link rel="stylesheet" href="{{ asset("css/stylepdf.css") }}">
    <style type="text/css">
        
    </style>
</head>
<body>

    <table class="table">
        <tr>
            <td style="width: 60%;">
                <div class="headempresa">
                    @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir' ] )
                </div>  
            </td>
            <td>
                <div class="headdoc">
                    <b style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado->documento_transaccion_descripcion }}</b>

                    <table>
                        <tr>
                            <td><b>Documento:</b></td> <td> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }} </td>
                        </tr>
                        <tr>
                            <td><b>Fecha:</b></td> <td> {{ $doc_encabezado->fecha }} </td>
                        </tr>
                    </table>    
                </div>
                
                
            </td>
        </tr>
    </table>
<div class="subhead">
    <table class="table">
        <tr>
            <td>
                <b>Tercero:</b> {{ $doc_encabezado->tercero_nombre_completo }}
                <br/>
                <b>Documento ID: &nbsp;&nbsp;</b> {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}
                <br/>
                <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
                <br/>
                <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
            </td>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>        
            <td colspan="2">
                
            </td>
        </tr>
    </table>
</div>

<br>

<?php 
    $total_recaudo=0;
    $i=0;
    $vec_motivos = [];
?>
<div class="table-responsive">

    <table class="table table-bordered">
        <tr>
            <td style="text-align: center; background-color: #ddd;"> <span style="text-align: right; font-weight: bold;"> Medios de recaudo </span> </td>
        </tr>
    </table>
    <table class="table table-bordered">
        {{ Form::bsTableHeader(['Medio de pago','Caja/Cta. Bancaria','Valor']) }}
        <tbody>
            @foreach ($doc_registros as $registro)
                <tr>
                    <td> {{ $registro->medio_recaudo }} </td>
                    <td> {{ $registro->caja }} {{ $registro->cuenta_bancaria }} </td>
                    <td> ${{ number_format($registro->valor, 0, ',', '.') }} </td>
                </tr>
                <?php
                    $total_recaudo += $registro->valor;

                    // Si el motivo no está en el array, se agregan sus valores por primera vez
                    if ( !isset( $vec_motivos[$registro->motivo_id] ) )
                    {
                        $vec_motivos[$registro->motivo_id]['descripcion'] = $registro->motivo;
                        $vec_motivos[$registro->motivo_id]['total_motivo'] = $registro->valor;
                    }else{
                        // si ya está el motivo en el array, se acumula su valor
                        $vec_motivos[$registro->motivo_id]['total_motivo'] += $registro->valor;
                    }                
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td style="border-top: solid 1px black;">
                   ${{ number_format($total_recaudo, 0, ',', '.') }} ({{ NumerosEnLetras::convertir($total_recaudo,'pesos',false) }})
                </td>
            </tr>
        </tfoot>
    </table>
</div>


    <br>

    <table class="table table-bordered">
        <tr>
            <td style="text-align: center; background-color: #ddd;"> <span style="text-align: right; font-weight: bold;"> Conceptos pagados </span> </td>
        </tr>
    </table>
    
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Motivo','Tercero','Detalle','Valor']) }}
        <tbody>
            <?php 
            
            $total_abono = 0;

            ?>
            @foreach($doc_registros as $linea )

                <tr>
                    <td> {{ $linea->motivo }} </td>
                    <td> {{ $linea->tercero }} </td>
                    <td> {{ $linea->detalle_operacion }} </td>
                    <td> {{ '$ '.number_format( $linea->valor, 0, ',', '.') }} </td>
                </tr>
                <?php 
                    $total_abono += $linea->valor;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">&nbsp;</td>
                <td> {{ number_format($total_abono, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>

    @if( !empty($registros_contabilidad) ) 
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
    @endif

    <br><br>
    @include('tesoreria.incluir.firmas')
        <br>
        <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
</body>
</html>