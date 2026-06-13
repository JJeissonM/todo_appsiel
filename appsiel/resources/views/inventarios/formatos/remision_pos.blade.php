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

        .datos-doc-pos,
        .datos-tercero-pos {
            width: 100%;
            line-height: 1.15;
        }

        .datos-doc-pos td,
        .datos-tercero-pos td {
            vertical-align: top;
            padding: 1px 3px;
        }

        .datos-doc-pos .label-pos,
        .datos-tercero-pos .label-pos {
            white-space: nowrap;
            font-weight: bold;
        }

        .datos-doc-pos .valor-pos,
        .datos-tercero-pos .valor-pos {
            word-break: normal;
        }

    </style>
</head>
<body>
    <?php        
        $url_img = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen;

        $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];

        $lineas_a_imprimir = $doc_registros;
        $mostrar_trazabilidad_pos = in_array( (int)$doc_encabezado->core_tipo_transaccion_id, [2, 3] );
        $hora_trazabilidad = '';

        if ( !empty($doc_encabezado->created_at) )
        {
            $hora_trazabilidad = strtolower(date('h:i a', strtotime($doc_encabezado->created_at)));
            $hora_trazabilidad = str_replace(['am', 'pm'], ['a.m.', 'p.m.'], $hora_trazabilidad);
        }

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
    <table border="0" class="datos-doc-pos" style="margin: 4px 0 !important;">
        <tr>
            <td class="valor-pos" colspan="2">
                <span class="label-pos">{{ $doc_encabezado->documento_transaccion_descripcion }} No.</span>
                {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
            </td>
        </tr>
        <tr>
            <td class="label-pos" width="28%">
                Fecha:
            </td>
            <td class="valor-pos" width="72%">
                {{ $doc_encabezado->fecha }}
            </td>
        </tr>
        @if ( $mostrar_trazabilidad_pos && $hora_trazabilidad != '' )
            <tr>
                <td class="label-pos">
                    Hora:
                </td>
                <td class="valor-pos">
                    {{ $hora_trazabilidad }}
                </td>
            </tr>
        @endif
    </table>
</div>
    
<div class="subheadp">
    <table border="0" class="datos-tercero-pos">
        <tr>
            <td class="label-pos" width="28%">
                Tercero:
            </td>
            <td class="valor-pos" width="72%">
                {{ $doc_encabezado->tercero_nombre_completo }}
            </td>
        </tr>
        @if ( (int)$doc_encabezado->core_tipo_transaccion_id === 2 )
            <tr>
                <td class="label-pos">
                    Bodega origen:
                </td>
                <td class="valor-pos">
                    {{ $doc_encabezado->bodega_origen_descripcion }}
                </td>
            </tr>
            <tr>
                <td class="label-pos">
                    Bodega destino:
                </td>
                <td class="valor-pos">
                    {{ $doc_encabezado->bodega_destino_descripcion }}
                </td>
            </tr>
        @endif
    </table>
</div>
    <br>


    <table style="width: 100%;" class="table">
        @if ( $mostrar_trazabilidad_pos )
            {{ Form::bsTableHeader(['Producto','Cantidad']) }}
        @else
            {{ Form::bsTableHeader(['línea','Producto','Cantidad']) }}
        @endif
        <tbody>

            <?php
                $numero = 1;
            ?>
            @foreach($lineas_a_imprimir as $linea )
                <tr>
                    @if ( !$mostrar_trazabilidad_pos )
                        <td style="text-align: center;"> {{ $numero }} </td>
                    @endif
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
