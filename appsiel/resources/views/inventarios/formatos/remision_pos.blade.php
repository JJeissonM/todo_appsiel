<!DOCTYPE html>
<html>
<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    <link rel="stylesheet" href="{{ asset("css/stylepdf.css") }}">
    <style type="text/css">
        @page {
          size: 3.15in 38.5in;
          margin: 15px;
        }

    </style>
</head>
<body>
    <?php        
        $url_img = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen;

        $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];

        $lineas_a_imprimir = $doc_registros;

        if ( (int)$doc_encabezado->core_tipo_transaccion_id === 2 )
        {
            $lineas_a_imprimir = $doc_registros->filter(function ($linea) use ($doc_encabezado) {
                return (int)$linea->inv_bodega_id === (int)$doc_encabezado->bodega_destino_id
                    && $linea->motivo_movimiento === 'entrada';
            });
        }
    ?>
<div class="headempresap">
    <table border="0" style="margin-top: 12px !important;" width="100%">
        <tr>
            <td width="15%">
                <img src="{{ $url_img }}" width="120px;" />
            </td>
            <td>
                <div style="text-align: center;">
                    <br/>
                    <b>{{ $empresa->descripcion }}</b><br/>
                    <b>{{ $empresa->nombre1 }} {{ $empresa->apellido1 }} {{ $empresa->apellido2 }}</b><br/>
                    <b>{{ config("configuracion.tipo_identificador") }}: </b>
                    @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}	@else {{ $empresa->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}</b><br/>
                    {{ $empresa->direccion1 }}, {{ $ciudad->descripcion }} <br/>
                    Teléfono(s): {{ $empresa->telefono1 }}<br/>
                    <b style="color: blue; font-weight: bold;">{{ $empresa->pagina_web }}</b><br/>
                </div>
            </td>
        </tr>
    </table>
</div>
    
<div class="headdocp">
    <table border="0" style="margin: 6px 0 !important;" width="100%">
        <tr>
            <td>
                <b>{{ $doc_encabezado->documento_transaccion_descripcion }} No.</b> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
            </td>
            <td>
                <b>Fecha:</b> {{ $doc_encabezado->fecha }}
            </td>
        </tr>

    </table>
</div>
    
<div class="subheadp">
    <div >
        <b>Tercero:</b> {{ $doc_encabezado->tercero_nombre_completo }}
        @if ( (int)$doc_encabezado->core_tipo_transaccion_id === 2 )
            <br>
            <b>Bodega origen:</b> {{ $doc_encabezado->bodega_origen_descripcion }}
            <br>
            <b>Bodega destino:</b> {{ $doc_encabezado->bodega_destino_descripcion }}
        @endif
    </div>
</div>
    <br>


    <table style="width: 100%;" class="table">
        {{ Form::bsTableHeader(['línea','Producto','Cantidad']) }}
        <tbody>

            <?php
                $numero = 1;
            ?>
            @foreach($lineas_a_imprimir as $linea )
                <tr>
                    <td style="text-align: center;"> {{ $numero }} </td>
                    <td> {{ $linea->item->get_value_to_show(true) }} </td>
                    <td style="text-align: center;"> {{ number_format( abs($linea->cantidad), 2, ',', '.') }} </td>
                </tr>
                <?php 
                    $numero++;
                ?>
            @endforeach
        </tbody>
    </table>

    <br/><br/>
    
    _______________________ 
    <br>
    Elaboró: {{ explode('@',$doc_encabezado->creado_por)[0] }}
    
    <br><br><br><br>
    <div style="width: 100%; text-align: right;">
        _______________________ 
        <br>
        Recibe
    </div>
    
    <br><br><br>
    _______________________ 
    <br>
    <b>Detalle: &nbsp;&nbsp;</b> {{ strip_tags($doc_encabezado->descripcion) }}

</body>
</html>
