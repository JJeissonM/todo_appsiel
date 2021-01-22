<?php

    $medio_recaudo = $encabezado_documento->medio_recaudo;

    switch ( $medio_recaudo->comportamiento )
    {
        case 'Efectivo':
            $caja = $encabezado_documento->caja;
            $cuenta_bancaria = null;
            break;

        case 'Tarjeta bancaria':
            $cuenta_bancaria = $encabezado_documento->cuenta_bancaria;
            $caja = null;
            break;
        
        default:
            $caja = null;
            $cuenta_bancaria = null;
            break;
    }
?>

<table class="table table-bordered" style="font-size: 14px;">
    @if( $vista_impresion )
        <tr>
            <td style="border: solid 1px #ddd; margin-top: -40px; width: 60%;">
                <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen}}" height="{{ config('configuracion.alto_logo_formatos') }}" width="80%" />
            </td>
            <td style="border: solid 1px #ddd;">
                    <b style="font-size: 1.1em; text-align: center; display: block; margin-bottom: -15px;">{{ $encabezado_documento->tipo_documento_app->descripcion }}</b>
                    <br/>
                    <b>Documento:</b> {{ $encabezado_documento->tipo_documento_app->prefijo }} {{ $encabezado_documento->consecutivo }}
                    <br/>
                    <b>Fecha:</b> {{ $encabezado_documento->fecha }}
                    <br>
                    <b>Valor: &nbsp;&nbsp;</b> ${{ number_format( $encabezado_documento->valor_total,0,',','.') }}
                @if($encabezado_documento->estado == 'Anulado')
                    <div class="alert alert-danger" class="center">
                        <strong>Documento Anulado</strong>
                    </div>
                @endif
            </td>
        </tr>
    @endif
    <tr>
        <td colspan="2">
            <b>Recibido de:</b> {{ $encabezado_documento->tercero->descripcion }}
            &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp; 
            <b> {{ $encabezado_documento->tercero->tipo_doc_identidad->abreviatura }}: </b> {{ number_format( $encabezado_documento->tercero->numero_identificacion, 0, ',', '.') }}
            &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp; 
            <b>Dirección:</b> {{ $encabezado_documento->tercero->direccion1 }}, {{ strtoupper($encabezado_documento->tercero->ciudad->descripcion) }}
            &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp; 
            <b>Teléfono:</b> {{ $encabezado_documento->tercero->telefono1 }}
            @if(!is_null($registro_referencia_tercero))
                &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp; 
                <b>PLACA:</b> {{ $registro_referencia_tercero->placa }}
                &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
                <b>No. interno:</b> {{ $registro_referencia_tercero->int }}
            @endif
        </td>
    </tr>
            
    <tr>
        <td>
            <b>La suma de: </b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ strtoupper( NumerosEnLetras::convertir( $encabezado_documento->valor_total,'pesos',false) ) }}
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
            <b>Por concepto de: </b> {{ $encabezado_documento->descripcion }}
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table class="table borderless">
                <tr>
                    <td colspan="3" width="50%">
                        <b>Observación: &nbsp;&nbsp;</b> {!! $encabezado_documento->documento_soporte !!}
                    </td>
                    <td colspan="4" style="vertical-align: bottom;">
                        <br><br>
                        &nbsp;&nbsp;&nbsp;&nbsp;_________________________________ <br>
                        &nbsp;&nbsp;&nbsp;&nbsp;<b> {{ $encabezado_documento->tercero->tipo_doc_identidad->abreviatura }}: </b> {{ number_format( $encabezado_documento->tercero->numero_identificacion, 0, ',', '.') }}
                    </td>
                </tr>
            </table>
            <table class="table table-bordered">
                <tr>
                    <td>
                        <b> Elaborado: </b> <br>
                            <div style="width: 100%; text-align: center;">{{ explode('@',$encabezado_documento->creado_por)[0] }}</div>
                    </td>
                    <td>
                        <b> Aprobado: </b> <br>
                    </td>
                    <td>
                        <b> Contabilizado: </b> <br>
                    </td>
                    <td>
                        <b> Fecha de recibido </b> <br>
                    </td>
                    <td>
                        <b> Día </b> <br>
                    </td>
                    <td>
                        <b> Mes </b> <br>
                    </td>
                    <td>
                        <b> Año </b> <br>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <div style="font-size: 11px; text-align: center;">
                * Valledupar: Calle 5C No. 38 - 61. Franciso el Hombre. Teléfono 5741396 - Cel. 3223039437
                <br>
                * Bucaramanga: Calle 41 No. 32 - 59, local 203 edificio profesional El prado. Cel. 3168757905
                <br>
                * Aguachica: Calle 3 No. 14 - 57. Tel. 5661208
                <br>
                Correo: <a href="mailto:servicioalcliente@transporcol.com">servicioalcliente@transporcol.com</a>
                &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp; 
                Web: <a href="www.transporcol.com">www.transporcol.com</a>
            </div>
        </td>
    </tr>
</table>
