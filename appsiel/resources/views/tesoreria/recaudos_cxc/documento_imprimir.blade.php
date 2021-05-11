<!DOCTYPE html>
<html>
<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    <link rel="stylesheet" href="{{ asset("css/stylepdf.css") }}">
    <style type="text/css">

    </style>
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

    <table>
        <tr>
            <td style="border: none" width="70%">
                <div class="headempresa">
                    @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir' ] )
                </div>                    
            </td>
            <td >
                <div class="headdoc"><br><br>
                    <b style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado->documento_transaccion_descripcion }}</b>
                    <br>
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
        <table >
            <tr>
                <td >
                    <b>Tercero:</b> {{ $doc_encabezado->tercero_nombre_completo }}
                    <br/>
                    <b>{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
    			@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif
                    <br/>
                    <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
                    <br/>
                    <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
                    @include('matriculas.facturas.datos_estudiante_recaudo')
                </td>
                <td>
                    @if( !is_null( $caja ) )
                        <b>Caja: &nbsp;&nbsp;</b> {{ $caja->descripcion }}
                        <br>
                    @endif
                    @if( !is_null( $cuenta_bancaria ) )
                        <b>Cuenta bancaria: &nbsp;&nbsp;</b> Cuenta {{ $cuenta_bancaria->tipo_cuenta }} {{ $cuenta_bancaria->entidad_financiera->descripcion }} No. {{ $cuenta_bancaria->descripcion }}
                        <br>
                    @endif
                </td>
            </tr>
            <tr>        
                <td colspan="2">
                    
                </td>
            </tr>
        </table>
    </div>

    <br>

    <table class="table table-bordered">
        <tr>
            <td style="text-align: center; background-color: #ddd;"> <span style="text-align: right; font-weight: bold;"> DOCUMENTOS PAGADOS </span> </td>
        </tr>
    </table>
    
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Tercero','Documento','Fecha','Detalle','Abono','Saldo']) }}
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
                    <td> {{ $linea->tercero_nombre_completo }} </td>
                    <td class="text-center"> {{ $linea->documento_prefijo_consecutivo }} </td>
                    <td> {{ $el_documento->fecha }} </td>
                    <td> {{ $el_documento->descripcion }} </td>
                    <td class="text-right"> {{ '$ '.number_format( $linea->abono, 0, ',', '.') }} </td>
                    <td class="text-right"> ${{ number_format( $saldo_pendiente, 0, ',', '.') }} </td>
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
                <td class="text-right"> ${{ number_format($total_abono, 0, ',', '.') }} </td>
                <td class="text-right"> ${{ number_format($total_pendiente, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>


    @include('tesoreria.medios_de_pago.tabla_show_detalles')

    @include('tesoreria.recaudos_cxc.cheques_relacionados')

    @include('tesoreria.recaudos_cxc.retenciones_relacionadas')


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
                        <td class="text-center"> {{ $fila['cuenta_codigo'] }}</td>
                        <td> {{ $fila['cuenta_descripcion'] }}</td>
                        <td class="text-right"> {{ number_format(  $fila['valor_debito'], 0, ',', '.') }}</td>
                        <td class="text-right"> {{ number_format(  $fila['valor_credito'] * -1, 0, ',', '.') }}</td>
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
                        <td class="text-right"> {{ number_format( $total_valor_debito, 0, ',', '.') }}</td>
                        <td class="text-right"> {{ number_format( $total_valor_credito, 0, ',', '.') }}</td>
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