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
    </style>
</head>
<body>
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
            <!--pendiente-->
            <!--Ingresos brutos y fehca inicio actividad modificables-->
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

<?php 
    $total_recaudo=0;
    $i=0;
    $vec_motivos = [];
?>
<div class="table-responsive contenido">

    <table class="table table-bordered">
        <tr>
            <td style="text-align: center; background-color: #ddd;"> <span style="text-align: right; font-weight: bold;"> Medios de Recaudo </span> </td>
        </tr>
    </table>
    <table class="table table-bordered">
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

    <table class="table table-bordered contenido">
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
    @include('tesoreria.incluir.firmas')
        <br>
        <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
</body>
</html>