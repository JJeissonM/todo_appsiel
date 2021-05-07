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

<div class="table-responsive">
    <table class="table table-bordered">
        <tr>
            <td width="50%" style="border: solid 1px #ddd; margin-top: -40px;">
                    @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'show' ] )
            </td>
            <td style="border: solid 1px #ddd; padding-top: -20px;">
                <div style="vertical-align: center;">
                    <b style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado->documento_transaccion_descripcion }}</b>
                    <br/>
                    <b>Documento:</b> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
                    <br/>
                    <b>Fecha:</b> {{ $doc_encabezado->fecha }}
                </div>
                @if($doc_encabezado->estado == 'Anulado')
                    <div class="alert alert-danger" class="center">
                        <strong>Documento Anulado</strong>
                    </div>
                @endif
            </td>
        </tr>
        <tr>
            <td style="border: solid 1px #ddd;">
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
            <td style="border: solid 1px #ddd;">
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
            <td colspan="2" style="border: solid 1px #ddd;">
                <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
            </td>
        </tr>
    </table>
</div>

<h4 style="text-align: center;"> Documentos abonados </h4>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Tercero','Documento','Fecha','Detalle','Abono','Saldo']) }}
        <tbody>
            <?php 
            
                $total_abono = 0;
                $total_pendiente = 0;

            ?>
            @foreach( $doc_pagados as $linea )

                <?php 
                    
                    $modelo_transaccion = App\Sistema\TipoTransaccion::find( $linea->doc_cxc_transacc_id )->modelo_encabezados_documentos;

                    $documento_pagado = app( $modelo_transaccion )->where('core_tipo_transaccion_id',$linea->doc_cxc_transacc_id)
                                                        ->where('core_tipo_doc_app_id',$linea->doc_cxc_tipo_doc_id)
                                                        ->where('consecutivo',$linea->doc_cxc_consecutivo)
                                                        ->get()
                                                        ->first();

                    $saldo_pendiente = App\CxC\CxcMovimiento::where('core_tipo_transaccion_id',$linea->doc_cxc_transacc_id)
                                                        ->where('core_tipo_doc_app_id',$linea->doc_cxc_tipo_doc_id)
                                                        ->where('consecutivo',$linea->doc_cxc_consecutivo)
                                                        ->value('saldo_pendiente');
                ?>

                <tr>
                    <td> {{ $linea->tercero_nombre_completo }} </td>
                    <td>
                        {{ $linea->documento_prefijo_consecutivo }}
                    </td>
                    <td> {{ $documento_pagado->fecha }} </td>
                    <td> {{ $documento_pagado->descripcion }} </td>
                    <td> $ {{ number_format( $linea->abono, 0, ',', '.') }} </td>
                    <td> $ {{ number_format( $saldo_pendiente, 0, ',', '.') }} </td>
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
                <td> $ {{ number_format($total_abono, 0, ',', '.') }} </td>
                <td> $ {{ number_format($total_pendiente, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
</div>

@include('tesoreria.recaudos_cxc.cheques_relacionados')

<h4 style="text-align: center;">Registros contables</h4>
<div class="table-responsive">
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
</div>

@include('transaccion.auditoria')