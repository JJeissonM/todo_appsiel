<div style="font-size: 15px;">
    <div>
        <div width="50%" style="border: solid 1px black; padding-top: -20px;">
            <div>
                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa' )
            </div>
        </div>
        <div style="border: solid 1px black; padding-top: -20px;">
            <div style="vertical-align: center;">
                <b style="font-size: 1.4em; text-align: center; display: block;">{{ $descripcion_transaccion }}</b>
                <br/>
                <b>Documento:</b> {{ $encabezado_doc->documento }}
            </div>
        </div>
    </div>
    <div>
        <div style="border: solid 1px black;">
            @php $fecha = explode("-",$encabezado_doc->fecha) @endphp
                <b>Ciudad y Fecha: </b> &nbsp; {{ $ciudad }}, {{ $fecha[2] }} de {{ Form::NombreMes([$fecha[1]]) }} de {{ $fecha[0] }}
        </div>
        <div style="border: solid 1px black;">
            @php $fecha=explode("-",$encabezado_doc->fecha_vencimiento) @endphp
            @if( $fecha[2] != '00' )
                <b>Pagar hasta: </b> &nbsp; {{ $fecha[2] }} de {{ Form::NombreMes([$fecha[1]]) }} de {{ $fecha[0] }}
            @endif
        </div>
    </div>
    <div>
        <div colspan="2" style="border: solid 1px black;">
            <b>Nombre: </b> {{ $encabezado_doc->tercero }}
        </div>
    </div>
    <div>
        <div colspan="2" style="border: solid 1px black;">
            <b>{{ $encabezado_doc->tipo_propiedad }}: </b> {{ $encabezado_doc->nomenclatura }}
        </div>
    </div>
</div>