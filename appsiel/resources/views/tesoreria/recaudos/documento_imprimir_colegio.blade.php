<?php

use App\Core\Tercero;

    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen; 	 

    $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];

    $tercero = Tercero::find( $doc_encabezado->core_tercero_id );
    
?>
<!DOCTYPE html>
<html>
<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    @include( 'core.dis_formatos.plantillas.estiloestandar2', [ 'vista' => 'imprimir' ] )
</head>
<body>
    
    @include('tesoreria.formatos_impresion.encabezados_documento_imprimir_colegio')

<?php 
    $total_recaudo=0;
    $i=0;
    $vec_motivos = [];
?>
<div class="table-responsive ">

    <table class="table table-bordered">
        <tr>
            <td style="text-align: center; background-color: #ddd;"> <span style="text-align: right; font-weight: bold;"> Medios de Recaudo </span> </td>
        </tr>
    </table>
    <table class="table table-bordered contenido">
        {{ Form::bsTableHeader(['Medio de pago','Caja/Cta. Bancaria','Valor']) }}
        <tbody>
            @foreach ($doc_registros as $registro)
                <tr>
                    <td class="text-left"> {{ $registro->medio_recaudo }} </td>
                    <td class="text-left"> {{ $registro->caja }} {{ $registro->cuenta_bancaria }} </td>
                    <td> $ {{ number_format($registro->valor, 0, ',', '.') }} </td>
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
                   $ {{ number_format($total_recaudo, 0, ',', '.') }} ({{ NumerosEnLetras::convertir($total_recaudo,'pesos',false) }})
                </td>
            </tr>
        </tfoot>
    </table>
</div>


    <br>

    <table class="table table-bordered">
        <tr>
            <td style="text-align: center; background-color: #ddd;"> <span style="text-align: right; font-weight: bold;"> Conceptos Pagados </span> </td>
        </tr>
    </table>
    
    <table class="table table-bordered table-striped contenido">
        {{ Form::bsTableHeader(['Motivo','Tercero','Valor']) }}
        <tbody>
            <?php 
            
            $total_abono = 0;

            ?>
            @foreach($doc_registros as $linea )

                <tr>
                    <td class="text-left"> {{ $linea->motivo }} </td>
                    <td class="text-left"> {{ $linea->tercero }} </td>
                    <td> {{ '$ '.number_format( $linea->valor, 0, ',', '.') }} </td>
                </tr>
                <?php 
                    $total_abono += $linea->valor;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">&nbsp;</td>
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
    @include('tesoreria.incluir.firmas2')
        <br>
        <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
</body>
</html>