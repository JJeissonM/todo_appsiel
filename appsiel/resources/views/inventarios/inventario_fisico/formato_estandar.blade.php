<link rel="stylesheet" href="{{ asset("css/stylepdf.css") }}">
<style type="text/css">

  
    .lbl_doc_anulado{
        background-color: rgba(253, 1, 1, 0.33);
        width: 100%;
        top: 300px;
        transform: rotate(-45deg);
        text-align: center;
        font-size: 2em;
    }
</style>

<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td width="60%" style="border: none;">
            <div class="headempresa">  
                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'show' ] )
            </div>                
        </td>
        <td >
            <div class="headdoc">
                <b style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado->documento_transaccion_descripcion }}</b>
                <br/>
                <b>Documento:</b> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
                <br/>
                <b>Fecha:</b> {{ $doc_encabezado->fecha }}
                <br/>
                <b>Tercero:</b> {{ $doc_encabezado->tercero_nombre_completo }}
                <br/>
                <b>{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
			@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif  
            </div>
            
        </td>
    </tr>
    <tr> 
    <div class="subhead">
        <table>
            <tr>
                <td>
                    
                </td>
                <td>
                    <b>Hora inicio:</b> {{ $doc_encabezado->hora_incio }}
                    <br/>
                    <b>Hora finalización:</b> {{ $doc_encabezado->hora_finalizacion }}
                </td>        
            </tr>
        </table>
    </div>        
    </tr>
</table>

<span style="size: 1.1em; font-weight: bold;"> IF = Inventario Físico </span>
<table class="table table-bordered table-striped">
    {{ Form::bsTableHeader(['Cód.','Producto','Cant. IF','Costo Unit. IF','Costo Tot. IF','Cant. sistema','Costo Tot. sistema','DIF. Cant.','DIF. Costo Tot.','']) }}
    <tbody>
        <?php 
        
        $total_cantidad = 0;
        $total_documento = 0;
        $total_cantidad_sistema = 0;
        $total_documento_sistema = 0;
        $total_cantidad_dif = 0;
        $total_documento_dif = 0;
        ?>
        @foreach($doc_registros as $linea )
            <?php
                $diferencia = round( $linea->cantidad - $linea->cantidad_sistema, 2 );
                $diferencia_costo = $linea->costo_total - $linea->costo_total_sistema;

                if ( $diferencia > 0 )
                {
                    $resultado = '<span style="color:green;">Sobrante</span>';
                }else{
                    $resultado = '<span style="color:red;">Faltante</span>';
                }

                if ( -0.0001 < $diferencia && $diferencia < 0.0001 )
                {
                    $resultado = '<span style="color:black;"><i class="fa fa-check"></i></span>';
                    $diferencia_costo = 0;
                }

            ?>
            <tr>
                <td class="text-center"> {{ $linea->producto_id }} </td>
                <td> {{ $linea->item->get_value_to_show(true)}} </td> 

                <!-- Datos del conteo físico -->
                <td class="text-center"> {{ number_format( $linea->cantidad, 2, ',', '.') }}  </td>
                <?php 
                    $costo_unit_conteo = 0;
                    if ($linea->cantidad != 0) {
                        $costo_unit_conteo = $linea->costo_total / $linea->cantidad;
                    }
                ?>
                <td class="text-right"> ${{ number_format( $costo_unit_conteo, 0, ',', '.') }} </td>
                <td class="text-right"> ${{ number_format( $linea->costo_total, 0, ',', '.') }} </td>

                <!-- Datos del sistema -->
                <td class="text-center"> {{ number_format( $linea->cantidad_sistema, 2, ',', '.') }} </td>
                <td class="text-right"> ${{ number_format( $linea->costo_total_sistema, 0, ',', '.') }} </td>

                <!-- Datos de la diferencia -->
                <td class="text-center"> {{ number_format( $diferencia, 2, ',', '.') }} </td>
                <td class="text-right"> ${{ number_format( $diferencia_costo, 0, ',', '.') }} </td>
                <td> 
                    <?php
                        echo $resultado;
                    ?>
                </td>
            </tr>
            <?php 
                $total_cantidad += $linea->cantidad;
                $total_documento += $linea->costo_total;
                $total_cantidad_sistema += $linea->cantidad_sistema;
                $total_documento_sistema += $linea->costo_total_sistema;
                $total_cantidad_dif += ( $linea->cantidad - $linea->cantidad_sistema);
                $total_documento_dif += ( $linea->costo_total - $linea->costo_total_sistema);
            ?>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <?php 
                if ( $total_cantidad_dif > 0 )
                {
                    $resultado = '<span style="color:green;">Sobrante</span>';
                }else{
                    $resultado = '<span style="color:red;">Faltante</span>';
                }

                if ( -0.0001 < $total_cantidad_dif && $total_cantidad_dif < 0.0001)
                {
                    $resultado = '<span style="color:black;"><i class="fa fa-check"></i></span>';
                    $total_documento_dif = 0;
                }
            ?>
            <td colspan="2">&nbsp;</td>
            <td class="text-center"> {{ number_format($total_cantidad, 0, ',', '.') }} </td>
            <td class="text-right"> &nbsp; </td>
            <td class="text-right"> {{ '$ '.number_format($total_documento, 0, ',', '.') }} </td>
            <td class="text-center"> {{ number_format($total_cantidad_sistema, 0, ',', '.') }} </td>
            <td class="text-right"> {{ '$ '.number_format($total_documento_sistema, 0, ',', '.') }} </td>
            <td class="text-center"> {{ number_format($total_cantidad_dif, 0, ',', '.') }} </td>
            <td class="text-right"> {{ '$ '.number_format($total_documento_dif, 0, ',', '.') }} </td>
                <td> 
                    <?php 
                        echo $resultado;
                    ?>
                </td>
        </tr>
    </tfoot>

</table>
<b>Detalle: &nbsp;&nbsp;</b>
<br>
{!! $doc_encabezado->descripcion !!}