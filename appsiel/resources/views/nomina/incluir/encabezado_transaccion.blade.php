<table style="font-size: 15px; border-collapse: collapse;">
    <tr>
        <td width="60%" style="border: solid 1px black; padding-top: -20px;">
            <div>
                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa' )
            </div>
        </td>
        <td style="border: solid 1px black; padding-top: -20px;">
            <div style="vertical-align: center;">
                <b style="font-size: 14px; text-align: center; display: block;">{{ $descripcion_transaccion }}</b>
                <br/>

                <b>Documento:</b> {{ $encabezado_doc->documento_app }}
                <br/>
                @php 
                    $fecha = explode("-",$encabezado_doc->fecha) 
                @endphp
                <b>Fecha: </b> &nbsp; {{ $fecha[2] }} de {{ Form::NombreMes([$fecha[1]]) }} de {{ $fecha[0] }}
            </div>
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;" colspan="2">
            <b>Detalle: </b> &nbsp; {{ $encabezado_doc->descripcion }}
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <div>
                <div style="display: inline; float: left;">
                    <b>Total Devengos: </b> &nbsp;
                </div>
                <div style="display: inline; float: left;">
                    {{ Form::TextoMoneda( $encabezado_doc->total_devengos) }}
                </div>
            </div>
        </td>
        <td style="border: solid 1px black;">
            <div>
                <div style="display: inline; float: left;">
                    <b>Total Deducciones: </b> &nbsp;
                </div>
                <div style="display: inline; float: left;">
                    {{ Form::TextoMoneda( $encabezado_doc->total_deducciones) }}
                </div>
            </div>
        </td>
    </tr>
</table>