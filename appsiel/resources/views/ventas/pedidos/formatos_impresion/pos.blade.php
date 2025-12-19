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
        .lbl_doc_anulado{
            position: absolute;

            background-color: rgba(253, 1, 1, 0.33);
            width: 100%;
            top: 100px;
            transform: rotate(-45deg);
            text-align: center;
            font-size: 2em;
        }
    </style>
</head>
<body onload="window.print()">
    <?php        
        $url_img = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen;

        $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];
    ?>
    <table border="0" style="margin-top: 0px !important;" width="100%">
        <tr>
            <td>
                <div class="headempresap" style="text-align: center;">
                    <br/>
                    <b>{{ $empresa->descripcion }}</b><br/>
                </div>
            </td>
        </tr>
        <tr>
            <td style="font-size: 15px;">
                <div class="headdocp" style="text-align: center;">
                    <b>{{ $doc_encabezado->documento_transaccion_descripcion }} 
                    <br>
                    No.</b> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
                    <br>
                    <b>Fecha:</b> {{ $doc_encabezado->fecha }}
                </div>
            </td>
        </tr>
    </table>
    
    

    <div class="subheadp" >
        <b>Cliente</b> {{ $doc_encabezado->tercero_nombre_completo }} <br>
        <b>{{ config("configuracion.tipo_identificador") }}: </b>
            @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif<br>
        <b>Dirección:</b> {{ $doc_encabezado->direccion1 }} <br>
        <b>Teléfono:</b> {{ $doc_encabezado->telefono1 }}
        <br>
        <b>Atendido por: &nbsp;&nbsp;</b> {{ $doc_encabezado->vendedor->tercero->descripcion }}
        <br>
        <b>Estado: &nbsp;&nbsp;</b> {{ $doc_encabezado->estado }}
        <br>
        
    </div>
    @if($doc_encabezado->estado == 'Anulado')
        <div class="lbl_doc_anulado">
            Documento Anulado
        </div>
    @endif
    <br>

    <table style="width: 100%;" class="table-bordered">
        <thead>
            <tr>
                <th width="10%">Item</th>
                <th width="89%">Cant. pedida</th>
                <!-- <th width="30px">Cant. <br> despachada</th> -->
            </tr>
        </thead>
        <tbody>
            <?php 
                $cantidad_total_productos = 0;
            ?>
            @foreach($doc_registros as $linea )
                <tr>
                    <td> {{ $linea->item->get_value_to_show( true ) }} </td>
                    <td> 
                        {{ number_format( $linea->cantidad, 2, ',', '.') }} {{ $linea->item->get_unidad_medida1() }}
                    </td>
                    <!-- 
                    <td> &nbsp; <br> ____________ </td>
                    -->
                </tr>
                <?php 
                    $cantidad_total_productos++;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">
                    &nbsp;
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <b> Cantidad de items&nbsp;: </b> {{ $cantidad_total_productos }}
                </td>
            </tr>
            <!--
            <tr>
                <td colspan="3">
                    <b> Despachado por &nbsp;&nbsp;&nbsp;: </b> _____________________
                </td>
            </tr>
        -->
        </tfoot>
    </table>
    
    <br>
    <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}

    <p style="text-align: right;">
        Generado: {{ $doc_encabezado->created_at }}
    </p>
    
    <script type="text/javascript">
        window.onload = function() { window.print(); }
    </script>

</body>

</html>