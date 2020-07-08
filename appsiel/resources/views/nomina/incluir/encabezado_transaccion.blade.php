<table class="table table-bordered">
    <tr>
        <td style="border: solid 1px #ddd; margin-top: -40px;">
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

        </td>
    </tr>
</table>

<table style="font-size: 15px; border-collapse: collapse;">
    <tr>
        <td style="border: solid 1px black;" colspan="2">
            <b>Detalle: </b> &nbsp; {{ $encabezado_doc->descripcion }}
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <b>Total Devengos: </b> &nbsp; ${{ number_format( $encabezado_doc->total_devengos, '0','.',',') }}
        </td>
        <td style="border: solid 1px black;">
            <b>Total Deducciones: </b> &nbsp; ${{ number_format( $encabezado_doc->total_deducciones, '0','.',',') }}
        </td>
    </tr>
</table>