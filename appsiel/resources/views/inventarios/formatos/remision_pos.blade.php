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
                <b>{{ $doc_encabezado->documento_transaccion_descripcion }} No.</b> @yield('documento_transaccion_prefijo_consecutivo')               
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
        <br>
        <b>{{ config("configuracion.tipo_identificador") }}: </b>
        @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}
        <br>
        <b>Dirección:</b> {{ $doc_encabezado->direccion1 }}
        <br>
        <b>Teléfono:</b> {{ $doc_encabezado->telefono1 }}
        <br>
        
    </div>
</div>
    <br>


    <table style="width: 100%;" class="table">
        {{ Form::bsTableHeader(['línea','Producto','Cantidad']) }}
        <tbody>

            <?php
                $total_cantidad = 0;
                $numero = 1;
            ?>
            @foreach($doc_registros as $linea )
                <tr>
                    <td style="text-align: center;"> {{ $numero }} </td>
                    <td> {{ $linea->producto_descripcion }} ({{ $linea->unidad_medida1 }}) </td>
                    <td style="text-align: right;"> {{ number_format( abs($linea->cantidad), 2, ',', '.') }} </td>
                </tr>
                <?php 
                    $total_cantidad += $linea->cantidad;
                    $numero++;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td style="text-align: right;"> {{ number_format( abs($total_cantidad), 2, ',', '.') }} </td>
            </tr>
        </tfoot>            
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
    <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}

</body>
</html>