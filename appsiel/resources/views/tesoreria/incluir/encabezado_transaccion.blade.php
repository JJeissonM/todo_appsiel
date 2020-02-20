<table class="table table-bordered">
    <tr>
        <td width="50%" style="border: solid 1px black; padding-top: -20px;">
            <div>
                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa' )
            </div>
        </td>
        <td style="border: solid 1px black; padding-top: -20px;">
            <div style="vertical-align: center;">
                <b style="font-size: 1.4em; text-align: center; display: block;">{{ $descripcion_transaccion }}</b>
                <br/>
                <b>Documento:</b> {{ $encabezado_doc->documento }}
                <br/>
                <b>Fecha:</b> {{ $encabezado_doc->fecha }}
            </div>
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <b>Recibí de:</b> {{ $encabezado_doc->tercero }}
        </td>
        <td style="border: solid 1px black;">
            @if( $encabezado_doc->codigo !='' )
                <b>Inmueble:</b>  {{ $encabezado_doc->codigo }} - {{ $encabezado_doc->nomenclatura }}
            @else
                <b>Documento: </b> {{ $tercero->tipo_doc_identidad }} {{ number_format($tercero->numero_identificacion, 0, ',', '.') }}
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2" style="border: solid 1px black;">
            <b>Por concepto de:</b> {{ $encabezado_doc->detalle }}
        </td>
    </tr>
</table>