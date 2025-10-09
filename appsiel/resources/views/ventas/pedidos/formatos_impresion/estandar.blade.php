<!DOCTYPE html>
<html>
<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    <link rel="stylesheet" href="{{ asset("css/stylepdf.css") }}">
    <style type="text/css">
    
    </style>
</head>
<body>

    <table>
        <tr>
            <td style="border: none" width="60%">
                <div class="headempresa">
                    @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir' ] )
                </div>                    
            </td>
            <td>
                <div class="headdoc">
                    <b style="font-size: 1.6em; text-align: center; display: block;">
                        {{ $doc_encabezado->documento_transaccion_descripcion }}
                        <br/>
                        <b>No.</b> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
                    </b>
                    <br/>
                    <b>Para:</b> {{ $doc_encabezado->tercero_nombre_completo }}
                    <br/>
                    <b>{{ config("configuracion.tipo_identificador") }}: </b>
                        @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif
                    <br/>
                    <b>Fecha:</b> {{ $doc_encabezado->fecha }}
                    <br/>
                    <b>Fecha Entrega:</b> {{ $doc_encabezado->fecha_entrega }}
                    <br/>
                    <b>Atendido por:</b> {{ $doc_encabezado->vendedor->tercero->descripcion }}
                </div>          
                
            </td>
        </tr>
    </table>

    
<div class="subhead">
    @if($doc_encabezado->estado == 'Anulado')
        <br><br>
        <div style="background-color: #ddd; width: 100%;">
            <strong>Documento Anulado</strong>
        </div>
        <br><br>
    @endif
    
</div>
    

<div class="row">

    <div class="col-md-4">
        <div style="text-align: center; background-color: #ddd;"> <span style="text-align: right; font-weight: bold;"> Productos del pedido </span> </div>

        
        <table class="table table-bordered table-striped">
            {{ Form::bsTableHeader(['Ítem','Cantidad']) }}
            <tbody>
                <?php 
                $i = 1;
                $total_cantidad = 0;
                $subtotal = 0;
                $total_impuestos = 0;
                $total_factura = 0;
                $array_tasas = [];
                ?>
                @foreach($doc_registros as $linea )
                    <tr>
                        <td> {{ $linea->item->get_value_to_show() }} </td>
                        <td width="12.5%" class="text-center"> {{ number_format( $linea->cantidad, 2, ',', '.') }} </td>
                    </tr>
                    <?php 
                        $i++;
                    ?>
                @endforeach
            </tbody>
        </table>

    </div>
    <div class="col-md-8">
        @if( $doc_encabezado->descripcion != '' )
            <b>Detalle: &nbsp;&nbsp;</b> <?php echo $doc_encabezado->descripcion ?>
            <br>
        @endif
    </div>
</div>
    
</body>
</html>