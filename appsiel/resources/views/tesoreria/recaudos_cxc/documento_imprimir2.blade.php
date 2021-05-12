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
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap" rel="stylesheet">  
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <style type="text/css">
    *{
       margin: 0;
       padding: 0;
       box-sizing: border-box;
       font-size: 12px;
       font-family: 'Open Sans', sans-serif;
    }
    
    .page-break{
        page-break-after: always;
    }

    html{
        margin: 30px 70px 20px 70px;
    }    
    .info, .table, .contenido, .encabezado{
        width: 100%;
        border-collapse: collapse;
        margin: .5rem 0;
    }
    .encabezado {
        background-color: #111e52;        
        color: white;
        margin-left: -40px;
        margin-right: -40px;
        padding: 10px;
    }
    .contenido >tr>td{ 
        border: 1px solid black !important;
        text-align: right;
        padding: 0 3px;
    }
    .text-center{
        text-align: center !important;
    }
    .text-left{
        text-align: left !important;
    }
    .text-indent, .text-indent > *{
        padding-left: 10px !important;
        text-align: justify !important;
    }
    .contenido th{
        color: black !important;
        border: 1px solid black !important;
        background-color: lightgray !important;
    }
    .totales{
        border: 1px solid black;
    }
    .totl-top{
        border-top: 1px solid black;
        border-left: 1px solid black;
        border-right: 1px solid black;
    }
    .totl-mid{
        border-left: 1px solid black;
        border-right: 1px solid black;
    }
    .totl-bottom{
        border-bottom: 1px solid black;
        border-left: 1px solid black;
        border-right: 1px solid black;
    }
    .encabezado a{
        color: white;
    }
    a{
        text-decoration: none;
        color: black;
        
    }
    tr>th{
        text-transform: capitalize;
    }
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
<table class="encabezado" width="100%">
    <tr>
        <td width="70%" rowspan="2">
            <?php
                $image = getimagesize($url);
                $ancho = $image[0];            
                $alto = $image[1];   
			 
                $palto = (60*100)/$alto;
				$ancho = $ancho*$palto/100;
				echo '<img src="'.$url.'" width="'.$ancho.'" height="60" />';
           
			?>	
            </td>
        <td>Teléfono: {{ $empresa->telefono1 }}</td>
    </tr>
    <tr>
        <td>Email: <a href="mailto:{{ $empresa->email }}">{{ $empresa->email }}</a></td>
    </tr>
</table>

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

    <table class="table table-bordered contenido">
        <tr>
            <td style="text-align: center; background-color: #ddd;"> <span style="text-align: right; font-weight: bold;"> Documentos Pagados </span> </td>
        </tr>
    </table>
    
    <table class="table table-bordered table-striped contenido">
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

    @include('tesoreria.medios_de_pago.tabla_show_detalles')

    @include('tesoreria.recaudos_cxc.cheques_relacionados')

    @include('tesoreria.recaudos_cxc.retenciones_relacionadas')


    @if( !empty($registros_contabilidad) ) 
        <table class="table table-bordered contenido">
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