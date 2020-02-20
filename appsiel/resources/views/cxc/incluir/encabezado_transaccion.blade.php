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
            </div>
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <b>Propietario: </b> {{ $encabezado_doc->descripcion }}
            <br/>

            <b>CC / NIT: </b> {{ number_format($encabezado_doc->numero_identificacion, 0, ',', '.') }}
            <br/>

            <b>Dirección: </b> {{ $encabezado_doc->direccion }}
            <br/>

            <b>Teléfono: </b> {{ $encabezado_doc->telefono1 }}
            <br/>
            
        </td>
        <td style="border: solid 1px black;">
            <b>Cód. inmueble: </b> {{ $encabezado_doc->codigo_inmueble }}
            <br/>

            @php 
                $fecha = explode("-",$encabezado_doc->fecha) 
            @endphp
            <b>Fecha facturación: </b> &nbsp; {{ $fecha[2] }} de {{ Form::NombreMes([$fecha[1]]) }} de {{ $fecha[0] }}
            <br/>

            @php $fecha=explode("-",$encabezado_doc->fecha_vencimiento) @endphp
            @if( $fecha[2] != '00' )
                <b>Pagar hasta: </b> &nbsp; {{ $fecha[2] }} de {{ Form::NombreMes([$fecha[1]]) }} de {{ $fecha[0] }}
            @endif
            <br/>
            @if($total_1 != 0)
                <spam style="color: red;"><b>Valor a pagar: </b> &nbsp; ${{ number_format($total_1, 0, ',', '.') }} </spam>
            @endif
        </td>
    </tr>
</table>