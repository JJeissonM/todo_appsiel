<?php
    $color = 'red';

    if ( $encabezado_doc->estado == 'Activo' )
    {
        $color = 'green';
    }
?>
<table class="table table-bordered">
    <tr>
        <td style="border: solid 1px #ddd; margin-top: -40px;" width="70%">
            @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir' ] )
        </td>
        <td style="border: solid 1px #ddd; padding-top: -20px;">

            <b style="font-size: 1.6em; text-align: center; display: block;">{{ $descripcion_transaccion }}</b>
                <br/>
                <b>Documento:</b> {{ $encabezado_doc->documento_app }}
                <br/>

                @php 
                    $fecha = explode("-",$encabezado_doc->fecha) 
                @endphp

                <b>Fecha: </b> &nbsp; {{ $fecha[2] }} de {{ Form::NombreMes([$fecha[1]]) }} de {{ $fecha[0] }}

                <div>
                    <b> Estado: </b> <i class="fa fa-circle" style="color: {{$color}}"> </i> {{ $encabezado_doc->estado }}
                </div>

        </td>
    </tr>
</table>

<table class="table table-bordered">
    <tr>
        <td colspan="3">
            <b>Detalle: </b> &nbsp; {{ $encabezado_doc->descripcion }}
        </td>
    </tr>
    <tr>
        <td>
            <b>Total Devengos: </b> &nbsp; ${{ number_format( $encabezado_doc->total_devengos, '0','.',',') }}
        </td>
        <td>
            <b>Total Deducciones: </b> &nbsp; ${{ number_format( $encabezado_doc->total_deducciones, '0','.',',') }}
        </td>
        <td>
            <b>Valor Neto: </b> &nbsp; ${{ number_format( $encabezado_doc->total_devengos - $encabezado_doc->total_deducciones, '0','.',',') }}
        </td>
    </tr>
</table>