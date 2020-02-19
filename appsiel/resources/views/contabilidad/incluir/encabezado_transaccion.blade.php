<table class="table table-bordered">
    <tr>
        <td width="50%" style="border: solid 1px black; margin-top: -40px;">
                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa' )
        </td>
        <td style="border: solid 1px black; padding-top: -20px;">
            <div style="vertical-align: center;">
                <b style="font-size: 1.6em; text-align: center; display: block;">{{ $descripcion_transaccion }}</b>
                <br/>
                <b>Documento:</b> {{ $encabezado_doc->documento }}
                <br/>
                <b>Fecha:</b> {{ $encabezado_doc->fecha }}
                <!-- <br/>
                <b>Aplicación Origen:</b> { { $aplicacion->descripcion }}
            -->
            </div>
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <b>Nombre Tercero:</b> {{ $encabezado_doc->tercero }}
            <br/>
            <b>Documento ID &nbsp;&nbsp;:</b> {{ $encabezado_doc->numero_identificacion }}
        </td>
        <td style="border: solid 1px black;">
            <b>Doc. soporte:</b>  {{ $encabezado_doc->documento_soporte }}
            <br/>
            <b>Detalle &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</b> {{ $encabezado_doc->detalle }}
        </td>
    </tr>
</table>