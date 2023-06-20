<?php

use App\Core\Tercero;

    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen; 	 

    $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];

    $tercero = Tercero::find( $doc_encabezado->core_tercero_id );
    $vistaimprimir = 'imprimir';
?>
<!DOCTYPE html>
<html>
<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    @include( 'core.dis_formatos.plantillas.estiloestandar2', [ 'vista' => 'imprimir' ] )
</head>
<body>

    <?php

        $medio_recaudo = $doc_encabezado->medio_recaudo;
        $caja = null;
        $cuenta_bancaria = null;
        if( !is_null($doc_encabezado->medio_recaudo) )
        {
            switch ( $medio_recaudo->comportamiento )
            {
                case 'Efectivo':
                    $caja = $doc_encabezado->caja;
                    $cuenta_bancaria = null;
                    break;

                case 'Tarjeta bancaria':
                    $cuenta_bancaria = $doc_encabezado->cuenta_bancaria;
                    $caja = null;
                    break;
                
                default:
                    break;
            }
        }
    ?>
    
    
@include('calificaciones.boletines.formatos.banner_colegio_con_escudo')
<hr>


<table class="info">
    <tr>
        <td width="55%"><b style="font-size: 16px">{{ $empresa->descripcion }}</b></td>
        <td width="45%" colspan="">
            <b style="font-size: 16px">Recibo N. {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</b>
        </td>
    </tr>
    <tr>
        <td>Dirección: {{ $empresa->direccion1 }}</td>
        <td colspan="">
            <p>{{ config("configuracion.tipo_identificador") }}: {{ $empresa->numero_identificacion }} - {{ $empresa->digito_verificacion }}</p>
        </td>
    </tr>
    <tr>
        <td>Telefono: {{ $empresa->telefono1 }}</td>
        <td>Mail: {{ $empresa->email }}</td>
    </tr>
</table>

<hr>
<table class="info">
    <tr>
        <td width="12%"><b>Cliente:</b></td>
        <td width="43%">{{ $doc_encabezado->tercero_nombre_completo }}</td>
        <td width="20%"><b>Fecha:</b></td>
        <td width="25%">
            <?php
                $fecha = date_create($doc_encabezado->fecha);
                $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                       
                $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
            ?>
            {{ $fecha_final }}
        </td>
    </tr>
    <tr>
        <td><b>{{ config("configuracion.tipo_identificador") }}:</b></td>
        <td>@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif</td>
        <td><b>Teléfono:</b></td>
        <td>{{ $doc_encabezado->telefono1 }}</td>
    </tr>
</table>

    <br>

    <table class="table table-bordered">
        <tr>
            <td style="text-align: center; background-color: #ddd;"> <span style="text-align: right; font-weight: bold;"> DOCUMENTOS PAGADOS </span> </td>
        </tr>
    </table>
    
    <table class="table table-bordered table-striped contenido">
        {{ Form::bsTableHeader(['TERCERO','DOCUMENTO','FECHA','DETALLE','ABONO','SALDO']) }}
        <tbody>
            <?php 
            
                $total_abono = 0;
                $total_pendiente = 0;

            ?>
            @foreach($doc_pagados as $linea )

                <?php 
            
                    $el_documento = app( App\Sistema\TipoTransaccion::find( $linea->doc_cxc_transacc_id )->modelo_encabezados_documentos )->where('core_tipo_transaccion_id',$linea->doc_cxc_transacc_id)
                    ->where('core_tipo_doc_app_id',$linea->doc_cxc_tipo_doc_id)
                    ->where('consecutivo',$linea->doc_cxc_consecutivo)
                    ->get()->first();

                    $saldo_pendiente = App\CxC\CxcMovimiento::where('core_tipo_transaccion_id',$linea->doc_cxc_transacc_id)
                                                        ->where('core_tipo_doc_app_id',$linea->doc_cxc_tipo_doc_id)
                                                        ->where('consecutivo',$linea->doc_cxc_consecutivo)
                                                        ->value('saldo_pendiente');
                ?>

                <tr>
                    <td class="text-left"> {{ $linea->tercero_nombre_completo }} </td>
                    <td class="text-left"> {{ $linea->documento_prefijo_consecutivo }} </td>
                    <td class="text-left"> {{ $el_documento->fecha }} </td>
                    <td class="text-left"> {{ $el_documento->descripcion }} </td>
                    <td> {{ '$ '.number_format( $linea->abono, 0, ',', '.') }} </td>
                    <td> ${{ number_format( $saldo_pendiente, 0, ',', '.') }} </td>
                </tr>
                <?php 
                    $total_abono += $linea->abono;
                    $total_pendiente += $saldo_pendiente;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="4" style="text-align: right;"> Totales </td>
                <td> ${{ number_format($total_abono, 0, ',', '.') }} </td>
                <td> ${{ number_format($total_pendiente, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>

    @include('tesoreria.incluir.registros_descuentos')

    @include('tesoreria.medios_de_pago.tabla_show_detalles', [ 'vistaimprimir' => 'si' ] )

    @include('tesoreria.recaudos_cxc.cheques_relacionados')

    @include('tesoreria.recaudos_cxc.retenciones_relacionadas')


    @if( !empty($registros_contabilidad) ) 
        <table class="table table-bordered">
            <tr>
                <td style="text-align: center; background-color: #ddd;"> <span style="text-align: right; font-weight: bold;"> Registros contables </span> </td>
            </tr>
        </table>
        
        <table class="table table-bordered table-striped contenido">
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